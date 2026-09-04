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

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('auth.login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

/* Public invoice PDF used by WhatsApp messages. */
Route::get('/public-invoice/{token}/pdf', [PembayaranController::class, 'publicPdf'])
    ->where('token', '[A-Za-z0-9]{64}')
    ->name('pembayaran.public.pdf');

Route::middleware('auth')->group(function () {
    Route::resource('router', RouterController::class);

    Route::get('/router/{id}/test', [RouterController::class, 'test'])
        ->name('router.test');
    Route::get('/router/{id}/ppp-secret', [RouterController::class, 'pppSecret'])
        ->name('router.pppsecret');
    Route::get('/router/{id}/ppp-secret/create', [RouterController::class, 'createSecret'])
        ->name('router.pppsecret.create');
    Route::post('/router/{id}/ppp-secret/store', [RouterController::class, 'storeSecret'])
        ->name('router.pppsecret.store');
    Route::get('/router/{id}/ppp-secret/{username}/edit', [RouterController::class, 'editSecret'])
        ->name('router.pppsecret.edit');
    Route::put('/router/{id}/ppp-secret/{secret}', [RouterController::class, 'updateSecret'])
        ->name('router.pppsecret.update');
    Route::delete('/router/{id}/ppp-secret/{secret}', [RouterController::class, 'deleteSecret'])
        ->name('router.pppsecret.delete');
    Route::put('/router/{id}/ppp-secret/{secret}/enable', [RouterController::class, 'enableSecret'])
        ->name('router.pppsecret.enable');
    Route::put('/router/{id}/ppp-secret/{secret}/disable', [RouterController::class, 'disableSecret'])
        ->name('router.pppsecret.disable');

    Route::get('/router/{id}/ppp-profile', [RouterController::class, 'pppProfile'])
        ->name('router.pppprofile');
    Route::get('/router/{id}/ppp-profile/create', [RouterController::class, 'createProfile'])
        ->name('router.pppprofile.create');
    Route::post('/router/{id}/ppp-profile/store', [RouterController::class, 'storeProfile'])
        ->name('router.pppprofile.store');
    Route::get('/router/{id}/ppp-profile/{profile}/edit', [RouterController::class, 'editProfile'])
        ->name('router.pppprofile.edit');
    Route::put('/router/{id}/ppp-profile/{profile}', [RouterController::class, 'updateProfile'])
        ->name('router.pppprofile.update');
    Route::delete('/router/{id}/ppp-profile/{profile}', [RouterController::class, 'deleteProfile'])
        ->name('router.pppprofile.delete');

    Route::resource('paket', PaketController::class);
    Route::get('/router/{router}/profiles', [PaketController::class, 'getProfiles'])
        ->name('paket.getProfiles');

    Route::resource('pelanggan', PelangganController::class);
    Route::post('/pelanggan/sync', [PelangganController::class, 'sync'])
        ->name('pelanggan.sync');

    Route::resource('tagihan', TagihanController::class)
        ->except(['create', 'store', 'edit', 'update']);
    Route::post('/tagihan/generate-harian', [TagihanController::class, 'generate'])
        ->name('tagihan.generate');

    Route::get('/tagihan/{tagihan}/bayar', [PembayaranController::class, 'create'])
        ->name('pembayaran.create');
    Route::resource('pembayaran', PembayaranController::class)
        ->only(['index', 'show', 'store']);
    Route::post('/pembayaran/{pembayaran}/cancel', [PembayaranController::class, 'cancel'])
        ->name('pembayaran.cancel');
    Route::get('/pembayaran/{pembayaran}/invoice', [PembayaranController::class, 'invoice'])
        ->name('pembayaran.invoice');
    Route::get('/pembayaran/{pembayaran}/pdf', [PembayaranController::class, 'pdf'])
        ->name('pembayaran.pdf');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
