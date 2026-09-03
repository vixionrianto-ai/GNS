<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Billing otomatis: hanya membuat tagihan yang jatuh pada tanggal aktif pelanggan.
// WhatsApp otomatis tetap dikendalikan oleh WHATSAPP_ENABLED dan tidak diaktifkan di sini.
Schedule::command('tagihan:generate')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->runInBackground();
