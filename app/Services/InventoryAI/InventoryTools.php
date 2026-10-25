<?php

namespace App\Services\InventoryAI;

use App\Models\Bodega;
use App\Models\Existencia;
use App\Models\Medicamento;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryTools
{
    /**
     * Herramientas que el asistente tiene permitido ejecutar.
     */
    private const ALLOWED_TOOLS = [
        'buscar_lotes',
        'consultar_stock',
        'consultar_vencimientos',
        'consultar_stock_minimo',
    ];

    public function execute(
        User $user,
        string $tool,
        array $arguments = []
    ): array {
        // Verifica el permiso en cada ejecución, no solo al entrar al endpoint.
        if (! $user->can('consultar-inventario')) {
            abort(403, 'No tienes permiso para consultar el inventario.');
        }

        if (! in_array($tool, self::ALLOWED_TOOLS, true)) {
            throw ValidationException::withMessages([
                'tool' => 'La herramienta solicitada no está permitida.',
            ]);
        }

        return match ($tool) {
            'buscar_lotes' => $this->buscarLotes($arguments),
            'consultar_stock' => $this->consultarStock($arguments),
            'consultar_vencimientos' => $this->consultarVencimientos($arguments),
            'consultar_stock_minimo' => $this->consultarStockMinimo($arguments),
        };
    }

    private function validarArgumentos(array $arguments): array
    {
        $validated = validator($arguments, [
            'medicamento' => ['nullable', 'string', 'max:100'],
            'bodega_id' => ['nullable', 'integer', 'exists:bodegas,id'],
            'dias' => ['nullable', 'integer', 'min:1', 'max:365'],
        ])->validate();

        if (isset($validated['medicamento'])) {
            $validated['medicamento'] = trim($validated['medicamento']);

            if ($validated['medicamento'] === '') {
                unset($validated['medicamento']);
            }
        }

        return $validated;
    }

    private function filtrarMedicamento($query, ?string $nombre): void
    {
        if ($nombre !== null) {
            $query->whereHas('medicamento', function ($medicamentos) use ($nombre) {
                $medicamentos->where('nombre', 'ilike', "%{$nombre}%")
                    ->orWhere('codigo', 'ilike', "%{$nombre}%");
            });
        }
    }

    private function filtrarBodega($query, ?int $bodegaId): void
    {
        if ($bodegaId !== null) {
            $query->where('bodega_id', $bodegaId);
        }
    }

    /**
     * Busca lotes que tengan existencias.
     */
    private function buscarLotes(array $arguments): array
    {
        $args = $this->validarArgumentos($arguments);

        $query = Existencia::query()
            ->with(['medicamento', 'lote', 'bodega'])
            ->where('cantidad', '>', 0);

        $this->filtrarMedicamento($query, $args['medicamento'] ?? null);
        $this->filtrarBodega($query, $args['bodega_id'] ?? null);

        return [
            'herramienta' => 'buscar_lotes',
            'resultados' => $query
                ->orderBy('bodega_id')
                ->orderBy('medicamento_id')
                ->limit(100)
                ->get()
                ->map(fn (Existencia $existencia) => $this->formatearExistencia($existencia))
                ->values()
                ->all(),
        ];
    }

    /**
     * Consulta el stock disponible por medicamento y bodega.
     */
    private function consultarStock(array $arguments): array
    {
        $args = $this->validarArgumentos($arguments);

        $query = Existencia::query()
            ->with(['medicamento', 'bodega', 'lote']);

        $this->filtrarMedicamento($query, $args['medicamento'] ?? null);
        $this->filtrarBodega($query, $args['bodega_id'] ?? null);

        $existencias = $query->limit(500)->get();

        $resultados = $existencias
            ->groupBy(fn (Existencia $e) => $e->bodega_id.'-'.$e->medicamento_id)
            ->map(function ($grupo) {
                $primera = $grupo->first();

                return [
                    'medicamento' => $primera->medicamento->nombre,
                    'codigo_medicamento' => $primera->medicamento->codigo,
                    'bodega' => $primera->bodega->nombre,
                    'cantidad_total' => $grupo->sum('cantidad'),
                    'cantidad_vigente' => $grupo
                        ->filter(fn (Existencia $e) => $e->lote->fecha_vencimiento->toDateString() >= now()->toDateString()
                        )
                        ->sum('cantidad'),
                ];
            })
            ->values()
            ->all();

        return [
            'herramienta' => 'consultar_stock',
            'resultados' => $resultados,
        ];
    }

    /**
     * Consulta lotes que vencen dentro de los próximos N días.
     */
    private function consultarVencimientos(array $arguments): array
    {
        $args = $this->validarArgumentos($arguments);

        $dias = $args['dias'] ?? 60;
        $hoy = Carbon::today();
        $fechaLimite = $hoy->copy()->addDays($dias);

        $query = Existencia::query()
            ->with(['medicamento', 'lote', 'bodega'])
            ->where('cantidad', '>', 0)
            ->whereHas('lote', function ($lotes) use ($hoy, $fechaLimite) {
                $lotes->whereDate('fecha_vencimiento', '>=', $hoy)
                    ->whereDate('fecha_vencimiento', '<=', $fechaLimite);
            });

        $this->filtrarMedicamento($query, $args['medicamento'] ?? null);
        $this->filtrarBodega($query, $args['bodega_id'] ?? null);

        return [
            'herramienta' => 'consultar_vencimientos',
            'dias_consultados' => $dias,
            'fecha_desde' => $hoy->toDateString(),
            'fecha_hasta' => $fechaLimite->toDateString(),
            'resultados' => $query
                ->orderBy('lote_id')
                ->limit(100)
                ->get()
                ->map(fn (Existencia $existencia) => $this->formatearExistencia($existencia))
                ->values()
                ->all(),
        ];
    }

    /**
     * Detecta medicamentos cuyo stock en una bodega es inferior
     * a la cantidad mínima configurada.
     */
    private function consultarStockMinimo(array $arguments): array
    {
        $args = $this->validarArgumentos($arguments);

        $query = DB::table('inventario_minimos as im')
            ->join('medicamentos as m', 'm.id', '=', 'im.medicamento_id')
            ->join('bodegas as b', 'b.id', '=', 'im.bodega_id')
            ->leftJoin('existencias as e', function ($join) {
                $join->on('e.medicamento_id', '=', 'im.medicamento_id')
                    ->on('e.bodega_id', '=', 'im.bodega_id');
            })
            ->select(
                'm.id as medicamento_id',
                'm.nombre as medicamento',
                'm.codigo as codigo_medicamento',
                'b.id as bodega_id',
                'b.nombre as bodega',
                'im.cantidad_minima'
            )
            ->selectRaw('COALESCE(SUM(e.cantidad), 0) as cantidad_total')
            ->groupBy(
                'm.id',
                'm.nombre',
                'm.codigo',
                'b.id',
                'b.nombre',
                'im.cantidad_minima'
            )
            ->havingRaw('COALESCE(SUM(e.cantidad), 0) < im.cantidad_minima');

        if (isset($args['bodega_id'])) {
            $query->where('im.bodega_id', $args['bodega_id']);
        }

        if (isset($args['medicamento'])) {
            $nombre = $args['medicamento'];

            $query->where(function ($q) use ($nombre) {
                $q->where('m.nombre', 'ilike', "%{$nombre}%")
                    ->orWhere('m.codigo', 'ilike', "%{$nombre}%");
            });
        }

        return [
            'herramienta' => 'consultar_stock_minimo',
            'resultados' => $query->limit(100)->get()->map(fn ($fila) => [
                'medicamento' => $fila->medicamento,
                'codigo_medicamento' => $fila->codigo_medicamento,
                'bodega' => $fila->bodega,
                'cantidad_actual' => (int) $fila->cantidad_total,
                'cantidad_minima' => (int) $fila->cantidad_minima,
                'faltante' => max(
                    0,
                    (int) $fila->cantidad_minima - (int) $fila->cantidad_total
                ),
            ])->all(),
        ];
    }

    private function formatearExistencia(Existencia $existencia): array
    {
        return [
            'medicamento' => $existencia->medicamento->nombre,
            'codigo_medicamento' => $existencia->medicamento->codigo,
            'bodega' => $existencia->bodega->nombre,
            'codigo_bodega' => $existencia->bodega->codigo,
            'lote' => $existencia->lote->codigo,
            'fecha_vencimiento' => $existencia->lote->fecha_vencimiento->toDateString(),
            'cantidad' => $existencia->cantidad,
        ];
    }
}
