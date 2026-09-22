<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Services\OltService;
use Illuminate\Console\Command;

class OltKuwuTestCommand extends Command
{
    protected $signature = 'olt:kuwu-test
                            {username : Username PPP yang dicari di OLT KUWU}
                            {--caller-id= : MAC Caller-ID PPP, opsional}';

    protected $description = 'Tes lookup ONU OLT KUWU tanpa memutus pelanggan';

    public function handle(OltService $olt): int
    {
        $router = Router::whereRaw('UPPER(nama_router) = ?', ['KUWU'])->first();

        if (!$router) {
            $this->error('Router KUWU tidak ditemukan di database.');
            return self::FAILURE;
        }

        $username = trim((string) $this->argument('username'));
        $callerId = trim((string) $this->option('caller-id'));

        $this->info("Lookup OLT KUWU: {$username}");
        if ($callerId !== '') {
            $this->line("Caller ID: {$callerId}");
        }

        $result = $olt->findByUsername($router, $username, $callerId !== '' ? $callerId : null);

        if (!$result) {
            $this->error('ONU KUWU tidak ditemukan.');
            return self::FAILURE;
        }

        $this->newLine();
        $this->table(
            ['Field', 'Value'],
            [
                ['ONU', $result['onu'] ?? '-'],
                ['Status', $result['status'] ?? '-'],
                ['MAC', $result['mac'] ?? '-'],
                ['Description', $result['description'] ?? '-'],
                ['Distance', $result['distance'] ?? '-'],
                ['RX Power', $result['rx_power'] ?? '-'],
                ['TX Power', $result['tx_power'] ?? '-'],
                ['Last Deregister Reason', $result['last_deregister_reason'] ?? '-'],
            ]
        );

        return self::SUCCESS;
    }
}
