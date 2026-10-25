<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DispensacionController;
use App\Http\Controllers\Api\InventarioController;
use App\Http\Controllers\Api\KardexController;
use App\Http\Controllers\Api\TrasladoController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    // Autenticación
    Route::get('/user', fn (Request $request) => new UserResource($request->user())
    );

    Route::post('/logout', [AuthController::class, 'logout']);

    // Inventario
    Route::prefix('inventario')->group(function () {

        Route::get('/', [InventarioController::class, 'index'])
            ->middleware('can:consultar-inventario');

        Route::prefix('alertas')->group(function () {

            Route::get('/vencimiento', [
                InventarioController::class,
                'alertasVencimiento',
            ])->middleware('can:consultar-inventario');

            Route::get('/stock-minimo', [
                InventarioController::class,
                'alertasStockMinimo',
            ])->middleware('can:consultar-inventario');

        });
    });

    // Kardex
    Route::get('/kardex', [KardexController::class, 'index'])
        ->middleware('can:consultar-kardex');

    // Dispensaciones
    Route::prefix('dispensaciones')->group(function () {

        Route::post('/', [DispensacionController::class, 'store'])
            ->middleware('can:dispensar-medicamentos');

        Route::post('/{dispensacion}/autorizar', [
            DispensacionController::class,
            'autorizar',
        ])->middleware('can:autorizar-control-especial');

    });

    // Traslados
    Route::prefix('traslados')->group(function () {

        Route::middleware('can:consultar-inventario')->group(function () {
            Route::get('/', [TrasladoController::class, 'index']);

            // Habilitar cuando implementes show:
            // Route::get('/{traslado}', [TrasladoController::class, 'show']);
        });

        Route::post('/', [TrasladoController::class, 'store'])
            ->middleware('can:crear-traslados');

        Route::post('/{traslado}/aprobar', [
            TrasladoController::class,
            'aprobar',
        ])->middleware('can:aprobar-traslados');

        Route::post('/{traslado}/despachar', [
            TrasladoController::class,
            'despachar',
        ])->middleware('can:despachar-traslados');

        Route::post('/{traslado}/recibir', [
            TrasladoController::class,
            'recibir',
        ])->middleware('can:recibir-traslados');

        Route::post('/{traslado}/anular', [
            TrasladoController::class,
            'anular',
        ])->middleware('can:anular-traslados');

    });

});
