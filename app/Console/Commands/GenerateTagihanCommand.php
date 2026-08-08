<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TagihanService;

class GenerateTagihanCommand extends Command
{
    /**
     * Nama command.
     */
    protected $signature = 'tagihan:generate';

    /**
     * Deskripsi command.
     */
    protected $description = 'Generate tagihan otomatis dan maintenance harian';

    /**
     * Execute the console command.
     */
    public function handle(TagihanService $tagihanService): int
    {
        $this->info('====================================');
        $this->info(' GNS Billing Engine');
        $this->info(' Generate Tagihan Otomatis');
        $this->info('====================================');
        $this->newLine();

        try {

            $hasil = $tagihanService->generateHarian();

            $maintenance = $tagihanService->updateStatusOtomatis();

            $denda = $tagihanService->updateDenda();

            $this->table(
                ['Proses', 'Jumlah'],
                [
                    ['Berhasil', $hasil['berhasil']],
                    ['Sudah Ada', $hasil['sudah_ada']],
                    ['Gagal', $hasil['gagal']],
                    ['Status Jatuh Tempo', $maintenance],
                    ['Update Denda', $denda],
                ]
            );

            $this->newLine();
            $this->info('Generate tagihan selesai.');

            return self::SUCCESS;

        } catch (\Throwable $e) {

            $this->error($e->getMessage());

            return self::FAILURE;

        }
    }
}