
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
    Route::get('/user', function (Request $request) {
        return new UserResource($request->user());
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/inventario', [InventarioController::class, 'index'])->middleware('can:consultar-inventario');

    Route::get('/kardex', [KardexController::class, 'index'])->middleware('can:consultar-kardex');

    Route::get('/inventario/alertas/vencimiento', [InventarioController::class, 'alertasVencimiento']);

    Route::get('/inventario/alertas/stock-minimo', [InventarioController::class, 'alertasStockMinimo']);

    Route::post('/dispensaciones', [DispensacionController::class, 'store'])->middleware('can:dispensar-medicamentos');
    Route::post('/dispensaciones/{dispensacion}/autorizar', [DispensacionController::class, 'autorizar'])->middleware('can:autorizar-control-especial');

    Route::post('/traslados', [TrasladoController::class, 'store'])->middleware('can:crear-traslados');
    Route::get('/traslados', [TrasladoController::class, 'index'])->middleware('can:consultar-inventario');
    //Route::get('/traslados/{traslado}', [TrasladoController::class, 'show'])->middleware('can:consultar-inventario');
    Route::post('/traslados/{traslado}/aprobar', [TrasladoController::class, 'aprobar'])->middleware('can:aprobar-traslados');
    Route::post('/traslados/{traslado}/despachar', [TrasladoController::class, 'despachar'])->middleware('can:aprobar-traslados');
    Route::post('/traslados/{traslado}/recibir', [TrasladoController::class, 'recibir'])->middleware('can:recibir-traslados');
    Route::post('/traslados/{traslado}/anular', [TrasladoController::class, 'anular'])->middleware('can:crear-traslados');

});
