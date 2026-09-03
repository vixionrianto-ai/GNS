<?php

namespace App\Console\Commands;

use App\Services\TagihanService;
use Illuminate\Console\Command;

class GenerateTagihanCommand extends Command
{
    protected $signature = 'tagihan:generate';

    protected $description = 'Generate tagihan otomatis berdasarkan tanggal aktif pelanggan';

    public function handle(TagihanService $tagihanService): int
    {
        try {
            $jumlah = $tagihanService->generateHarian();

            $this->info("Generate tagihan selesai. {$jumlah} tagihan dibuat.");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            report($e);
            $this->error('Generate tagihan gagal: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
