<?php

namespace App\Console\Commands;

use App\Services\OltMonitoringService;
use Illuminate\Console\Command;
use Throwable;

class OltMonitorCommand extends Command
{
    protected $signature = 'olt:monitor';

    protected $description = 'Periksa status ONU OLT dan kirim notifikasi perubahan status ke Telegram';

    public function handle(OltMonitoringService $monitor): int
    {
        try {
            $summary = $monitor->run();

            $this->info(sprintf(
                'OLT diperiksa: %d | Online: %d | Offline: %d | Tidak diketahui: %d | Error: %d',
                $summary['checked'],
                $summary['online'],
                $summary['offline'],
                $summary['unknown'],
                $summary['errors']
            ));

            return self::SUCCESS;
        } catch (Throwable $e) {
            report($e);
            $this->error('Monitoring OLT gagal: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
