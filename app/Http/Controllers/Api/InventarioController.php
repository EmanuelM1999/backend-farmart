<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InventarioService;
use App\Http\Requests\Api\ConsultarInventarioRequest;
use App\Http\Resources\ExistenciaResource;
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
}
