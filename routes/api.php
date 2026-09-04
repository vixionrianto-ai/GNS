<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PaketController;
use App\Http\Controllers\Api\PelangganController;
use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\RouterController;
use App\Http\Controllers\Api\TagihanController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('api.token')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::apiResource('router', RouterController::class);
        Route::get('/router/{router}/test', [RouterController::class, 'test']);
        Route::get('/router/{router}/ppp-secret', [RouterController::class, 'pppSecret']);
        Route::post('/router/{router}/ppp-secret', [RouterController::class, 'storeSecret']);
        Route::get('/router/{router}/ppp-secret/{username}/edit', [RouterController::class, 'editSecret']);
        Route::put('/router/{router}/ppp-secret/{secret}', [RouterController::class, 'updateSecret']);
        Route::delete('/router/{router}/ppp-secret/{secret}', [RouterController::class, 'deleteSecret']);
        Route::put('/router/{router}/ppp-secret/{secret}/enable', [RouterController::class, 'enableSecret']);
        Route::put('/router/{router}/ppp-secret/{secret}/disable', [RouterController::class, 'disableSecret']);
        Route::get('/router/{router}/ppp-profile', [RouterController::class, 'pppProfile']);
        Route::post('/router/{router}/ppp-profile', [RouterController::class, 'storeProfile']);
        Route::get('/router/{router}/ppp-profile/{profile}/edit', [RouterController::class, 'editProfile']);
        Route::put('/router/{router}/ppp-profile/{profile}', [RouterController::class, 'updateProfile']);
        Route::delete('/router/{router}/ppp-profile/{profile}', [RouterController::class, 'deleteProfile']);

        Route::apiResource('paket', PaketController::class);
        Route::get('/router/{router}/profiles', [PaketController::class, 'profiles']);

        Route::apiResource('pelanggan', PelangganController::class);
        Route::post('/pelanggan/sync', [PelangganController::class, 'sync']);

        Route::apiResource('tagihan', TagihanController::class)->only(['index', 'show', 'destroy']);
        Route::post('/tagihan/generate-harian', [TagihanController::class, 'generate']);

        Route::get('/tagihan/{tagihan}/bayar', [PembayaranController::class, 'create']);
        Route::apiResource('pembayaran', PembayaranController::class)->only(['index', 'show', 'store']);
        Route::post('/pembayaran/{pembayaran}/cancel', [PembayaranController::class, 'cancel']);
        Route::get('/pembayaran/{pembayaran}/invoice', [PembayaranController::class, 'invoice']);
        Route::get('/pembayaran/{pembayaran}/pdf', [PembayaranController::class, 'pdf']);
    });
});
