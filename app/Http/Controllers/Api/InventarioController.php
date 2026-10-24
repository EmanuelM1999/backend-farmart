<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ConsultarInventarioRequest;
use App\Http\Resources\ExistenciaResource;
use App\Http\Resources\InventarioMinimoResource;
use App\Services\InventarioService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InventarioController extends Controller
{
    public function __construct(
        private readonly InventarioService $inventarioService
    ) {}

    /**
     * Consulta el inventario con filtros opcionales.
     */
    public function index(ConsultarInventarioRequest $request): AnonymousResourceCollection
    {
        $existencias = $this->inventarioService->consultar(
            $request->validated()
        );

        return ExistenciaResource::collection($existencias);
    }

    /**
     * Devuelve la alerta de vencimiento por los proximos 90 días.
     */
    public function alertasVencimiento(InventarioService $inventarioService)
    {
        return ExistenciaResource::collection(
            $inventarioService->alertasVencimiento()
        );
    }

    /**
     * Devuelve la alerta de stock minimo de una bodega.
     */
    public function alertasStockMinimo(InventarioService $inventarioService)
    {
        return InventarioMinimoResource::collection(
            $inventarioService->alertasStockMinimo()
        );
    }
}
