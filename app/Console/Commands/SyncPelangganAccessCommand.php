<?php

namespace App\Console\Commands;

use App\Models\Pelanggan;
use App\Services\PembayaranService;
use Illuminate\Console\Command;

class SyncPelangganAccessCommand extends Command
{
    protected $signature = 'pelanggan:sync-access';

    protected $description = 'Sinkronkan status akses PPP pelanggan berdasarkan status pelanggan dan tagihan jatuh tempo';

    public function handle(PembayaranService $pembayaranService): int
    {
        $jumlah = 0;
        $gagal = 0;

        Pelanggan::query()
            ->with('router')
            ->whereNotNull('router_id')
            ->whereNotNull('mikrotik_secret_id')
            ->chunkById(100, function ($pelanggans) use ($pembayaranService, &$jumlah, &$gagal): void {
                foreach ($pelanggans as $pelanggan) {
                    if ($pembayaranService->syncCustomerAccess($pelanggan)) {
                        $jumlah++;
                        continue;
                    }

                    $gagal++;
                }
            });

        $this->info("Sinkronisasi akses selesai. Diproses: {$jumlah}, gagal: {$gagal}.");

        return $gagal > 0 ? self::FAILURE : self::SUCCESS;
    }
}
