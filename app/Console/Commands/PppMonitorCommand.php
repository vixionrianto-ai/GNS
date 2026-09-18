<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Services\PppMonitoringService;
use Illuminate\Console\Command;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('ppp:monitor')]
#[Description('Sinkronisasi status PPP pelanggan dengan MikroTik')]
class PppMonitorCommand extends Command
{
    public function handle(PppMonitoringService $service): int
    {
        $routers = Router::query()->where('status', 'Aktif')->get();
        if ($routers->isEmpty()) {
            $this->info('Tidak ada router aktif.');
            return self::SUCCESS;
        }

        $online = $offline = $disabled = $routerOffline = 0;

        foreach ($routers as $router) {
            $result = $service->syncRouter($router);
            if ($result['success']) {
                $this->line(sprintf(
                    '%s: ONLINE %d | OFFLINE %d | DISABLED %d | UNKNOWN %d',
                    $router->nama_router, $result['online'], $result['offline'],
                    $result['disabled'], $result['unknown']
                ));
                $online += $result['online'];
                $offline += $result['offline'];
                $disabled += $result['disabled'];
            } else {
                $routerOffline++;
                $this->warn($router->nama_router . ': ROUTER OFFLINE - ' . ($result['error'] ?? 'koneksi gagal'));
            }
        }

        $this->info("Selesai. Online: {$online} | Offline: {$offline} | Disabled: {$disabled} | Router offline: {$routerOffline}");
        return self::SUCCESS;
    }
}
