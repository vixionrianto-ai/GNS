<?php

namespace App\Services;

use App\Models\SaldoPelanggan;
use App\Models\SaldoUsage;
use App\Models\Tagihan;
use App\Models\AlokasiPembayaran;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentAllocationService
{
    /**
     * Generate nomor pembayaran saldo.
     */
    protected function generateSaldoInvoiceNo(): string
    {
        $prefix = 'PAY-SALDO-' . now()->format('Ym') . '-';

        $last = Pembayaran::where('invoice_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $next = 1;

        if ($last) {
            $lastNumber = (int) substr($last->invoice_no, -5);
            $next = $lastNumber + 1;
        }

        return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Alokasikan pembayaran secara FIFO.
     *
     * @param Tagihan $tagihanAwal Tagihan yang dipilih saat pembayaran dimulai.
     * @param float $nominalBayar Nominal bersih yang siap dialokasikan ke tagihan,
     *                            setelah biaya admin.
     *
     * @return array
     */
    public function allocate(
        Pembayaran $pembayaran,
        Tagihan $tagihanAwal,
        float $nominalBayar
    ): array {

        return DB::transaction(function () use (
            $pembayaran,
            $tagihanAwal,
            $nominalBayar
        ) {

            $pelanggan = $tagihanAwal->pelanggan;

            $saldo = SaldoPelanggan::milik(
                $pelanggan->id
            );

            // Kunci saldo selama transaksi agar pembayaran bersamaan tidak overspend saldo.
            $saldo = SaldoPelanggan::where('id', $saldo->id)
                ->lockForUpdate()
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Ambil semua tagihan yang boleh menerima pembayaran
            |--------------------------------------------------------------------------
            | Tagihan Dibatalkan harus selalu dikeluarkan dari FIFO.
            */

            $tagihans = Tagihan::where(
                    'pelanggan_id',
                    $pelanggan->id
                )
                ->whereIn('status', [
                    Tagihan::STATUS_BELUM_BAYAR,
                    Tagihan::STATUS_SEBAGIAN,
                    Tagihan::STATUS_JATUH_TEMPO,
                ])
                ->orderByRaw(
                    'CASE WHEN id = ? THEN 0 ELSE 1 END',
                    [$tagihanAwal->id]
                )
                ->orderBy('tahun')
                ->orderBy('bulan')
                ->lockForUpdate()
                ->get();

            $sisaUang = $nominalBayar;

            $teralokasi = [];

            foreach ($tagihans as $tagihan) {

                if ($sisaUang <= 0) {
                    break;
                }

                /*
                |--------------------------------------------------------------------------
                | Lewati bila sudah tidak memiliki sisa tagihan
                |--------------------------------------------------------------------------
                */

                if ($tagihan->sisa <= 0) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Hitung alokasi pembayaran
                |--------------------------------------------------------------------------
                */

                $nominalDialokasikan = min(
                    $tagihan->sisa,
                    $sisaUang
                );

                /*
                |--------------------------------------------------------------------------
                | Simpan alokasi pembayaran
                |--------------------------------------------------------------------------
                */

                AlokasiPembayaran::create([
                    'pembayaran_id' => $pembayaran->id,
                    'tagihan_id'    => $tagihan->id,
                    'nominal'       => $nominalDialokasikan,
                    'keterangan'    => 'Alokasi FIFO',
                ]);

                /*
                |--------------------------------------------------------------------------
                | Hitung ulang status tagihan
                |--------------------------------------------------------------------------
                */

                $tagihan->refresh();

                $tagihan->refreshStatus();

                $teralokasi[] = [
                    'tagihan_id' => $tagihan->id,
                    'nominal'    => $nominalDialokasikan,
                ];

                $sisaUang -= $nominalDialokasikan;
            }

            /*
            |--------------------------------------------------------------------------
            | Simpan sisa sebagai saldo pelanggan
            |--------------------------------------------------------------------------
            */

            if ($sisaUang > 0) {

                $saldo->tambah(
                    $sisaUang,
                    'Kelebihan pembayaran'
                );

                AlokasiPembayaran::create([
                    'pembayaran_id' => $pembayaran->id,
                    'tagihan_id'    => null,
                    'nominal'       => $sisaUang,
                    'keterangan'    => 'Masuk saldo pelanggan',
                ]);

            }

            return [
                'alokasi' => $teralokasi,
                'saldo' => $sisaUang,
            ];

        });

    }

    protected function applyToSingleTagihan(
        Tagihan $tagihan,
        float $nominal
    ): float {

        if ($nominal <= 0) {
            return 0;
        }

        // Pastikan data terbaru
        $tagihan->refresh();

        // Hitung ulang dari alokasi yang sudah ada
        $sisa = $tagihan->getSisaTagihan();

        if ($sisa <= 0) {
            return 0;
        }

        $dipakai = min($sisa, $nominal);

        /*
        |--------------------------------------------------------------------------
        | Buat pembayaran internal dari saldo pelanggan
        |--------------------------------------------------------------------------
        */

        $pembayaran = Pembayaran::create([
            'invoice_no'    => $this->generateSaldoInvoiceNo(),
            'invoice_date'  => now(),
            'tagihan_id'    => $tagihan->id,
            'user_id'       => Auth::id() ?? 1,
            'tanggal_bayar' => now(),
            'metode'        => 'Saldo',
            'nominal'       => $dipakai,
            'biaya_admin'   => 0,
            'total_bayar'   => $dipakai,
            'dibayar'       => $dipakai,
            'kembalian'     => 0,
            'status'        => Pembayaran::STATUS_BERHASIL,
            'keterangan'    => 'Pembayaran otomatis menggunakan saldo pelanggan',
        ]);

        AlokasiPembayaran::create([
            'pembayaran_id' => $pembayaran->id,
            'tagihan_id'    => $tagihan->id,
            'nominal'       => $dipakai,
            'keterangan'    => 'Pemakaian saldo pelanggan',
        ]);

        // Refresh status berdasarkan seluruh alokasi
        $tagihan->refresh();

        $tagihan->refreshStatus();

        return $dipakai;
    }

    /**
     * Gunakan saldo pelanggan untuk melunasi tagihan.
     */
    public function applySaldo(Tagihan $tagihan): float
    {
        return DB::transaction(function () use ($tagihan) {

            $saldo = SaldoPelanggan::milik(
                $tagihan->pelanggan_id
            );

            // Kunci saldo selama transaksi agar tidak dapat dipakai dua kali secara bersamaan.
            $saldo = SaldoPelanggan::where('id', $saldo->id)
                ->lockForUpdate()
                ->first();

            // Tidak ada saldo
            if ($saldo->saldo <= 0) {
                return 0;
            }

            // Refresh data tagihan sebelum memutuskan apakah boleh dibayar
            $tagihan->refresh();

            // Hanya tagihan aktif yang boleh menerima pemakaian saldo
            if (! in_array(
                $tagihan->status,
                [
                    Tagihan::STATUS_BELUM_BAYAR,
                    Tagihan::STATUS_SEBAGIAN,
                    Tagihan::STATUS_JATUH_TEMPO,
                ]
            )) {
                return 0;
            }

            $dipakai = $this->applyToSingleTagihan(
                $tagihan,
                (float) $saldo->saldo
            );

            if ($dipakai <= 0) {
                return 0;
            }

            /*
            |--------------------------------------------------------------------------
            | Kurangi saldo pelanggan
            |--------------------------------------------------------------------------
            */

            $saldo->kurangi(
                $dipakai,
                'Pemakaian saldo untuk invoice ' .
                $tagihan->invoice_no
            );

            /*
            |--------------------------------------------------------------------------
            | Simpan histori pemakaian saldo
            |--------------------------------------------------------------------------
            */

            SaldoUsage::create([
                'saldo_pelanggan_id' => $saldo->id,
                'tagihan_id'         => $tagihan->id,
                'jumlah'             => $dipakai,
                'tipe'               => 'auto',
                'keterangan'         => 'Auto Apply Saldo',
            ]);

            return $dipakai;

        });
    }
}