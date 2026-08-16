<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Setting;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('tagihan:generate')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('isolation:check --run')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('wa:reminder')
    ->dailyAt(Setting::value('whatsapp.send_time', '08:00'))
    ->withoutOverlapping()
    ->runInBackground();
