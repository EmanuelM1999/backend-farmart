<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexTrasladoRequest;
use App\Http\Requests\Api\RecibirTrasladoRequest;
use App\Http\Requests\Api\StoreTrasladoRequest;
use App\Http\Resources\TrasladoResource;
use App\Services\TrasladoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TrasladoController extends Controller
{
    public function __construct(
        private readonly TrasladoService $trasladoService
    ) {}

    public function index(IndexTrasladoRequest $request): AnonymousResourceCollection
    {
        $traslados = $this->trasladoService->consultar($request->validated());

        return TrasladoResource::collection($traslados);
    }

    public function store(StoreTrasladoRequest $request): JsonResponse
    {
        $traslado = $this->trasladoService->crearSolicitud(
            $request->validated(),
            $request->user()
        );

        return (new TrasladoResource($traslado))
            ->response()
            ->setStatusCode(201);
    }

    public function aprobar(Request $request, int $traslado): TrasladoResource
    {
        $trasladoAprobado = $this->trasladoService->aprobar(
            $traslado,
            $request->user()
        );

        return new TrasladoResource($trasladoAprobado);
    }

    public function despachar(Request $request, int $traslado): TrasladoResource
    {
        $trasladoDespachado = $this->trasladoService->despachar(
            $traslado,
            $request->user()
        );

        return new TrasladoResource($trasladoDespachado);
    }

    public function recibir(RecibirTrasladoRequest $request, int $traslado): TrasladoResource
    {
        $trasladoRecibido = $this->trasladoService->recibir(
            $traslado,
            $request->validated()['detalles'],
            $request->user()
        );

        return new TrasladoResource($trasladoRecibido);
    }

    public function anular(Request $request, int $traslado, TrasladoService $service): TrasladoResource
    {
        $trasladoAnulado = $this->trasladoService->anular($traslado);

        return new TrasladoResource($trasladoAnulado);
    }
}
