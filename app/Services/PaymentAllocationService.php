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
    protected function generateSaldoInvoiceNo(): string
    {
        $prefix = 'GNSM-SALDO-' . now()->format('Ym') . '-';

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

    public function allocate(Pembayaran $pembayaran, Tagihan $tagihanAwal, float $nominalBayar): array
    {
        return DB::transaction(function () use ($pembayaran, $tagihanAwal, $nominalBayar) {
            $pelanggan = $tagihanAwal->pelanggan;

            $saldo = SaldoPelanggan::milik($pelanggan->id);
            $saldo = SaldoPelanggan::where('id', $saldo->id)->lockForUpdate()->first();

            // FIFO murni: selalu mulai dari tagihan paling lama yang masih memiliki sisa.
            // Tagihan awal tidak boleh diprioritaskan, karena pembayaran harus melunasi
            // kewajiban tertua terlebih dahulu meskipun user membuka invoice yang lebih baru.
            $tagihans = Tagihan::where('pelanggan_id', $pelanggan->id)
                ->whereIn('status', [
                    Tagihan::STATUS_BELUM_BAYAR,
                    Tagihan::STATUS_SEBAGIAN,
                    Tagihan::STATUS_JATUH_TEMPO,
                ])
                ->orderBy('tahun')
                ->orderBy('bulan')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $sisaUang = $nominalBayar;
            $teralokasi = [];

            foreach ($tagihans as $tagihan) {
                if ($sisaUang <= 0) break;

                // Jangan bergantung pada kolom sisa yang mungkin belum tersinkron.
                // Hitung dari alokasi/pembayaran aktual agar FIFO konsisten.
                $sisaTagihan = (float) $tagihan->getSisaTagihan();
                if ($sisaTagihan <= 0) {
                    $tagihan->refreshStatus();
                    continue;
                }

                $nominalDialokasikan = min($sisaTagihan, $sisaUang);

                AlokasiPembayaran::create([
                    'pembayaran_id' => $pembayaran->id,
                    'tagihan_id' => $tagihan->id,
                    'nominal' => $nominalDialokasikan,
                    'keterangan' => 'Alokasi FIFO',
                ]);

                $tagihan->refresh();
                $tagihan->refreshStatus();

                $teralokasi[] = [
                    'tagihan_id' => $tagihan->id,
                    'nominal' => $nominalDialokasikan,
                ];

                $sisaUang -= $nominalDialokasikan;
            }

            if ($sisaUang > 0) {
                $saldo->tambah(
                    $sisaUang,
                    'Kelebihan pembayaran ' . $pembayaran->invoice_no
                );

                AlokasiPembayaran::create([
                    'pembayaran_id' => $pembayaran->id,
                    'tagihan_id' => null,
                    'nominal' => $sisaUang,
                    'keterangan' => 'Masuk saldo pelanggan dari ' . $pembayaran->invoice_no,
                ]);
            }

            return ['alokasi' => $teralokasi, 'saldo' => $sisaUang];
        });
    }

    protected function applyToSingleTagihan(Tagihan $tagihan, float $nominal): float
    {
        if ($nominal <= 0) return 0;

        $tagihan->refresh();
        $sisa = $tagihan->getSisaTagihan();
        if ($sisa <= 0) return 0;

        $dipakai = min($sisa, $nominal);

        $pembayaran = Pembayaran::create([
            'invoice_no' => $this->generateSaldoInvoiceNo(),
            'invoice_date' => now(),
            'tagihan_id' => $tagihan->id,
            'user_id' => Auth::id() ?? 1,
            'tanggal_bayar' => now(),
            'metode' => 'Saldo',
            'nominal' => $dipakai,
            'biaya_admin' => 0,
            'total_bayar' => $dipakai,
            'dibayar' => $dipakai,
            'kembalian' => 0,
            'status' => Pembayaran::STATUS_BERHASIL,
            'keterangan' => 'Pembayaran otomatis menggunakan saldo pelanggan',
        ]);

        AlokasiPembayaran::create([
            'pembayaran_id' => $pembayaran->id,
            'tagihan_id' => $tagihan->id,
            'nominal' => $dipakai,
            'keterangan' => 'Pemakaian saldo pelanggan untuk ' . $tagihan->invoice_no,
        ]);

        $tagihan->refresh();
        $tagihan->refreshStatus();

        return $dipakai;
    }

    public function applySaldo(Tagihan $tagihan): float
    {
        return DB::transaction(function () use ($tagihan) {
            $saldo = SaldoPelanggan::milik($tagihan->pelanggan_id);
            $saldo = SaldoPelanggan::where('id', $saldo->id)->lockForUpdate()->first();

            if ($saldo->saldo <= 0) return 0;

            // Saldo juga wajib mengikuti FIFO. Jangan langsung membayar
            // tagihan yang baru dibuat apabila masih ada tagihan lama yang memiliki sisa.
            $tagihans = Tagihan::where('pelanggan_id', $tagihan->pelanggan_id)
                ->whereIn('status', [
                    Tagihan::STATUS_BELUM_BAYAR,
                    Tagihan::STATUS_SEBAGIAN,
                    Tagihan::STATUS_JATUH_TEMPO,
                ])
                ->orderBy('tahun')
                ->orderBy('bulan')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $saldoTersedia = (float) $saldo->saldo;
            $totalDipakai = 0.0;
            $invoiceTerakhir = null;

            foreach ($tagihans as $tagihanItem) {
                if ($saldoTersedia <= 0) break;

                $tagihanItem->refresh();

                if (! in_array($tagihanItem->status, [
                    Tagihan::STATUS_BELUM_BAYAR,
                    Tagihan::STATUS_SEBAGIAN,
                    Tagihan::STATUS_JATUH_TEMPO,
                ])) {
                    continue;
                }

                $dipakai = $this->applyToSingleTagihan($tagihanItem, $saldoTersedia);
                if ($dipakai <= 0) continue;

                $saldoTersedia -= $dipakai;
                $totalDipakai += $dipakai;
                $invoiceTerakhir = $tagihanItem->invoice_no;

                SaldoUsage::create([
                    'saldo_pelanggan_id' => $saldo->id,
                    'tagihan_id' => $tagihanItem->id,
                    'jumlah' => $dipakai,
                    'tipe' => 'auto',
                    'keterangan' => 'Pemakaian saldo untuk ' . $tagihanItem->invoice_no . ' secara FIFO',
                ]);
            }

            if ($totalDipakai > 0) {
                $saldo->kurangi(
                    $totalDipakai,
                    'Pemakaian saldo untuk ' . $invoiceTerakhir . ' secara FIFO'
                );
            }

            return $totalDipakai;
        });
    }
}
