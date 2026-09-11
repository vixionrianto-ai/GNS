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

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('auth.login');
});

Route::get('/public-invoice/{token}/pdf', [PembayaranController::class, 'publicPdf'])
    ->name('pembayaran.public.pdf');

Route::middleware('auth')->group(function () {
    Route::resource('roles', \App\Http\Controllers\RoleController::class)
        ->middlewareFor(['index', 'show'], 'permission:role.view')
        ->middlewareFor(['create', 'store'], 'permission:role.create')
        ->middlewareFor(['edit', 'update'], 'permission:role.edit')
        ->middlewareFor('destroy', 'permission:role.delete');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');
    Route::get('/monitoring-mikrotik', [DashboardController::class, 'monitoring'])
        ->middleware('permission:mikrotik.view')
        ->name('mikrotik.monitor');

    Route::get('/settings', [SettingController::class, 'index'])
        ->middleware('permission:setting.manage')
        ->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])
        ->middleware('permission:setting.manage')
        ->name('settings.update');

    Route::get('/super-admin/reset', [SuperAdminController::class, 'index'])
        ->middleware('role:Super Admin')
        ->name('superadmin.index');
    Route::post('/super-admin/reset', [SuperAdminController::class, 'reset'])
        ->middleware('role:Super Admin')
        ->name('superadmin.reset');

    Route::get('/laporan', [LaporanController::class, 'index'])
        ->middleware('permission:laporan.view')
        ->name('laporan.index');
    Route::get('/laporan/export/pdf', [LaporanController::class, 'exportPdf'])
        ->middleware('permission:laporan.view')
        ->name('laporan.export.pdf');
    Route::get('/laporan/export/excel', [LaporanController::class, 'exportExcel'])
        ->middleware('permission:laporan.view')
        ->name('laporan.export.excel');

    Route::middleware('role:Super Admin')->group(function () {
        Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
        Route::get('/restore', [BackupController::class, 'index'])->name('restore.index');
        Route::post('/backup/create', [BackupController::class, 'create'])->name('backup.create');
        Route::post('/backup/restore', [BackupController::class, 'restore'])->name('backup.restore');
        Route::get('/backup/{file}/download', [BackupController::class, 'download'])->name('backup.download');
        Route::delete('/backup/{file}', [BackupController::class, 'destroy'])->name('backup.destroy');
    });

    Route::resource('router', RouterController::class)
        ->except(['show'])
        ->middlewareFor(['index', 'show'], 'permission:router.view')
        ->middlewareFor(['create', 'store'], 'permission:router.create')
        ->middlewareFor(['edit', 'update'], 'permission:router.edit')
        ->middlewareFor('destroy', 'permission:router.delete');

    Route::get('/router/{id}/test', [RouterController::class, 'test'])
        ->middleware('permission:router.view')
        ->name('router.test');
    Route::get('/router/{id}/ppp-secret', [RouterController::class, 'pppSecret'])
        ->middleware('permission:router.view')
        ->name('router.pppsecret');
    Route::get('/router/{id}/ppp-secret/create', [RouterController::class, 'createSecret'])
        ->middleware('permission:router.create')
        ->name('router.pppsecret.create');
    Route::post('/router/{id}/ppp-secret/store', [RouterController::class, 'storeSecret'])
        ->middleware('permission:router.create')
        ->name('router.pppsecret.store');
    Route::get('/router/{id}/ppp-secret/{username}/edit', [RouterController::class, 'editSecret'])
        ->middleware('permission:router.edit')
        ->name('router.pppsecret.edit');
    Route::put('/router/{id}/ppp-secret/{secret}', [RouterController::class, 'updateSecret'])
        ->middleware('permission:router.edit')
        ->name('router.pppsecret.update');
    Route::delete('/router/{id}/ppp-secret/{secret}', [RouterController::class, 'deleteSecret'])
        ->middleware('permission:router.delete')
        ->name('router.pppsecret.delete');
    Route::put('/router/{id}/ppp-secret/{secret}/enable', [RouterController::class, 'enableSecret'])
        ->middleware('permission:router.edit')
        ->name('router.pppsecret.enable');
    Route::put('/router/{id}/ppp-secret/{secret}/disable', [RouterController::class, 'disableSecret'])
        ->middleware('permission:router.edit')
        ->name('router.pppsecret.disable');
    Route::get('/router/{id}/ppp-active', [RouterController::class, 'pppActive'])
        ->middleware('permission:router.view')
        ->name('router.pppactive');
    Route::delete('/router/{id}/ppp-active/{session}/disconnect', [RouterController::class, 'disconnectSession'])
        ->middleware('permission:router.delete')
        ->name('router.pppactive.disconnect');
    Route::get('/router/{id}/ppp-profile', [RouterController::class, 'pppProfile'])
        ->middleware('permission:router.view')
        ->name('router.pppprofile');
    Route::get('/router/{id}/ppp-profile/create', [RouterController::class, 'createProfile'])
        ->middleware('permission:router.create')
        ->name('router.pppprofile.create');
    Route::post('/router/{id}/ppp-profile/store', [RouterController::class, 'storeProfile'])
        ->middleware('permission:router.create')
        ->name('router.pppprofile.store');
    Route::get('/router/{id}/ppp-profile/{profile}/edit', [RouterController::class, 'editProfile'])
        ->middleware('permission:router.edit')
        ->name('router.pppprofile.edit');
    Route::put('/router/{id}/ppp-profile/{profile}', [RouterController::class, 'updateProfile'])
        ->middleware('permission:router.edit')
        ->name('router.pppprofile.update');
    Route::delete('/router/{id}/ppp-profile/{profile}', [RouterController::class, 'deleteProfile'])
        ->middleware('permission:router.delete')
        ->name('router.pppprofile.delete');

    Route::resource('paket', PaketController::class)
        ->except(['show'])
        ->middlewareFor('index', 'permission:paket.view')
        ->middlewareFor(['create', 'store'], 'permission:paket.create')
        ->middlewareFor(['edit', 'update'], 'permission:paket.edit')
        ->middlewareFor('destroy', 'permission:paket.delete');
    Route::get('/router/{router}/profiles', [PaketController::class, 'getProfiles'])
        ->middleware('permission:paket.view')
        ->name('paket.getProfiles');

    Route::resource('pelanggan', PelangganController::class)
        ->middlewareFor(['index', 'show'], 'permission:pelanggan.view')
        ->middlewareFor(['create', 'store'], 'permission:pelanggan.create')
        ->middlewareFor(['edit', 'update'], 'permission:pelanggan.edit')
        ->middlewareFor('destroy', 'permission:pelanggan.delete');
    Route::post('/pelanggan/sync', [PelangganController::class, 'sync'])
        ->middleware('permission:pelanggan.edit')
        ->name('pelanggan.sync');

    Route::resource('tagihan', TagihanController::class)
        ->except(['create', 'store', 'edit', 'update'])
        ->middlewareFor(['index', 'show'], 'permission:tagihan.view')
        ->middlewareFor('destroy', 'permission:tagihan.delete');
    Route::post('/tagihan/generate-harian', [TagihanController::class, 'generate'])
        ->middleware('permission:tagihan.generate')
        ->name('tagihan.generate');
    Route::post('/tagihan/generate-semua', [TagihanController::class, 'generateSemua'])
        ->middleware('permission:tagihan.generate')
        ->name('tagihan.generate.semua');
    Route::post('/tagihan/generate-periode', [TagihanController::class, 'generatePeriode'])
        ->middleware('permission:tagihan.generate')
        ->name('tagihan.generate.periode');
    Route::get('/tagihan/{tagihan}/whatsapp', [TagihanController::class, 'sendWhatsapp'])
        ->middleware('permission:whatsapp.view')
        ->name('tagihan.whatsapp');
    Route::delete('/tagihan/{tagihan}/batalkan-alokasi', [TagihanController::class, 'destroyWithRollback'])
        ->middleware('permission:tagihan.delete')
        ->name('tagihan.destroy.with-rollback');

    Route::get('/tagihan/{tagihan}/bayar', [PembayaranController::class, 'create'])
        ->middleware('permission:pembayaran.create')
        ->name('pembayaran.create');
    Route::resource('pembayaran', PembayaranController::class)
        ->only(['index', 'show', 'store'])
        ->middlewareFor(['index', 'show'], 'permission:pembayaran.view')
        ->middlewareFor('store', 'permission:pembayaran.create');
    Route::delete('/pembayaran/{pembayaran}', [PembayaranController::class, 'destroy'])
        ->middleware('permission:pembayaran.cancel')
        ->name('pembayaran.destroy');
    Route::get('/pembayaran/{pembayaran}/invoice', [PembayaranController::class, 'invoice'])
        ->middleware('permission:pembayaran.view')
        ->name('pembayaran.invoice');
    Route::get('/pembayaran/{pembayaran}/cetak', [PembayaranController::class, 'print'])
        ->middleware('permission:pembayaran.view')
        ->name('pembayaran.print');
    Route::get('/pembayaran/{pembayaran}/pdf', [PembayaranController::class, 'pdf'])
        ->middleware('permission:pembayaran.view')
        ->name('pembayaran.pdf');

    Route::get('whatsapp', [WhatsAppLogController::class, 'index'])
        ->middleware('permission:whatsapp.view')
        ->name('whatsapp.index');
    Route::get('whatsapp/{whatsapp}', [WhatsAppLogController::class, 'show'])
        ->middleware('permission:whatsapp.view')
        ->name('whatsapp.show');

    Route::resource('users', UserController::class)
        ->middlewareFor(['index', 'show'], 'permission:user.view')
        ->middlewareFor(['create', 'store'], 'permission:user.create')
        ->middlewareFor(['edit', 'update'], 'permission:user.edit')
        ->middlewareFor('destroy', 'permission:user.delete');

    Route::get('/audit', [AuditTrailController::class, 'index'])
        ->middleware('permission:audit.view')
        ->name('audit.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
