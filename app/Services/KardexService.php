<?php

namespace App\Services;

use App\Models\InventarioMovimiento;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class KardexService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function consultar(array $filtros): LengthAwarePaginator
    {
        $consulta = InventarioMovimiento::query()
            ->with([
                'usuario:id,name,email',
                'existencia:id,cantidad,bodega_id,medicamento_id,lote_id',
                'existencia.bodega:id,codigo,nombre',
                'existencia.medicamento:id,codigo,nombre',
                'existencia.lote:id,codigo,fecha_vencimiento',
            ]);

        if (isset($filtros['bodega_id'])) {
            $consulta->whereHas('existencia', function ($query) use ($filtros) {
                $query->where('bodega_id', $filtros['bodega_id']);
            });
        }

        if (isset($filtros['medicamento_id'])) {
            $consulta->whereHas('existencia', function ($query) use ($filtros) {
                $query->where('medicamento_id', $filtros['medicamento_id']);
            });
        }

        if (isset($filtros['lote_id'])) {
            $consulta->whereHas('existencia', function ($query) use ($filtros) {
                $query->where('lote_id', $filtros['lote_id']);
            });
        }

        if (isset($filtros['tipo_movimiento'])) {
            $consulta->where(
                'tipo_movimiento',
                $filtros['tipo_movimiento']
            );
        }

        if (isset($filtros['desde'])) {
            $consulta->whereDate('created_at', '>=', $filtros['desde']);
        }

        if (isset($filtros['hasta'])) {
            $consulta->whereDate('created_at', '<=', $filtros['hasta']);
        }

        return $consulta
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($filtros['per_page'] ?? 25)
            ->withQueryString();
    }
}
