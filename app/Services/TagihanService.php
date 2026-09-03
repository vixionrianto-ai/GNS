<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Tagihan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TagihanService
{
    /**
     * Generate nomor invoice
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
            throw new \Exception(
                "Pelanggan {$pelanggan->nama} belum memiliki tanggal aktif."
            );
        }

        $tanggalAktif = Carbon::parse($pelanggan->tanggal_aktif);
        $hariTagihan = $tanggalAktif->day;
        $hariIni = Carbon::today();

        $periode = $hariIni->format('Y-m');

        $exists = Tagihan::where('pelanggan_id', $pelanggan->id)
            ->where('periode', $periode)
            ->exists();

        if ($exists) {
            throw new \Exception(
                "Tagihan periode {$periode} sudah ada."
            );
        }

        $jumlahHariBulanIni = Carbon::create(
            $hariIni->year,
            $hariIni->month,
            1
        )->daysInMonth;

        $hariTagihan = min($hariTagihan, $jumlahHariBulanIni);

        $tanggalTagihan = Carbon::create(
            $hariIni->year,
            $hariIni->month,
            $hariTagihan
        );

        $tanggalJatuhTempo = $tanggalTagihan
            ->copy()
            ->addDays(10);

        $nominal = $pelanggan->paket->harga;

        return DB::transaction(function () use (
            $pelanggan,
            $periode,
            $tanggalTagihan,
            $tanggalJatuhTempo,
            $nominal
        ) {
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

            if (Carbon::parse($pelanggan->tanggal_aktif)->day != $hariIni) {
                continue;
            }

            try {
                $this->generate($pelanggan);
                $jumlah++;
            } catch (\Exception $e) {
                continue;
            }
        }

        return $jumlah;
    }
}
