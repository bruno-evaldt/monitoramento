<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('devices')->group(function () {
    // Estas rotas poderão ser protegidas por token/auth posteriormente.
    Route::post('/{mac_address}/readings', [\App\Http\Controllers\Api\DeviceApiController::class, 'storeReading']);
    Route::get('/{mac_address}/commands', [\App\Http\Controllers\Api\DeviceApiController::class, 'getCommands']);
});
