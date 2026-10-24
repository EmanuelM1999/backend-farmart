<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ConsultarKardexRequest;
use App\Http\Resources\InventarioMovimientoResource;
use App\Services\KardexService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class KardexController extends Controller
{
    public function __construct(
        private readonly KardexService $kardexService
    ) {}

    /**
     * Consulta el historial de movimientos de inventario.
     */
    public function index(
        ConsultarKardexRequest $request
    ): AnonymousResourceCollection {
        $movimientos = $this->kardexService->consultar(
            $request->validated()
        );

        return InventarioMovimientoResource::collection($movimientos);
    }
}
