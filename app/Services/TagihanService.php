<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Tagihan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TagihanService
{
    /**
     * Generate nomor invoice.
     * Contoh: INV-202607-00001
     */
    public function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ym') . '-';

        $last = Tagihan::where('invoice_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        if (!$last) {
            return $prefix . '00001';
        }

        $lastNumber = (int) substr($last->invoice_no, -5);

        return $prefix . str_pad(
            $lastNumber + 1,
            5,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Generate tagihan untuk satu pelanggan.
     */
    public function generate(Pelanggan $pelanggan): Tagihan
    {
        if (empty($pelanggan->tanggal_aktif)) {
            throw new \InvalidArgumentException(
                "Pelanggan {$pelanggan->nama} belum memiliki tanggal aktif."
            );
        }

        if (!$pelanggan->paket) {
            throw new \InvalidArgumentException(
                "Pelanggan {$pelanggan->nama} belum memiliki paket."
            );
        }

        $tanggalAktif = Carbon::parse($pelanggan->tanggal_aktif);
        $hariIni = Carbon::today();
        $periode = $hariIni->format('Y-m');

        $exists = Tagihan::where('pelanggan_id', $pelanggan->id)
            ->where('periode', $periode)
            ->exists();

        if ($exists) {
            throw new \RuntimeException(
                "Tagihan periode {$periode} sudah ada."
            );
        }

        $jumlahHariBulanIni = Carbon::create(
            $hariIni->year,
            $hariIni->month,
            1
        )->daysInMonth;

        $hariTagihan = min($tanggalAktif->day, $jumlahHariBulanIni);

        $tanggalTagihan = Carbon::create(
            $hariIni->year,
            $hariIni->month,
            $hariTagihan
        );

        $tanggalJatuhTempo = $tanggalTagihan->copy()->addDays(10);
        $nominal = (float) $pelanggan->paket->harga;

        return DB::transaction(function () use (
            $pelanggan,
            $periode,
            $tanggalTagihan,
            $tanggalJatuhTempo,
            $nominal
        ) {
            // Re-check inside the transaction so a concurrent manual/cron
            // generation cannot create the same customer's period twice.
            $exists = Tagihan::where('pelanggan_id', $pelanggan->id)
                ->where('periode', $periode)
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                throw new \RuntimeException(
                    "Tagihan periode {$periode} sudah ada."
                );
            }

            return Tagihan::create([
                'pelanggan_id' => $pelanggan->id,
                'invoice_no' => $this->generateInvoiceNumber(),
                'periode' => $periode,
                'bulan' => $tanggalTagihan->month,
                'tahun' => $tanggalTagihan->year,
                'tanggal_tagihan' => $tanggalTagihan,
                'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
                'nominal' => $nominal,
                'denda' => 0,
                'total' => $nominal,
                'dibayar' => 0,
                'sisa' => $nominal,
                'status' => Tagihan::STATUS_BELUM_BAYAR,
                'keterangan' => 'Tagihan Internet Periode ' . $periode,
            ]);
        });
    }

    /**
     * Generate tagihan otomatis berdasarkan tanggal aktif pelanggan.
     */
    public function generateHarian(): int
    {
        $hariIni = now()->day;

        $pelanggans = Pelanggan::with('paket')
            ->where('status', 'Aktif')
            ->get();

        $jumlah = 0;

        foreach ($pelanggans as $pelanggan) {
            if (empty($pelanggan->tanggal_aktif)) {
                continue;
            }

            if (Carbon::parse($pelanggan->tanggal_aktif)->day !== $hariIni) {
                continue;
            }

            try {
                $this->generate($pelanggan);
                $jumlah++;
            } catch (\RuntimeException $e) {
                // Duplicate generation is expected when cron and a manual
                // trigger overlap. Other runtime errors are logged below.
                if (!str_contains($e->getMessage(), 'sudah ada')) {
                    Log::error('Generate tagihan gagal', [
                        'pelanggan_id' => $pelanggan->id,
                        'message' => $e->getMessage(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Generate tagihan gagal', [
                    'pelanggan_id' => $pelanggan->id,
                    'message' => $e->getMessage(),
                    'exception' => $e::class,
                ]);
            }
        }

        return $jumlah;
    }
}
