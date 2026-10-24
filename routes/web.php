<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
    ]);
});

Route::get('/ready', function () {
    try {
        DB::select('SELECT 1');

        return response()->json([
            'status' => 'ready',
            'checks' => [
                'database' => 'ok',
            ],
        ]);
    } catch (\Throwable $exception) {
        return response()->json([
            'status' => 'not_ready',
            'checks' => [
                'database' => 'error',
            ],
        ], 503);
    }
});
