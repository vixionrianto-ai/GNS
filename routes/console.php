<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Generate tagihan otomatis setiap hari. WhatsApp otomatis tetap OFF
// selama WHATSAPP_ENABLED=false.
Schedule::command('tagihan:generate')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->runInBackground();

// Setelah proses generate, sinkronkan akses PPP berdasarkan tagihan yang
// sudah jatuh tempo dan status pelanggan.
Schedule::command('pelanggan:sync-access')
    ->dailyAt('00:15')
    ->withoutOverlapping()
    ->runInBackground();
