<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PaketController;
use App\Http\Controllers\Api\RouterController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::apiResource('router', RouterController::class);
        Route::get('/router/{router}/test', [RouterController::class, 'test']);

        Route::apiResource('paket', PaketController::class);
        Route::get('/router/{router}/profiles', [PaketController::class, 'profiles']);
    });
});
