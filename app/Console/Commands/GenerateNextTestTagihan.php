<?php

namespace App\Console\Commands;

use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Services\TagihanService;
use Carbon\Carbon;

use Illuminate\Console\Attributes\AsCommand;
use Illuminate\Console\Command;

#[AsCommand(
    name: 'tagihan:test-next',
    description: 'Membuat tagihan bulan berikutnya untuk testing'
)]
class GenerateNextTestTagihan extends Command
{
    protected TagihanService $tagihanService;
    protected $signature = 'tagihan:test-next {pelanggan_id}';

    public function __construct(
        TagihanService $tagihanService
    )
    {
        parent::__construct();

        $this->tagihanService = $tagihanService;
    }
    public function handle(): int
    {
        $pelanggan = Pelanggan::find($this->argument('pelanggan_id'));

        if (!$pelanggan) {
            $this->error('Pelanggan tidak ditemukan.');
            return self::FAILURE;
        }

        $tagihanTerakhir = Tagihan::where('pelanggan_id', $pelanggan->id)
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->first();

        if (!$tagihanTerakhir) {
            $this->error('Pelanggan belum memiliki tagihan.');
            return self::FAILURE;
        }

        $periodeBaru = Carbon::create(
            $tagihanTerakhir->tahun,
            $tagihanTerakhir->bulan,
            1
        )->addMonth();

       $tagihan = $this->tagihanService->generateUntukPeriode(
            $pelanggan,
            $periodeBaru
        );
        

        $this->info('Tagihan testing berhasil dibuat.');

        $this->line('Invoice : '.$tagihan->invoice_no);

        $this->line('Periode : '.$periodeBaru->format('Y-m'));

        return self::SUCCESS;
    }
}