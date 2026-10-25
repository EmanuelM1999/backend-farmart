<?php

namespace App\Services;

use App\Models\Existencia;
use App\Models\InventarioMovimiento;
use App\Models\Lote;
use App\Models\Traslado;
use App\Models\TrasladoDetalle;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TrasladoService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function crearSolicitud(array $datos, User $usuario): Traslado
    {
        return DB::transaction(function () use ($datos, $usuario) {
            $bodegaOrigenId = (int) $datos['bodega_origen_id'];
            $bodegaDestinoId = (int) $datos['bodega_destino_id'];

            if ($bodegaOrigenId === $bodegaDestinoId) {
                throw ValidationException::withMessages([
                    'bodega_destino_id' => 'La bodega de origen y destino deben ser diferentes.',
                ]);
            }

            $detalles = collect($datos['detalles'])
                ->sortBy('lote_id')
                ->values();

            $lotes = Lote::query()
                ->whereIn(
                    'id',
                    $detalles->pluck('lote_id')->unique()->all()
                )
                ->get()
                ->keyBy('id');

            // Bloquear las existencias para evitar que dos solicitudes
            // concurrentes validen simultáneamente el mismo stock.
            $existencias = Existencia::query()
                ->where('bodega_id', $bodegaOrigenId)
                ->whereIn('lote_id', $detalles->pluck('lote_id')->all())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('lote_id');

            foreach ($detalles as $detalle) {
                $loteId = (int) $detalle['lote_id'];
                $cantidad = (int) $detalle['cantidad_solicitada'];

                $lote = $lotes->get($loteId);

                if (! $lote) {
                    throw ValidationException::withMessages([
                        'detalles' => 'Uno de los lotes no existe.',
                    ]);
                }

                if ($lote->fecha_vencimiento->lt(today())) {
                    throw ValidationException::withMessages([
                        'detalles' => "El lote {$lote->codigo} está vencido.",
                    ]);
                }

                $existencia = $existencias->get($loteId);

                if (! $existencia || $existencia->cantidad < $cantidad) {
                    throw ValidationException::withMessages([
                        'detalles' => "No hay existencias suficientes para el lote {$lote->codigo} en la bodega de origen.",
                    ]);
                }
            }

            $traslado = Traslado::create([
                'bodega_origen_id' => $bodegaOrigenId,
                'bodega_destino_id' => $bodegaDestinoId,
                'solicitante_id' => $usuario->id,
                'estado' => 'SOLICITADO',
            ]);

            foreach ($detalles as $detalle) {
                $lote = $lotes->get((int) $detalle['lote_id']);

                TrasladoDetalle::create([
                    'traslado_id' => $traslado->id,
                    'medicamento_id' => $lote->medicamento_id,
                    'lote_id' => $lote->id,
                    'cantidad_solicitada' => (int) $detalle['cantidad_solicitada'],
                    'cantidad_despachada' => 0,
                    'cantidad_recibida' => 0,
                ]);
            }

            return $traslado->load('detalles');
        });
    }

    public function consultar(array $filtros): LengthAwarePaginator
    {
        return Traslado::query()
            ->with('detalles')
            ->when(
                isset($filtros['estado']),
                fn ($query) => $query->where(
                    'estado',
                    $filtros['estado']
                )
            )
            ->when(
                isset($filtros['bodega_origen_id']),
                fn ($query) => $query->where(
                    'bodega_origen_id',
                    $filtros['bodega_origen_id']
                )
            )
            ->when(
                isset($filtros['bodega_destino_id']),
                fn ($query) => $query->where(
                    'bodega_destino_id',
                    $filtros['bodega_destino_id']
                )
            )
            ->latest('id')
            ->paginate($filtros['per_page'] ?? 25);
    }

    public function aprobar(int $trasladoId, User $usuario): Traslado
    {
        return DB::transaction(function () use ($trasladoId, $usuario) {
            $traslado = Traslado::query()
                ->lockForUpdate()
                ->findOrFail($trasladoId);

            if ($traslado->estado !== 'SOLICITADO') {
                throw ValidationException::withMessages([
                    'estado' => 'Solo se pueden aprobar traslados en estado SOLICITADO.',
                ]);
            }

            if ((int) $traslado->solicitante_id === (int) $usuario->id) {
                throw ValidationException::withMessages([
                    'traslado' => 'No puedes aprobar un traslado que tú mismo solicitaste.',
                ]);
            }

            $traslado->aprobador_id = $usuario->id;
            $traslado->estado = 'APROBADO';
            $traslado->save();

            return $traslado->load('detalles');
        });
    }

    public function despachar(int $trasladoId, User $usuario): Traslado
    {
        return DB::transaction(function () use ($trasladoId, $usuario) {
            $traslado = Traslado::query()
                ->lockForUpdate()
                ->with('detalles')
                ->findOrFail($trasladoId);

            if ($traslado->estado !== 'APROBADO') {
                throw ValidationException::withMessages([
                    'estado' => 'Solo se pueden despachar traslados aprobados.',
                ]);
            }

            if ($traslado->detalles->isEmpty()) {
                throw ValidationException::withMessages([
                    'detalles' => 'El traslado no tiene medicamentos para despachar.',
                ]);
            }

            foreach ($traslado->detalles as $detalle) {
                $existencia = Existencia::query()
                    ->where('bodega_id', $traslado->bodega_origen_id)
                    ->where('lote_id', $detalle->lote_id)
                    ->lockForUpdate()
                    ->first();

                if (! $existencia) {
                    throw ValidationException::withMessages([
                        'inventario' => "No existe stock del lote {$detalle->lote_id} en la bodega de origen.",
                    ]);
                }

                $lote = $existencia->lote;

                if (! $lote || $lote->fecha_vencimiento->lt(today())) {
                    throw ValidationException::withMessages([
                        'lote' => "El lote {$detalle->lote_id} está vencido o no existe.",
                    ]);
                }

                $cantidad = (int) $detalle->cantidad_solicitada;

                if ($existencia->cantidad < $cantidad) {
                    throw ValidationException::withMessages([
                        'inventario' => "Stock insuficiente para el lote {$detalle->lote_id}.",
                    ]);
                }

                // Descontar inventario de la bodega de origen.
                $existencia->cantidad -= $cantidad;
                $existencia->save();

                // Registrar salida en el kardex.
                InventarioMovimiento::create([
                    'existencia_id' => $existencia->id,
                    'tipo_movimiento' => 'salida_por_traslado',
                    'cantidad' => $cantidad,
                    'user_id' => $usuario->id,
                    'motivo' => "Despacho del traslado #{$traslado->id}",
                ]);

                $detalle->cantidad_despachada = $cantidad;
                $detalle->save();
            }

            $traslado->estado = 'EN_TRANSITO';
            $traslado->save();

            return $traslado->load('detalles');
        });
    }

    public function recibir(int $trasladoId, array $detallesRecibidos, User $usuario): Traslado
    {
        return DB::transaction(function () use (
            $trasladoId,
            $detallesRecibidos,
            $usuario
        ) {
            $traslado = Traslado::query()
                ->lockForUpdate()
                ->findOrFail($trasladoId);

            if (! in_array($traslado->estado, [
                'EN_TRANSITO',
                'RECIBIDO_PARCIAL',
            ], true)) {
                throw ValidationException::withMessages([
                    'estado' => 'El traslado no está disponible para recepción.',
                ]);
            }

            foreach ($detallesRecibidos as $detalleRecibido) {
                $detalle = $traslado->detalles()
                    ->where('id', $detalleRecibido['traslado_detalle_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $detalle) {
                    throw ValidationException::withMessages([
                        'detalles' => 'Uno de los detalles no pertenece a este traslado.',
                    ]);
                }

                $cantidadNueva = (int) $detalleRecibido['cantidad_recibida'];

                $cantidadPendiente = (int) $detalle->cantidad_despachada
                    - (int) $detalle->cantidad_recibida;

                if ($cantidadNueva > $cantidadPendiente) {
                    throw ValidationException::withMessages([
                        'detalles' => "La cantidad recibida supera lo pendiente para el lote {$detalle->lote_id}.",
                    ]);
                }

                if ($cantidadNueva === 0) {
                    continue;
                }

                // Buscar la existencia del lote en la bodega de destino.
                $existencia = Existencia::query()
                    ->where('bodega_id', $traslado->bodega_destino_id)
                    ->where('lote_id', $detalle->lote_id)
                    ->lockForUpdate()
                    ->first();

                // Si el lote aún no existe en esa bodega, crear su existencia.
                if (! $existencia) {
                    $existencia = Existencia::create([
                        'medicamento_id' => $detalle->medicamento_id,
                        'bodega_id' => $traslado->bodega_destino_id,
                        'lote_id' => $detalle->lote_id,
                        'cantidad' => 0,
                    ]);
                }

                $existencia->cantidad += $cantidadNueva;
                $existencia->save();

                InventarioMovimiento::create([
                    'existencia_id' => $existencia->id,
                    'tipo_movimiento' => 'entrada_por_traslado',
                    'cantidad' => $cantidadNueva,
                    'user_id' => $usuario->id,
                    'motivo' => "Recepción del traslado #{$traslado->id}",
                ]);

                $detalle->cantidad_recibida =
                    (int) $detalle->cantidad_recibida + $cantidadNueva;

                $detalle->save();
            }

            // Determinar el estado según las cantidades acumuladas.
            $traslado->load('detalles');

            $todoRecibido = $traslado->detalles->every(
                fn ($detalle) => (int) $detalle->cantidad_recibida
                    === (int) $detalle->cantidad_despachada
            );

            $traslado->estado = $todoRecibido
                ? 'RECIBIDO'
                : 'RECIBIDO_PARCIAL';

            $traslado->save();

            return $traslado->load('detalles');
        });
    }

    public function anular(int $trasladoId): Traslado
    {
        return DB::transaction(function () use ($trasladoId) {
            $traslado = Traslado::query()
                ->lockForUpdate()
                ->findOrFail($trasladoId);

            if (! in_array($traslado->estado, [
                'SOLICITADO',
                'APROBADO',
            ], true)) {
                throw ValidationException::withMessages([
                    'estado' => 'Solo se pueden anular traslados solicitados o aprobados que aún no han sido despachados.',
                ]);
            }

            $traslado->estado = 'ANULADO';
            $traslado->save();

            return $traslado->load('detalles');
        });
    }
}
