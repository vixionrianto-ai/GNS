<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembayaran;
use App\Models\AlokasiPembayaran;
use App\Models\Tagihan;
use Illuminate\Support\Facades\DB;

class SyncPaymentAllocations extends Command
{
    protected $signature = 'gns:sync-payment-allocation';

    protected $description = 'Sinkron pembayaran lama ke tabel alokasi_pembayarans';

    public function handle()
    {
        DB::transaction(function () {

            $bar = $this->output->createProgressBar(
                Pembayaran::count()
            );

            Pembayaran::chunk(100, function ($items) use ($bar) {

                foreach ($items as $pembayaran) {

                    if (!$pembayaran->tagihan_id) {
                        $bar->advance();
                        continue;
                    }

                    $sudahAda = AlokasiPembayaran::where(
                        'pembayaran_id',
                        $pembayaran->id
                    )->exists();

                    if ($sudahAda) {
                        $bar->advance();
                        continue;
                    }

                    AlokasiPembayaran::create([

                        'pembayaran_id' => $pembayaran->id,

                        'tagihan_id' => $pembayaran->tagihan_id,

                        'nominal' => $pembayaran->dibayar
                            ?: $pembayaran->nominal
                            ?: $pembayaran->total_bayar,

                        'keterangan' => 'Migrasi data lama',

                    ]);

                    $bar->advance();
                }

            });

            $bar->finish();

        });

        $this->newLine();

        $this->info('Sinkronisasi selesai.');

        Tagihan::chunk(100, function ($tagihans) {

            foreach ($tagihans as $tagihan) {

                $tagihan->refreshStatus();

            }

        });

        $this->info('Semua status tagihan telah diperbarui.');

        return self::SUCCESS;
    }
}