<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RouterController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\DashboardController;



use App\Models\Router;
use App\Services\MikroTikService;

Route::get('/test-mikrotik', function (MikroTikService $mikrotik) {

    $router = Router::first();

    dd([
        'identity' => $mikrotik->getIdentity($router),
        'version' => $mikrotik->getRouterVersion($router),
    ]);
});

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('auth.login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | ROUTER
    |--------------------------------------------------------------------------
    */

    Route::resource('router', RouterController::class);

    // Test koneksi
    Route::get('/router/{id}/test',
        [RouterController::class, 'test'])
        ->name('router.test');

    /*
    |--------------------------------------------------------------------------
    | PPP SECRET
    |--------------------------------------------------------------------------
    */

    Route::get('/router/{id}/ppp-secret',
        [RouterController::class, 'pppSecret'])
        ->name('router.pppsecret');

    Route::get('/router/{id}/ppp-secret/create',
        [RouterController::class, 'createSecret'])
        ->name('router.pppsecret.create');

    Route::post('/router/{id}/ppp-secret/store',
        [RouterController::class, 'storeSecret'])
        ->name('router.pppsecret.store');

    Route::get('/router/{id}/ppp-secret/{username}/edit',
        [RouterController::class, 'editSecret'])
        ->name('router.pppsecret.edit');

    Route::put('/router/{id}/ppp-secret/{secret}',
        [RouterController::class, 'updateSecret'])
        ->name('router.pppsecret.update');

    Route::delete('/router/{id}/ppp-secret/{secret}',
        [RouterController::class, 'deleteSecret'])
        ->name('router.pppsecret.delete');

    Route::put('/router/{id}/ppp-secret/{secret}/enable',
        [RouterController::class, 'enableSecret'])
        ->name('router.pppsecret.enable');

    Route::put('/router/{id}/ppp-secret/{secret}/disable',
        [RouterController::class, 'disableSecret'])
        ->name('router.pppsecret.disable');

    /*
    |--------------------------------------------------------------------------
    | PPP PROFILE
    |--------------------------------------------------------------------------
    */

    Route::get('/router/{id}/ppp-profile',
        [RouterController::class, 'pppProfile'])
        ->name('router.pppprofile');

    Route::get('/router/{id}/ppp-profile/create',
        [RouterController::class, 'createProfile'])
        ->name('router.pppprofile.create');

    Route::post('/router/{id}/ppp-profile/store',
        [RouterController::class, 'storeProfile'])
        ->name('router.pppprofile.store');

    Route::get('/router/{id}/ppp-profile/{profile}/edit',
        [RouterController::class, 'editProfile'])
        ->name('router.pppprofile.edit');

    Route::put('/router/{id}/ppp-profile/{profile}',
        [RouterController::class, 'updateProfile'])
        ->name('router.pppprofile.update');

    Route::delete('/router/{id}/ppp-profile/{profile}',
        [RouterController::class, 'deleteProfile'])
        ->name('router.pppprofile.delete');

    /*
    |--------------------------------------------------------------------------
    | PAKET
    |--------------------------------------------------------------------------
    */

    Route::resource('paket', PaketController::class);

    Route::get('/router/{router}/profiles',
        [PaketController::class, 'getProfiles'])
        ->name('paket.getProfiles');

    /*
    |--------------------------------------------------------------------------
    | PELANGGAN
    |--------------------------------------------------------------------------
    */

    Route::resource('pelanggan', PelangganController::class);

    Route::post('/pelanggan/sync',
        [PelangganController::class, 'sync'])
        ->name('pelanggan.sync');

    /*
    |--------------------------------------------------------------------------
    | TAGIHAN
    |--------------------------------------------------------------------------
    */

    Route::resource('tagihan', TagihanController::class)
        ->except([
            'create',
            'store',
            'edit',
            'update'
        ]);

    Route::post('/tagihan/generate-harian',
        [TagihanController::class, 'generate'])
        ->name('tagihan.generate');

    /*
    |--------------------------------------------------------------------------
    | PEMBAYARAN
    |--------------------------------------------------------------------------
    */
    Route::get(
        '/tagihan/{tagihan}/bayar',
        [PembayaranController::class, 'create']
    )->name('pembayaran.create');

    Route::resource('pembayaran', PembayaranController::class)
        ->only([
            'index',
            'show',
            'store',
        ]);

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */

    Route::get('/profile',
        [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile',
        [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile',
        [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';