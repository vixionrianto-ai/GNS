<?php

namespace App\Console\Commands;

use App\Services\IsolationService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('isolation:check {--run : Jalankan isolir pelanggan}')]
#[Description('Menampilkan daftar pelanggan yang memenuhi syarat isolir')]
class IsolationCommand extends Command
{
    public function handle(IsolationService $service): int
    {
        $this->info('========================================');
        $this->info(' GNS Isolation Engine');
        $this->info('========================================');
        $this->newLine();

        $execute = $this->option('run');

        $processed = $service->process($execute);

        if ($processed->isEmpty()) {

            $this->info('Tidak ada pelanggan yang memenuhi syarat isolir.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($processed as $index => $pelanggan) {

            $rows[] = [

                $index + 1,

                $pelanggan->kode_pelanggan,

                $pelanggan->nama,

                $pelanggan->jumlah_periode,

                'Rp ' . number_format(
                    $pelanggan->total_tunggakan,
                    0,
                    ',',
                    '.'
                ),

            ];

        }

        $this->table(

            [
                'No',
                'Kode',
                'Nama',
                'Periode',
                'Total Tunggakan',
            ],

            $rows

        );

        $this->newLine();

        $this->info(
            'Total pelanggan diproses : '
            . count($rows)
            . ' pelanggan'
        );

        return self::SUCCESS;
    }
}