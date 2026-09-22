<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Services\OltService;
use Illuminate\Console\Command;

class OltKuwuCommand extends Command
{
    protected $signature = 'olt:kuwu {username : Username PPP, contoh riono@kuwu} {--mac= : Caller-ID/MAC sebagai pencocokan tambahan}';

    protected $description = 'Baca data ONU OLT KUWU secara read-only tanpa menyentuh koneksi PPP pelanggan';

    public function handle(OltService $olt): int
    {
        $username = trim((string) $this->argument('username'));
        $callerId = trim((string) $this->option('mac'));

        $router = Router::whereRaw('UPPER(nama_router) = ?', ['KUWU'])->first();

        if (!$router) {
            $this->error('Router KUWU tidak ditemukan di database.');
            return self::FAILURE;
        }

        $this->info('Membaca OLT KUWU (READ-ONLY)...');
        $this->line('Pelanggan : ' . $username);

        $data = $olt->findByUsername($router, $username, $callerId !== '' ? $callerId : null);

        if (!$data) {
            $this->error('ONU tidak ditemukan.');
            $this->line('Tidak ada koneksi pelanggan yang diputus atau diubah.');
            return self::FAILURE;
        }

        $this->newLine();
        $this->line('========================================');
        $this->line('OLT KUWU');
        $this->line('========================================');
        $this->line('Pelanggan : ' . ($data['description'] ?? $username));
        $this->line('ONU       : ' . ($data['onu'] ?? '-'));
        $this->line('Status    : ' . ($data['status'] ?? '-'));
        $this->line('MAC       : ' . ($data['mac'] ?? '-'));
        $this->line('Distance  : ' . ($data['distance'] ?? '-') . ' m');
        $this->line('RX Power  : ' . ($data['rx_power'] ?? '-') . ' dBm');
        $this->line('TX Power  : ' . ($data['tx_power'] ?? '-') . ' dBm');
        $this->line('Temp      : ' . ($data['temperature'] ?? '-') . ' C');
        $this->line('Voltage   : ' . ($data['voltage'] ?? '-') . ' V');
        $this->line('TX Bias   : ' . ($data['tx_bias'] ?? '-') . ' mA');
        $this->line('Deregister: ' . ($data['last_deregister_reason'] ?? '-'));
        $this->line('========================================');
        $this->line('READ-ONLY: tidak ada PPP disconnect/restart.');

        return self::SUCCESS;
    }
}
