<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\SaldoPelanggan;
use App\Models\SaldoUsage;
use Exception;
use App\Services\AuditTrailService;
use App\Services\PaymentAllocationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PembayaranService
{
    private const DEFAULT_BIAYA_ADMIN = 0;

    protected MikroTikService $mikroTikService;
    protected InvoiceService $invoiceService;
    protected AuditTrailService $auditTrailService;
    protected WhatsAppService $whatsAppService;
    protected PaymentAllocationService $paymentAllocationService;

    public function __construct(
        MikroTikService $mikroTikService,
        InvoiceService $invoiceService,
        AuditTrailService $auditTrailService,
        WhatsAppService $whatsAppService,
        PaymentAllocationService $paymentAllocationService
    ) {
        $this->mikroTikService = $mikroTikService;
        $this->invoiceService = $invoiceService;
        $this->auditTrailService = $auditTrailService;
        $this->whatsAppService = $whatsAppService;
        $this->paymentAllocationService = $paymentAllocationService;
    }

    private function getTagihan(int $tagihanId): Tagihan
    {
        return Tagihan::with(['pelanggan', 'pelanggan.router'])
            ->findOrFail($tagihanId);
    }

    private function getBiayaAdmin(array $data): float
    {
        return (float) ($data['biaya_admin'] ?? self::DEFAULT_BIAYA_ADMIN);
    }

    private function hitungTotal(Tagihan $tagihan, float $biayaAdmin): float
    {
        return $tagihan->nominal + $tagihan->denda + $biayaAdmin;
    }

    private function validatePembayaran(Tagihan $tagihan, float $total, float $dibayar): void
    {
        if ($tagihan->status === Tagihan::STATUS_LUNAS) {
            throw new Exception('Tagihan sudah lunas.');
        }

        if (! in_array($tagihan->status, [
            Tagihan::STATUS_BELUM_BAYAR,
            Tagihan::STATUS_SEBAGIAN,
            Tagihan::STATUS_JATUH_TEMPO,
        ])) {
            throw new Exception('Status tagihan tidak dapat diproses untuk pembayaran.');
        }

        if ($dibayar <= 0) {
            throw new Exception('Nominal pembayaran harus lebih besar dari nol.');
        }

        if (! is_numeric($dibayar)) {
            throw new Exception('Nominal pembayaran tidak valid.');
        }
    }

    private function logPembayaran(string $message, array $context = [], string $level = 'info'): void
    {
        match ($level) {
            'warning' => Log::warning($message, $context),
            'error'   => Log::error($message, $context),
            default   => Log::info($message, $context),
        };
    }

    private function generateInvoiceNo(): string
    {
        $prefix = 'GNSM-' . now()->format('Ym') . '-';

        do {
            $number = strtoupper(Str::random(8));
            $invoiceNo = $prefix . $number;
        } while (Pembayaran::where('invoice_no', $invoiceNo)->exists());

        return $invoiceNo;
    }

    public function bayar(array $data): Pembayaran
    {
        return DB::transaction(function () use ($data) {
            $tagihan = $this->getTagihan($data['tagihan_id']);

            if (empty($data['metode'])) {
                throw new Exception('Metode pembayaran wajib diisi.');
            }

            $dibayar = (float) $data['dibayar'];
            $biayaAdmin = $this->getBiayaAdmin($data);
            $total = $this->hitungTotal($tagihan, $biayaAdmin);

            $this->validatePembayaran($tagihan, $total, $dibayar);

            $nominalBersih = $dibayar - $biayaAdmin;

            if ($nominalBersih <= 0) {
                throw new Exception('Nominal pembayaran harus lebih besar dari biaya admin.');
            }

            $pembayaran = $this->simpanPembayaran($tagihan, $data, $biayaAdmin, $total);

            $this->paymentAllocationService->allocate(
                $pembayaran,
                $tagihan,
                (float) $pembayaran->nominal
            );

            $pembayaran->update(['kembalian' => 0]);

            $masihAdaTagihan = Tagihan::where('pelanggan_id', $tagihan->pelanggan_id)
                ->where('sisa', '>', 0)
                ->exists();

            if (! $masihAdaTagihan) {
                $this->aktifkanSecretMikrotik($tagihan);
            }

            $waBerhasil = false;

            try {
                $pembayaran->load('tagihan.pelanggan');
                $pdfUrl = url('/public-invoice/' . $pembayaran->public_token . '/pdf');
                $waBerhasil = $this->whatsAppService->sendInvoicePdf($pembayaran, $pdfUrl);
            } catch (\Throwable $e) {
                Log::warning('WhatsApp pembayaran gagal dikirim.', [
                    'invoice' => $pembayaran->invoice_no,
                    'message' => $e->getMessage(),
                ]);
            }

            $this->logPembayaran('Pembayaran berhasil', [
                'invoice'      => $pembayaran->invoice_no,
                'tagihan_id'   => $tagihan->id,
                'pelanggan_id' => $tagihan->pelanggan_id,
                'user_id'      => Auth::id(),
                'total'        => $total,
            ]);

            $this->auditTrailService->pembayaran(
                'create',
                'Pembayaran invoice ' . $pembayaran->invoice_no,
                [
                    'invoice_no'   => $pembayaran->invoice_no,
                    'tagihan_id'   => $tagihan->id,
                    'pelanggan_id' => $tagihan->pelanggan_id,
                    'user_id'      => Auth::id(),
                    'nominal'      => $pembayaran->nominal,
                    'biaya_admin'  => $pembayaran->biaya_admin,
                    'total_bayar'  => $pembayaran->total_bayar,
                    'metode'       => $pembayaran->metode,
                ]
            );

            $pembayaran->wa_berhasil = $waBerhasil;

            return $pembayaran;
        });
    }

    public function batalkan(Pembayaran $pembayaran): Pembayaran
    {
        return DB::transaction(function () use ($pembayaran) {
            $pembayaran->refresh();

            if ($pembayaran->status === Pembayaran::STATUS_DIBATALKAN) {
                throw new Exception('Pembayaran sudah dibatalkan.');
            }

            if ($pembayaran->status !== Pembayaran::STATUS_BERHASIL) {
                throw new Exception('Hanya pembayaran yang berhasil yang dapat dibatalkan.');
            }

            $alokasis = $pembayaran->alokasi()->get(['id', 'tagihan_id', 'nominal']);

            $tagihanAwal = Tagihan::with('pelanggan')
                ->find($pembayaran->tagihan_id);

            if (! $tagihanAwal) {
                throw new Exception('Tagihan asal pembayaran tidak ditemukan.');
            }

            $tagihanIds = $alokasis
                ->whereNotNull('tagihan_id')
                ->pluck('tagihan_id')
                ->unique()
                ->values();

            // Pembayaran lama (terutama pembayaran Saldo) dapat tidak memiliki
            // AlokasiPembayaran. Tagihan asal tetap harus ikut di-refresh.
            if (! $tagihanIds->contains($tagihanAwal->id)) {
                $tagihanIds->push($tagihanAwal->id);
            }

            $saldoDikembalikan = 0.0;
            $saldoUsagesToDelete = collect();

            if ($pembayaran->metode === 'Saldo') {
                $saldoDikembalikan = (float) $alokasis
                    ->whereNotNull('tagihan_id')
                    ->sum('nominal');

                // Kompatibilitas dengan pembayaran Saldo lama yang belum
                // mempunyai AlokasiPembayaran. Hubungkan berdasarkan tagihan,
                // nominal, dan usage auto terbaru.
                if ($saldoDikembalikan <= 0) {
                    $saldo = SaldoPelanggan::milik($tagihanAwal->pelanggan_id);

                    $usage = SaldoUsage::where('saldo_pelanggan_id', $saldo->id)
                        ->where('tagihan_id', $tagihanAwal->id)
                        ->where('jumlah', $pembayaran->nominal)
                        ->where('tipe', 'auto')
                        ->latest('id')
                        ->first();

                    if ($usage) {
                        $saldoDikembalikan = (float) $usage->jumlah;
                        $saldoUsagesToDelete->push($usage);
                    }
                }
            } else {
                $saldoDikembalikan = (float) $alokasis
                    ->whereNull('tagihan_id')
                    ->sum('nominal');
            }

            if ($saldoDikembalikan > 0) {
                $saldo = SaldoPelanggan::milik($tagihanAwal->pelanggan_id);
                $saldo = SaldoPelanggan::where('id', $saldo->id)
                    ->lockForUpdate()
                    ->first();

                $saldo->tambah(
                    $saldoDikembalikan,
                    'Pengembalian pembatalan pembayaran ' . $pembayaran->invoice_no
                );

                if ($pembayaran->metode === 'Saldo') {
                    foreach ($alokasis->whereNotNull('tagihan_id') as $alokasi) {
                        $usage = SaldoUsage::where('saldo_pelanggan_id', $saldo->id)
                            ->where('tagihan_id', $alokasi->tagihan_id)
                            ->where('jumlah', $alokasi->nominal)
                            ->where('tipe', 'auto')
                            ->latest('id')
                            ->first();

                        if ($usage) {
                            $saldoUsagesToDelete->push($usage);
                        }
                    }

                    foreach ($saldoUsagesToDelete->unique('id') as $usage) {
                        $usage->delete();
                    }
                }
            }

            $pembayaran->update(['status' => Pembayaran::STATUS_DIBATALKAN]);

            foreach ($tagihanIds as $tagihanId) {
                $tagihan = Tagihan::find($tagihanId);
                if ($tagihan) {
                    $tagihan->refreshStatus();
                }
            }

            $this->auditTrailService->pembayaran(
                'cancel',
                'Pembayaran dibatalkan: ' . $pembayaran->invoice_no,
                [
                    'invoice_no' => $pembayaran->invoice_no,
                    'tagihan_id' => $pembayaran->tagihan_id,
                    'user_id'    => Auth::id(),
                    'nominal'    => $pembayaran->nominal,
                    'saldo_dikembalikan' => $saldoDikembalikan,
                ]
            );

            return $pembayaran->fresh();
        });
    }

    private function simpanPembayaran(Tagihan $tagihan, array $data, float $biayaAdmin, float $total): Pembayaran
    {
        return Pembayaran::create([
            'invoice_no'    => $this->generateInvoiceNo(),
            'invoice_date'  => now(),
            'invoice_pdf'   => null,
            'public_token'  => Str::uuid()->toString(),
            'tagihan_id'    => $tagihan->id,
            'user_id'       => Auth::id(),
            'tanggal_bayar' => now(),
            'metode'        => $data['metode'],
            'nominal'       => (float) $data['dibayar'] - $biayaAdmin,
            'biaya_admin'   => $biayaAdmin,
            'total_bayar'   => (float) $data['dibayar'],
            'dibayar'       => (float) $data['dibayar'],
            'kembalian'     => max(0, (float) $data['dibayar'] - $total),
            'status'        => Pembayaran::STATUS_BERHASIL,
            'keterangan'    => $data['keterangan'] ?? null,
        ]);
    }

    private function aktifkanSecretMikrotik(Tagihan $tagihan): void
    {
        $pelanggan = $tagihan->pelanggan;

        if (!$pelanggan || !$pelanggan->mikrotik_secret_id) {
            return;
        }

        try {
            $this->mikroTikService->enableSecretById($pelanggan->router, $pelanggan->mikrotik_secret_id);
            $this->mikroTikService->disconnectActiveSessionBySecretId($pelanggan->router, $pelanggan->mikrotik_secret_id);

            $pelanggan->is_isolated = false;
            $pelanggan->isolated_at = null;
            $pelanggan->save();

            $this->logPembayaran('PPPoE berhasil diaktifkan', [
                'pelanggan_id' => $pelanggan->id,
                'secret_id'    => $pelanggan->mikrotik_secret_id,
                'router_id'    => $pelanggan->router_id,
            ]);
        } catch (\Throwable $e) {
            $this->logPembayaran('Gagal mengaktifkan PPP Secret', [
                'pelanggan_id' => $pelanggan->id,
                'secret_id'    => $pelanggan->mikrotik_secret_id,
                'message'      => $e->getMessage(),
            ], 'error');
        }
    }
}
