<?php

namespace App\Services;

use App\Models\AlokasiPembayaran;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class PembayaranService
{
    protected MikroTikService $mikrotik;
    protected InvoiceService $invoiceService;

    public function __construct(MikroTikService $mikrotik, InvoiceService $invoiceService)
    {
        $this->mikrotik = $mikrotik;
        $this->invoiceService = $invoiceService;
    }

    public function bayar(array $data): Pembayaran
    {
        return DB::transaction(function () use ($data) {
            $tagihan = Tagihan::query()->with(['pelanggan.router'])->whereKey($data['tagihan_id'])->lockForUpdate()->firstOrFail();
            $tagihan->refreshStatus();
            $tagihan = $tagihan->fresh(['pelanggan.router']);

            if ($tagihan->status === Tagihan::STATUS_LUNAS) {
                throw new Exception('Tagihan sudah lunas.');
            }

            $biayaAdmin = (float) ($data['biaya_admin'] ?? 0);
            $nominalTagihan = (float) $tagihan->getTotalTagihan();
            $total = $nominalTagihan + $biayaAdmin;
            $dibayar = (float) $data['dibayar'];

            if ($dibayar < $total) {
                throw new Exception('Nominal pembayaran kurang.');
            }

            $pembayaran = Pembayaran::create([
                'invoice_no' => $this->invoiceService->generate(),
                'invoice_date' => now(),
                'invoice_pdf' => null,
                'public_token' => Str::random(64),
                'tagihan_id' => $tagihan->id,
                'user_id' => Auth::id(),
                'tanggal_bayar' => now(),
                'metode' => $data['metode'],
                'nominal' => $nominalTagihan,
                'biaya_admin' => $biayaAdmin,
                'total_bayar' => $total,
                'dibayar' => $dibayar,
                'kembalian' => $dibayar - $total,
                'status' => Pembayaran::STATUS_BERHASIL,
                'keterangan' => $data['keterangan'] ?? null,
            ]);

            // Keep the payment ledger and tagihan allocation in sync.
            // Admin fee is not applied to the customer's invoice balance.
            AlokasiPembayaran::create([
                'pembayaran_id' => $pembayaran->id,
                'tagihan_id' => $tagihan->id,
                'nominal' => $nominalTagihan,
                'keterangan' => 'Alokasi pembayaran tagihan',
            ]);

            $tagihan->refreshStatus();
            $tagihan = $tagihan->fresh(['pelanggan.router']);

            $pelanggan = $tagihan->pelanggan;
            if ($pelanggan && $pelanggan->mikrotik_secret_id) {
                try {
                    $this->mikrotik->enableSecretById($pelanggan->router, $pelanggan->mikrotik_secret_id);
                    $this->mikrotik->disconnectActiveSessionBySecretId($pelanggan->router, $pelanggan->mikrotik_secret_id);
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
