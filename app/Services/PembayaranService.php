<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class PembayaranService
{
    protected MikroTikService $mikrotik;
    protected InvoiceService $invoiceService;

    public function __construct(
        MikroTikService $mikrotik,
        InvoiceService $invoiceService
    ) {
        $this->mikrotik = $mikrotik;
        $this->invoiceService = $invoiceService;
    }

    /**
     * Proses pembayaran tagihan.
     */
    public function bayar(array $data): Pembayaran
    {
        return DB::transaction(function () use ($data) {
            $tagihan = Tagihan::with([
                'pelanggan.router'
            ])->findOrFail($data['tagihan_id']);

            if ($tagihan->status === Tagihan::STATUS_LUNAS) {
                throw new Exception('Tagihan sudah lunas.');
            }

            $biayaAdmin = (float) ($data['biaya_admin'] ?? 0);
            $total = $tagihan->nominal + $tagihan->denda + $biayaAdmin;

            if ($data['dibayar'] < $total) {
                throw new Exception('Nominal pembayaran kurang.');
            }

            $invoiceNo = $this->invoiceService->generate();

            $pembayaran = Pembayaran::create([
                'invoice_no' => $invoiceNo,
                'invoice_date' => now(),
                'invoice_pdf' => null,
                'tagihan_id' => $tagihan->id,
                'user_id' => Auth::id(),
                'tanggal_bayar' => now(),
                'metode' => $data['metode'],
                'nominal' => $tagihan->nominal,
                'biaya_admin' => $biayaAdmin,
                'total_bayar' => $total,
                'dibayar' => $data['dibayar'],
                'kembalian' => $data['dibayar'] - $total,
                'status' => Pembayaran::STATUS_BERHASIL,
                'keterangan' => $data['keterangan'] ?? null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | UPDATE TAGIHAN
            |--------------------------------------------------------------------------
            | Pembayaran pada flow lama memang harus lunas. Nilai dibayar pada
            | Tagihan dicatat sebesar kewajiban yang tertutup, bukan uang kembalian.
            |--------------------------------------------------------------------------
            */
            $tagihan->update([
                'status' => Tagihan::STATUS_LUNAS,
                'tanggal_bayar' => now(),
                'dibayar' => $total,
                'sisa' => 0,
            ]);

            /*
            |--------------------------------------------------------------------------
            | AKTIFKAN SECRET MIKROTIK
            |--------------------------------------------------------------------------
            */
            $pelanggan = $tagihan->pelanggan;

            if (
                $pelanggan &&
                $pelanggan->mikrotik_secret_id
            ) {
                try {
                    $this->mikrotik->enableSecretById(
                        $pelanggan->router,
                        $pelanggan->mikrotik_secret_id
                    );

                    $this->mikrotik->disconnectActiveSessionBySecretId(
                        $pelanggan->router,
                        $pelanggan->mikrotik_secret_id
                    );
                } catch (\Throwable $e) {
                    \Log::warning('Gagal memperbarui status PPP secret saat pembayaran', [
                        'tagihan_id' => $tagihan->id,
                        'pelanggan_id' => $pelanggan->id,
                        'mikrotik_secret_id' => $pelanggan->mikrotik_secret_id,
                        'message' => $e->getMessage(),
                    ]);
                }
            }

            return $pembayaran;
        });
    }
}
