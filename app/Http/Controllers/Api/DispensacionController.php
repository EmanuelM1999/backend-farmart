<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDispensacionRequest;
use App\Http\Resources\DispensacionResource;
use App\Models\Dispensacion;
use App\Services\DispensacionService;
use Illuminate\Http\JsonResponse;

class DispensacionController extends Controller
{
    public function store(StoreDispensacionRequest $request, DispensacionService $service): JsonResponse|DispensacionResource
    {

        $dispensacion = $service->crearSolicitud(
            $request->validated(),
            $request->user()
        );

        return (new DispensacionResource($dispensacion))
            ->response()
            ->setStatusCode(201);
    }

    public function autorizar(StoreDispensacionRequest $request, Dispensacion $dispensacion, DispensacionService $service): JsonResponse
    {
        $resultado = $service->autorizarSolicitud(
            $dispensacion->id,
            $request->validated(),
            $request->user()
        );

        return (new DispensacionResource($resultado))
            ->response()
            ->setStatusCode(200);
    }
}
