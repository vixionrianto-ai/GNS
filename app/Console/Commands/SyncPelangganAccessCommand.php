<?php

namespace App\Console\Commands;

use App\Models\Pelanggan;
use App\Services\PembayaranService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
                    try {
                        $pembayaranService->syncCustomerAccess($pelanggan);
                        $jumlah++;
                    } catch (\Throwable $e) {
                        $gagal++;
                        Log::error('Gagal sinkronisasi akses pelanggan terjadwal', [
                            'pelanggan_id' => $pelanggan->id,
                            'message' => $e->getMessage(),
                            'exception' => $e::class,
                        ]);
                    }
                }
            });

        $this->info("Sinkronisasi akses selesai. Diproses: {$jumlah}, gagal: {$gagal}.");

        return $gagal > 0 ? self::FAILURE : self::SUCCESS;
    }
}
