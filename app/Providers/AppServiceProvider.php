<?php

namespace App\Providers;

use App\Services\WhatsApp\FonnteProvider;
use App\Services\WhatsApp\WhatsAppProvider;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(WhatsAppProvider::class, function () {
            return match (config('whatsapp.provider', 'fonnte')) {
                'fonnte' => new FonnteProvider(),
                default => throw new RuntimeException('Provider WhatsApp tidak dikenal: ' . config('whatsapp.provider')),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}