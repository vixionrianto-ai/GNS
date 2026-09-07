<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\WhatsAppLogController;
use App\Http\Controllers\RouterController;
use App\Http\Controllers\PaketInternetController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\DashboardAnalitikController;
use App\Http\Controllers\MikrotikMonitoringController;

Route::middleware(['auth'])->group(function () {

    /* DASHBOARD */
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/analitik', [DashboardAnalitikController::class, 'index'])->name('dashboard.analitik');

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

    Route::delete('/tagihan/{tagihan}/batalkan-alokasi', [TagihanController::class, 'destroyWithRollback'])
        ->name('tagihan.destroy.with-rollback');

    /* PEMBAYARAN */
    Route::get('/tagihan/{tagihan}/bayar', [PembayaranController::class, 'create'])
        ->name('pembayaran.create');

    Route::resource('pembayaran', PembayaranController::class)
        ->only(['index', 'show', 'store']);

    Route::delete('/pembayaran/{pembayaran}', [PembayaranController::class, 'destroy'])
        ->name('pembayaran.destroy');

    Route::get('/pembayaran/{pembayaran}/invoice', [PembayaranController::class, 'invoice'])
        ->name('pembayaran.invoice');

    Route::get('/pembayaran/{pembayaran}/pdf', [PembayaranController::class, 'pdf'])
        ->name('pembayaran.pdf');

    /* RIWAYAT WHATSAPP */
    Route::get('whatsapp', [WhatsAppLogController::class, 'index'])
        ->name('whatsapp.index');

    /* ROUTER */
    Route::resource('router', RouterController::class);
    Route::post('/router/{router}/test-connection', [RouterController::class, 'testConnection'])
        ->name('router.test-connection');
    Route::post('/router/{router}/sync', [RouterController::class, 'sync'])
        ->name('router.sync');

    /* PAKET INTERNET */
    Route::resource('paket-internet', PaketInternetController::class)
        ->except(['show']);

    /* LAPORAN */
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');

    /* MIKROTIK */
    Route::get('/mikrotik/monitoring', [MikrotikMonitoringController::class, 'index'])
        ->name('mikrotik.monitoring');
});
