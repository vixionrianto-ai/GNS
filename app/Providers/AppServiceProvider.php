<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Services\WhatsApp\WhatsAppProvider;
use App\Services\WhatsApp\FonnteProvider;
use App\Models\Pelanggan;
use App\Observers\PelangganObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(

            WhatsAppProvider::class,

            FonnteProvider::class

        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // AdminLTE menggunakan Bootstrap 4
        Paginator::useBootstrapFour();

        // Audit Trail
        Pelanggan::observe(PelangganObserver::class);
    }
}