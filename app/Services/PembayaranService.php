<?php

namespace App\Services;

use App\Models\AlokasiPembayaran;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\Pelanggan;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                return DB::transaction(function () use ($data) {
                    $tagihan = Tagihan::query()
                        ->with(['pelanggan.router'])
                        ->whereKey($data['tagihan_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

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

                    AlokasiPembayaran::create([
                        'pembayaran_id' => $pembayaran->id,
                        'tagihan_id' => $tagihan->id,
                        'nominal' => $nominalTagihan,
                        'keterangan' => 'Alokasi pembayaran tagihan',
                    ]);

                    $tagihan->refreshStatus();
                    $tagihan = $tagihan->fresh(['pelanggan.router']);

                    if ($tagihan->pelanggan) {
                        $pelanggan = $tagihan->pelanggan;
                        DB::afterCommit(function () use ($pelanggan): void {
                            $this->syncCustomerAccess($pelanggan);
                        });
                    }

                    return $pembayaran;
                });
            } catch (QueryException $e) {
                $sqlState = (string) $e->getCode();
                $message = strtolower($e->getMessage());
                $isDuplicate = $sqlState === '23000' && str_contains($message, 'invoice_no');

                if (!$isDuplicate || $attempt === 3) {
                    throw $e;
                }

                usleep(100000 * $attempt);
            }
        }

        throw new Exception('Pembayaran gagal diproses.');
    }

    public function batalkan(Pembayaran $pembayaran): Pembayaran
    {
        return DB::transaction(function () use ($pembayaran) {
            $pembayaran = Pembayaran::query()
                ->with('tagihan.pelanggan.router')
                ->whereKey($pembayaran->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($pembayaran->status === Pembayaran::STATUS_DIBATALKAN) {
                throw new Exception('Pembayaran sudah dibatalkan.');
            }

            $tagihan = Tagihan::query()
                ->whereKey($pembayaran->tagihan_id)
                ->lockForUpdate()
                ->firstOrFail();

            $pembayaran->update([
                'status' => Pembayaran::STATUS_DIBATALKAN,
            ]);

            AlokasiPembayaran::where('pembayaran_id', $pembayaran->id)->delete();
            $tagihan->refreshStatus();
            $tagihan = $tagihan->fresh(['pelanggan.router']);

            if ($tagihan->pelanggan) {
                $pelanggan = $tagihan->pelanggan;
                DB::afterCommit(function () use ($pelanggan): void {
                    $this->syncCustomerAccess($pelanggan);
                });
            }

            return $pembayaran->fresh(['tagihan']);
        });
    }

    /** Synchronize PPP access from current customer and invoice state. */
    public function syncCustomerAccess(Pelanggan $pelanggan): bool
    {
        if (empty($pelanggan->mikrotik_secret_id) || !$pelanggan->router) {
            return true;
        }

        $pelanggan->loadMissing('router');

        $harusDiisolir = $pelanggan->status !== 'Aktif'
            || $pelanggan->tagihans()
                ->where('status', '!=', Tagihan::STATUS_DIBATALKAN)
                ->whereDate('tanggal_jatuh_tempo', '<=', now()->toDateString())
                ->where(function ($query) {
                    $query->where('sisa', '>', 0)
                        ->orWhereNull('sisa');
                })
                ->exists();

        try {
            if ($harusDiisolir) {
                $this->mikrotik->disableSecretById($pelanggan->router, $pelanggan->mikrotik_secret_id);
                $this->mikrotik->disconnectActiveSessionBySecretId($pelanggan->router, $pelanggan->mikrotik_secret_id);
                return true;
            }

            $this->mikrotik->enableSecretById($pelanggan->router, $pelanggan->mikrotik_secret_id);
            return true;
        } catch (\Throwable $e) {
            Log::warning('Gagal sinkronisasi akses PPP', [
                'pelanggan_id' => $pelanggan->id,
                'mikrotik_secret_id' => $pelanggan->mikrotik_secret_id,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
