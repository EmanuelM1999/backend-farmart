<?php

namespace App\Services;

use App\Models\Dispensacion;
use App\Models\DispensacionDetalle;
use App\Models\Existencia;
use App\Models\InventarioMovimiento;
use App\Models\Prescripcion;
use App\Models\PrescripcionDetalle;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DispensacionService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function crearSolicitud(array $datos, User $usuario): Dispensacion
    {
        $detallesSolicitados = collect($datos['medicamentos'])
            ->sortBy('prescripcion_detalle_id')
            ->values()
            ->map(fn ($detalle) => [
                'prescripcion_detalle_id' => (int) $detalle['prescripcion_detalle_id'],
                'cantidad' => (int) $detalle['cantidad'],
            ])
            ->all();

        $datosHash = [
            'paciente_id' => (int) $datos['paciente_id'],
            'prescripcion_id' => (int) $datos['prescripcion_id'],
            'bodega_id' => (int) $datos['bodega_id'],
            'medicamentos' => $detallesSolicitados,
        ];

        $hash = hash(
            'sha256',
            json_encode($datosHash, JSON_THROW_ON_ERROR)
        );

        try {
            return DB::transaction(function () use (
                $datos,
                $usuario,
                $detallesSolicitados,
                $hash
            ) {
                // Comprobar si ya se procesó esta clave.
                $existente = Dispensacion::query()
                    ->where(
                        'clave_idempotencia',
                        $datos['clave_idempotencia']
                    )
                    ->first();

                if ($existente) {
                    if ($existente->request_hash !== $hash) {
                        throw ValidationException::withMessages([
                            'clave_idempotencia' => 'La clave ya fue utilizada con datos diferentes.',
                        ]);
                    }

                    return $existente->load('detalles');
                }

                $prescripcion = Prescripcion::query()
                    ->whereKey($datos['prescripcion_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    (int) $prescripcion->paciente_id
                    !== (int) $datos['paciente_id']
                ) {
                    throw ValidationException::withMessages([
                        'paciente_id' => 'El paciente no corresponde a la prescripción.',
                    ]);
                }

                $requiereAutorizacion = false;

                // Validar que los detalles pertenezcan a la prescripción
                // y comprobar las cantidades acumuladas.
                foreach ($detallesSolicitados as $detalleSolicitado) {
                    $detallePrescripcion = PrescripcionDetalle::query()
                        ->whereKey(
                            $detalleSolicitado['prescripcion_detalle_id']
                        )
                        ->where('prescripcion_id', $prescripcion->id)
                        ->with('medicamento')
                        ->lockForUpdate()
                        ->first();

                    if (! $detallePrescripcion) {
                        throw ValidationException::withMessages([
                            'medicamentos' => 'Un medicamento no pertenece a la prescripción.',
                        ]);
                    }

                    $cantidadYaDispensada = DB::table(
                        'dispensacion_detalles'
                    )
                        ->join(
                            'dispensaciones',
                            'dispensaciones.id',
                            '=',
                            'dispensacion_detalles.dispensacion_id'
                        )
                        ->where(
                            'dispensacion_detalles.prescripcion_detalle_id',
                            $detallePrescripcion->id
                        )
                        ->where('dispensaciones.estado', 'DISPENSADA')
                        ->sum(
                            'dispensacion_detalles.cantidad_dispensada'
                        );

                    if (
                        $cantidadYaDispensada
                        + $detalleSolicitado['cantidad']
                        > $detallePrescripcion->cantidad_prescrita
                    ) {
                        throw ValidationException::withMessages([
                            'medicamentos' => "La cantidad solicitada supera lo prescrito para {$detallePrescripcion->medicamento->nombre}.",
                        ]);
                    }

                    if ($detallePrescripcion->medicamento->control_especial) {
                        $requiereAutorizacion = true;
                    }
                }

                // La solicitud se registra antes de procesar el inventario.
                $dispensacion = Dispensacion::create([
                    'paciente_id' => $prescripcion->paciente_id,
                    'prescripcion_id' => $prescripcion->id,
                    'user_id' => $usuario->id,
                    'clave_idempotencia' => $datos['clave_idempotencia'],
                    'request_hash' => $hash,
                    'estado' => 'PENDIENTE',
                ]);

                // Si hay control especial, no se modifica el inventario.
                if ($requiereAutorizacion) {
                    return $dispensacion;
                }

                // Medicamentos normales: procesar en la misma transacción.
                $this->procesarConsumo(
                    $dispensacion,
                    $datos['bodega_id'],
                    $detallesSolicitados,
                    $usuario
                );

                $dispensacion->update([
                    'estado' => 'DISPENSADA',
                ]);

                return $dispensacion->fresh('detalles');
            });
        } catch (\Throwable $th) {
            $codigoSql = $th->errorInfo[0] ?? (string) $th->getCode();
            $mensajeSql = $th->getMessage();

            $esDuplicadoIdempotencia =
                $codigoSql === '23505'
                && str_contains(
                    $mensajeSql,
                    'dispensaciones_clave_idempotencia_unique'
                );

            if (! $esDuplicadoIdempotencia) {
                throw $th;
            }

            $existente = Dispensacion::query()
                ->where('clave_idempotencia', $datos['clave_idempotencia'])
                ->first();

            if (! $existente) {
                throw $th;
            }

            if ($existente->request_hash !== $hash) {
                throw ValidationException::withMessages([
                    'clave_idempotencia' => 'La clave ya fue utilizada con datos diferentes.',
                ]);
            }

            return $existente->load('detalles');
        }
    }

    private function procesarConsumo(Dispensacion $dispensacion, int $bodegaId, array $detallesSolicitados, User $usuario): void
    {
        foreach ($detallesSolicitados as $detalleSolicitado) {
            $detallePrescripcion = PrescripcionDetalle::query()
                ->whereKey(
                    $detalleSolicitado['prescripcion_detalle_id']
                )
                ->where(
                    'prescripcion_id',
                    $dispensacion->prescripcion_id
                )
                ->with('medicamento')
                ->lockForUpdate()
                ->first();

            if (! $detallePrescripcion) {
                throw ValidationException::withMessages([
                    'medicamentos' => 'El detalle ya no pertenece a la prescripción.',
                ]);
            }

            // Volver a comprobar la cantidad antes del consumo.
            $cantidadYaDispensada = DB::table(
                'dispensacion_detalles'
            )
                ->join(
                    'dispensaciones',
                    'dispensaciones.id',
                    '=',
                    'dispensacion_detalles.dispensacion_id'
                )
                ->where(
                    'dispensacion_detalles.prescripcion_detalle_id',
                    $detallePrescripcion->id
                )
                ->where('dispensaciones.estado', 'DISPENSADA')
                ->sum('dispensacion_detalles.cantidad_dispensada');

            $cantidadSolicitada = $detalleSolicitado['cantidad'];

            if (
                $cantidadYaDispensada + $cantidadSolicitada
                > $detallePrescripcion->cantidad_prescrita
            ) {
                throw ValidationException::withMessages([
                    'medicamentos' => 'La cantidad disponible de la prescripción cambió.',
                ]);
            }

            // Obtener existencias vigentes ordenadas por FEFO.
            $existencias = Existencia::query()
                ->select('existencias.*')
                ->join(
                    'lotes',
                    'lotes.id',
                    '=',
                    'existencias.lote_id'
                )
                ->where('existencias.bodega_id', $bodegaId)
                ->where(
                    'existencias.medicamento_id',
                    $detallePrescripcion->medicamento_id
                )
                ->where('existencias.cantidad', '>', 0)
                ->whereDate(
                    'lotes.fecha_vencimiento',
                    '>=',
                    now()->toDateString()
                )
                ->orderBy('lotes.fecha_vencimiento')
                ->orderBy('existencias.id')
                ->lock('for update of existencias')
                ->get();

            $stockDisponible = (int) $existencias->sum('cantidad');

            if ($stockDisponible < $cantidadSolicitada) {
                throw ValidationException::withMessages([
                    'medicamentos' => "Inventario insuficiente para {$detallePrescripcion->medicamento->nombre}.",
                ]);
            }

            $pendiente = $cantidadSolicitada;

            foreach ($existencias as $existencia) {
                if ($pendiente === 0) {
                    break;
                }

                $cantidadConsumida = min(
                    $pendiente,
                    (int) $existencia->cantidad
                );

                $existencia->cantidad -= $cantidadConsumida;
                $existencia->save();

                DispensacionDetalle::create([
                    'dispensacion_id' => $dispensacion->id,
                    'medicamento_id' => $detallePrescripcion->medicamento_id,
                    'lote_id' => $existencia->lote_id,
                    'prescripcion_detalle_id' => $detallePrescripcion->id,
                    'cantidad_dispensada' => $cantidadConsumida,
                    'cantidad_existencia_despues' => $existencia->cantidad,
                ]);

                InventarioMovimiento::create([
                    'existencia_id' => $existencia->id,
                    'tipo_movimiento' => 'salida',
                    'cantidad' => $cantidadConsumida,
                    'user_id' => $usuario->id,
                    'motivo' => "Dispensación #{$dispensacion->id}",
                ]);

                $pendiente -= $cantidadConsumida;
            }
        }
    }

    public function autorizarSolicitud(int $dispensacionId, array $datos, User $regente): Dispensacion
    {
        $detallesSolicitados = collect($datos['medicamentos'])
            ->sortBy('prescripcion_detalle_id')
            ->values()
            ->map(fn ($detalle) => [
                'prescripcion_detalle_id' => (int) $detalle['prescripcion_detalle_id'],
                'cantidad' => (int) $detalle['cantidad'],
            ])
            ->all();

        $datosHash = [
            'paciente_id' => (int) $datos['paciente_id'],
            'prescripcion_id' => (int) $datos['prescripcion_id'],
            'bodega_id' => (int) $datos['bodega_id'],
            'medicamentos' => $detallesSolicitados,
        ];

        $hash = hash(
            'sha256',
            json_encode($datosHash, JSON_THROW_ON_ERROR)
        );

        return DB::transaction(function () use (
            $dispensacionId,
            $datos,
            $detallesSolicitados,
            $hash,
            $regente
        ) {
            $dispensacion = Dispensacion::query()
                ->whereKey($dispensacionId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($dispensacion->estado !== 'PENDIENTE') {
                throw ValidationException::withMessages([
                    'dispensacion' => 'La dispensación no está pendiente de autorización.',
                ]);
            }

            if ($dispensacion->clave_idempotencia !== $datos['clave_idempotencia']) {
                throw ValidationException::withMessages([
                    'clave_idempotencia' => 'La clave no corresponde a la solicitud original.',
                ]);
            }

            if ($dispensacion->user_id === $regente->id) {
                throw ValidationException::withMessages([
                    'regente' => 'La persona que solicita la dispensación no puede autorizarla.',
                ]);
            }

            if ($dispensacion->request_hash !== $hash) {
                throw ValidationException::withMessages([
                    'dispensacion' => 'Los datos no coinciden con la solicitud original.',
                ]);
            }

            if (
                (int) $dispensacion->paciente_id !== (int) $datos['paciente_id']
                || (int) $dispensacion->prescripcion_id !== (int) $datos['prescripcion_id']
            ) {
                throw ValidationException::withMessages([
                    'dispensacion' => 'El paciente o la prescripción no coinciden con la solicitud.',
                ]);
            }

            $prescripcion = Prescripcion::query()
                ->whereKey($dispensacion->prescripcion_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $prescripcion->paciente_id !== (int) $dispensacion->paciente_id) {
                throw ValidationException::withMessages([
                    'prescripcion' => 'La prescripción no corresponde al paciente.',
                ]);
            }

            $requiereAutorizacion = false;

            foreach ($detallesSolicitados as $detalleSolicitado) {
                $detallePrescripcion = PrescripcionDetalle::query()
                    ->whereKey($detalleSolicitado['prescripcion_detalle_id'])
                    ->where('prescripcion_id', $prescripcion->id)
                    ->with('medicamento')
                    ->lockForUpdate()
                    ->first();

                if (! $detallePrescripcion) {
                    throw ValidationException::withMessages([
                        'medicamentos' => 'Un medicamento no pertenece a la prescripción.',
                    ]);
                }

                if ($detallePrescripcion->medicamento->control_especial) {
                    $requiereAutorizacion = true;
                }
            }

            if (! $requiereAutorizacion) {
                throw ValidationException::withMessages([
                    'medicamentos' => 'La solicitud no contiene medicamentos de control especial.',
                ]);
            }

            $dispensacion->autorizacion()->create([
                'regente_id' => $regente->id,
            ]);

            $this->procesarConsumo(
                $dispensacion,
                (int) $datos['bodega_id'],
                $detallesSolicitados,
                $dispensacion->usuario
            );

            $dispensacion->update([
                'estado' => 'DISPENSADA',
            ]);

            return $dispensacion->fresh('detalles');
        });
    }
}
