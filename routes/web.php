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
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\WhatsAppLogController;

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

/*
|--------------------------------------------------------------------------
| PUBLIC INVOICE PDF
|--------------------------------------------------------------------------
*/

Route::get(
    '/public-invoice/{token}/pdf',
    [PembayaranController::class, 'publicPdf']
)->name('pembayaran.public.pdf');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/monitoring-mikrotik', [DashboardController::class, 'monitoring'])
        ->name('mikrotik.monitor');

    Route::get('/settings', [SettingController::class, 'index'])
        ->name('settings.index');

    Route::post('/settings', [SettingController::class, 'update'])
        ->name('settings.update');

    /* SUPER ADMIN */

    Route::get(
        '/super-admin/reset',
        [SuperAdminController::class, 'index']
    )->name('superadmin.index');

    Route::post(
        '/super-admin/reset',
        [SuperAdminController::class, 'reset']
    )->name('superadmin.reset');

    /* LAPORAN */

    Route::get(
        '/laporan',
        [LaporanController::class, 'index']
    )->name('laporan.index');

    Route::get(
        '/laporan/export/pdf',
        [LaporanController::class, 'exportPdf']
    )->name('laporan.export.pdf');

    Route::get(
        '/laporan/export/excel',
        [LaporanController::class, 'exportExcel']
    )->name('laporan.export.excel');

    /* BACKUP DATABASE */

    Route::get('/backup', [BackupController::class, 'index'])
        ->name('backup.index');

    Route::post('/backup/create', [BackupController::class, 'create'])
        ->name('backup.create');

    Route::post('/backup/restore', [BackupController::class, 'restore'])
        ->name('backup.restore');

    Route::get('/backup/{file}/download', [BackupController::class, 'download'])
        ->name('backup.download');

    Route::delete('/backup/{file}', [BackupController::class, 'destroy'])
        ->name('backup.destroy');

    /* ROUTER & MIKROTIK */

    Route::resource('router', RouterController::class);

    Route::get('/router/{id}/test', [RouterController::class, 'test'])
        ->name('router.test');

    /* PPP SECRET */

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

    /* PPP ACTIVE */

    Route::get('/router/{id}/ppp-active', [RouterController::class, 'pppActive'])
        ->name('router.pppactive');

    Route::delete('/router/{id}/ppp-active/{session}/disconnect', [RouterController::class, 'disconnectSession'])
        ->name('router.pppactive.disconnect');

    /* PPP PROFILE */

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

    /* PAKET */

    Route::resource('paket', PaketController::class);

    Route::get('/router/{router}/profiles', [PaketController::class, 'getProfiles'])
        ->name('paket.getProfiles');

    /* PELANGGAN */

    Route::resource('pelanggan', PelangganController::class);

    Route::post('/pelanggan/sync', [PelangganController::class, 'sync'])
        ->name('pelanggan.sync');

    /* TAGIHAN */

    Route::resource('tagihan', TagihanController::class)
        ->except(['create', 'store', 'edit', 'update']);

    Route::post('/tagihan/generate-harian', [TagihanController::class, 'generate'])
        ->name('tagihan.generate');

    Route::post('/tagihan/generate-semua', [TagihanController::class, 'generateSemua'])
        ->name('tagihan.generate.semua');

    Route::post('/tagihan/generate-periode', [TagihanController::class, 'generatePeriode'])
        ->name('tagihan.generate.periode');

    Route::get('/tagihan/{tagihan}/whatsapp', [TagihanController::class, 'sendWhatsapp'])
        ->name('tagihan.whatsapp');

    /* PEMBAYARAN */

    Route::get('/tagihan/{tagihan}/bayar', [PembayaranController::class, 'create'])
        ->name('pembayaran.create');

    Route::resource('pembayaran', PembayaranController::class)
        ->only(['index', 'show', 'store']);

    Route::get('/pembayaran/{pembayaran}/invoice', [PembayaranController::class, 'invoice'])
        ->name('pembayaran.invoice');

    Route::get('/pembayaran/{pembayaran}/pdf', [PembayaranController::class, 'pdf'])
        ->name('pembayaran.pdf');

    /* RIWAYAT WHATSAPP */

    Route::get('whatsapp', [WhatsAppLogController::class, 'index'])
        ->name('whatsapp.index');

    Route::get('whatsapp/{whatsapp}', [WhatsAppLogController::class, 'show'])
        ->name('whatsapp.show');

    /* USER MANAGEMENT */

    Route::resource('users', UserController::class)
        ->middleware('permission:user.view');

    /* AUDIT TRAIL */

    Route::get('/audit', [AuditTrailController::class, 'index'])
        ->middleware('permission:audit.view')
        ->name('audit.index');

    /* PROFILE */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

});

require __DIR__.'/auth.php';