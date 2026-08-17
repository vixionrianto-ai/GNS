<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\OperatorController;
use App\Http\Controllers\Api\PelangganController;
use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\TagihanController;
use App\Http\Controllers\Api\RouterController;
use App\Http\Controllers\Api\PaketController;

/*
|--------------------------------------------------------------------------
| Public API
|--------------------------------------------------------------------------
*/
Route::name('api.')->group(function () {
    Route::apiResource('router', RouterController::class);
});

Route::post(
    'router/{id}/test',
    [RouterController::class, 'testConnection']
);

Route::get(
    'router/{id}/info',
    [RouterController::class, 'info']
);

/*
|--------------------------------------------------------------------------
| Router Read API
|--------------------------------------------------------------------------
*/

Route::get(
    'router/{id}/profiles',
    [RouterController::class, 'profiles']
);

Route::get(
    'router/{id}/secrets',
    [RouterController::class, 'secrets']
);

Route::get(
    'router/{id}/active',
    [RouterController::class, 'active']
);

/*
|--------------------------------------------------------------------------
| Router PPP Secret
|--------------------------------------------------------------------------
*/

Route::post(
    'router/{id}/secret',
    [RouterController::class, 'createSecret']
);

Route::put(
    'router/{id}/secret/{secret}',
    [RouterController::class, 'updateSecret']
);

Route::delete(
    'router/{id}/secret/{secret}',
    [RouterController::class, 'deleteSecret']
);

Route::put(
    'router/{id}/secret/{secret}/enable',
    [RouterController::class, 'enableSecret']
);

Route::put(
    'router/{id}/secret/{secret}/disable',
    [RouterController::class, 'disableSecret']
);

Route::post(
    'router/{id}/secret/{secret}/disconnect',
    [RouterController::class, 'disconnectSecret']
);

Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected API
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Pelanggan
    |--------------------------------------------------------------------------
    */

    Route::get('/pelanggan', [PelangganController::class, 'index']);
    Route::get('/pelanggan/{id}', [PelangganController::class, 'show']);
    Route::get('/pelanggan/{id}/tagihan', [PelangganController::class, 'tagihan']);
    Route::get('/pelanggan/{id}/pembayaran', [PelangganController::class, 'pembayaran']);

    Route::post('/pelanggan', [PelangganController::class, 'store']);
    Route::put('/pelanggan/{id}', [PelangganController::class, 'update']);
    Route::delete('/pelanggan/{id}', [PelangganController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Pembayaran (DIBENAHI: Rute statis ditaruh di atas {id})
    |--------------------------------------------------------------------------
    */

    Route::get('/pembayaran', [PembayaranController::class, 'index']);
    Route::get('/pembayaran/summary', [PembayaranController::class, 'summary']);
    Route::get('/pembayaran/history', [PembayaranController::class, 'history']);
    Route::get('/pembayaran/{id}', [PembayaranController::class, 'show']);
    Route::post('/pembayaran', [PembayaranController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    | Tagihan
    |--------------------------------------------------------------------------
    */
    Route::get(
        '/tagihan',
        [TagihanController::class, 'index']
    );

    Route::post(
        '/tagihan/generate-semua',
        [TagihanController::class, 'generateSemua']
    );

    Route::post(
        '/tagihan/generate-periode',
        [TagihanController::class, 'generatePeriode']
    );

    Route::post(
        '/tagihan/generate/{pelanggan}',
        [TagihanController::class, 'generate']
    );

    Route::post(
        '/tagihan/maintenance',
        [TagihanController::class, 'maintenance']
    );

    Route::post(
        '/tagihan/{tagihan}/regenerate',
        [TagihanController::class, 'regenerate']
    );

    Route::get(
        '/tagihan/{tagihan}/whatsapp',
        [TagihanController::class, 'whatsapp']
    );

    Route::get(
        '/tagihan/{id}',
        [TagihanController::class, 'show']
    );

    /*
    |--------------------------------------------------------------------------
    | Paket
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/paket',
        [PaketController::class, 'index']
    );

    Route::get(
        '/paket/{id}',
        [PaketController::class, 'show']
    );

    Route::post(
        '/paket',
        [PaketController::class, 'store']
    );

    Route::put(
        '/paket/{id}',
        [PaketController::class, 'update']
    );

    Route::delete(
        '/paket/{id}',
        [PaketController::class, 'destroy']
    );

    /*
    |--------------------------------------------------------------------------
    | Operator
    |--------------------------------------------------------------------------
    */

    Route::get('/operator/home', [OperatorController::class, 'home']);
    Route::get(
        '/operator/profile',
        [OperatorController::class, 'profile']
    );

    Route::put(
        '/operator/profile',
        [OperatorController::class, 'updateProfile']
    );

    Route::put(
        '/operator/password',
        [OperatorController::class, 'changePassword']
    );

    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    Route::get('/search', [SearchController::class, 'index']);

});