<?php

namespace App\Services;

use App\Models\Existencia;
use App\Models\InventarioMinimo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InventarioService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Consulta las existencias aplicando los filtros recibidos.
     */
    public function consultar(array $filtros): LengthAwarePaginator
    {
        $consulta = Existencia::query()
            ->with([
                'bodega:id,codigo,nombre',
                'medicamento:id,codigo,nombre,unidad_medida,control_especial',
                'lote:id,codigo,fecha_vencimiento,medicamento_id',
            ]);

        if (isset($filtros['bodega_id'])) {
            $consulta->where('bodega_id', $filtros['bodega_id']);
        }

        if (isset($filtros['medicamento_id'])) {
            $consulta->where('medicamento_id', $filtros['medicamento_id']);
        }

        if (isset($filtros['lote_id'])) {
            $consulta->where('lote_id', $filtros['lote_id']);
        }

        if (
            isset($filtros['vencimiento_proximo'])
            && filter_var(
                $filtros['vencimiento_proximo'],
                FILTER_VALIDATE_BOOLEAN
            )
        ) {
            $hoy = now()->toDateString();
            $limite = now()->addDays(90)->toDateString();

            $consulta->whereHas('lote', function ($query) use ($hoy, $limite) {
                $query->whereBetween('fecha_vencimiento', [$hoy, $limite]);
            });
        }

        return $consulta
            ->orderBy('bodega_id')
            ->orderBy('medicamento_id')
            ->orderBy('lote_id')
            ->paginate(25)
            ->withQueryString();
    }

    /**
     * Consulta los lotes que vencen dentro de los próximos 90 días.
     */
    public function alertasVencimiento(): Collection
    {
        $hoy = now()->toDateString();
        $limite = now()->addDays(90)->toDateString();

        return Existencia::query()
            ->with([
                'bodega:id,codigo,nombre',
                'medicamento:id,codigo,nombre,unidad_medida',
                'lote:id,codigo,fecha_vencimiento,medicamento_id',
            ])
            ->whereHas('lote', function ($query) use ($hoy, $limite) {
                $query->whereBetween(
                    'fecha_vencimiento',
                    [$hoy, $limite]
                );
            })
            ->where('cantidad', '>', 0)
            ->orderBy('bodega_id')
            ->orderBy('medicamento_id')
            ->orderBy('lote_id')
            ->get();
    }

    /**
     * Consulta productos cuya existencia total por bodega
     * está por debajo del mínimo configurado.
     */
    public function alertasStockMinimo(): Collection
    {
        return InventarioMinimo::query()
            ->with([
                'bodega:id,codigo,nombre',
                'medicamento:id,codigo,nombre,unidad_medida',
            ])
            ->get()
            ->filter(function ($minimo) {
                $cantidadActual = Existencia::query()
                    ->where('bodega_id', $minimo->bodega_id)
                    ->where('medicamento_id', $minimo->medicamento_id)
                    ->sum('cantidad');

                $minimo->cantidad_actual = $cantidadActual;
                $minimo->faltante = max(
                    0,
                    $minimo->cantidad_minima - $cantidadActual
                );

                return $cantidadActual < $minimo->cantidad_minima;
            })
            ->values();
    }
}
