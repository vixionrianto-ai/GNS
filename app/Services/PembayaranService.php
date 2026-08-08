<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use Exception;
use App\Services\AuditTrailService;
use App\Services\PaymentAllocationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PembayaranService
{
    /*
    |--------------------------------------------------------------------------
    | CONSTANT
    |--------------------------------------------------------------------------
    */

    /**
     * Default biaya admin.
     */
    private const DEFAULT_BIAYA_ADMIN = 0;

    /*
    |--------------------------------------------------------------------------
    | DEPENDENCY
    |--------------------------------------------------------------------------
    */

    protected MikroTikService $mikroTikService;
    protected InvoiceService $invoiceService;
    protected AuditTrailService $auditTrailService;
    protected WhatsAppService $whatsAppService;
    protected PaymentAllocationService $paymentAllocationService;

    /**
     * Constructor.
     */
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

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    /**
     * Ambil tagihan beserta relasi yang dibutuhkan.
     */
    private function getTagihan(int $tagihanId): Tagihan
    {
        return Tagihan::with([
            'pelanggan',
            'pelanggan.router',
        ])->findOrFail($tagihanId);
    }

    /**
     * Hitung biaya admin.
     */
    private function getBiayaAdmin(array $data): float
    {
        return (float) (
            $data['biaya_admin']
            ?? self::DEFAULT_BIAYA_ADMIN
        );
    }

    /**
     * Hitung total pembayaran.
     */
    private function hitungTotal(
        Tagihan $tagihan,
        float $biayaAdmin
    ): float {
        return
            $tagihan->nominal +
            $tagihan->denda +
            $biayaAdmin;
    }

    /**
     * Validasi pembayaran.
     *
     * Mendukung:
     * - pembayaran sebagian
     * - pembayaran penuh
     * - pembayaran lebih
     *
     * @throws Exception
     */
    private function validatePembayaran(
        Tagihan $tagihan,
        float $total,
        float $dibayar
    ): void {
        if ($tagihan->status === Tagihan::STATUS_LUNAS) {
            throw new Exception(
                'Tagihan sudah lunas.'
            );
        }

        if (! in_array(
            $tagihan->status,
            [
                Tagihan::STATUS_BELUM_BAYAR,
                Tagihan::STATUS_SEBAGIAN,
                Tagihan::STATUS_JATUH_TEMPO,
            ]
        )) {
            throw new Exception(
                'Status tagihan tidak dapat diproses untuk pembayaran.'
            );
        }

        if ($dibayar <= 0) {
            throw new Exception(
                'Nominal pembayaran harus lebih besar dari nol.'
            );
        }

        if (! is_numeric($dibayar)) {
            throw new Exception(
                'Nominal pembayaran tidak valid.'
            );
        }
    }

    /**
     * Logging pembayaran.
     */
    private function logPembayaran(
        string $message,
        array $context = [],
        string $level = 'info'
    ): void {
        match ($level) {
            'warning' => Log::warning($message, $context),
            'error'   => Log::error($message, $context),
            default   => Log::info($message, $context),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    /**
     * Proses pembayaran tagihan.
     *
     * @throws \Exception
     */
    public function bayar(array $data): Pembayaran
    {
        return DB::transaction(function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | Ambil Tagihan
            |--------------------------------------------------------------------------
            */

            $tagihan = $this->getTagihan(
                $data['tagihan_id']
            );

            if (empty($data['metode'])) {
                throw new Exception('Metode pembayaran wajib diisi.');
            }

            $dibayar = (float) $data['dibayar'];

            /*
            |--------------------------------------------------------------------------
            | Hitung Total
            |--------------------------------------------------------------------------
            */

            $biayaAdmin = $this->getBiayaAdmin($data);

            $total = $this->hitungTotal(
                $tagihan,
                $biayaAdmin
            );

            /*
            |--------------------------------------------------------------------------
            | Validasi Pembayaran
            |--------------------------------------------------------------------------
            */

            $this->validatePembayaran(
                $tagihan,
                $total,
                $dibayar
            );

            $nominalBersih = $dibayar - $biayaAdmin;

            if ($nominalBersih <= 0) {
                throw new Exception(
                    'Nominal pembayaran harus lebih besar dari biaya admin.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Simpan Pembayaran
            |--------------------------------------------------------------------------
            */

            $pembayaran = $this->simpanPembayaran(
                $tagihan,
                $data,
                $biayaAdmin,
                $total
            );

            /*
            |--------------------------------------------------------------------------
            | Alokasi Pembayaran (FIFO / Cicilan / Saldo)
            |--------------------------------------------------------------------------
            | Yang dialokasikan hanya nominal bersih setelah biaya admin.
            | Biaya admin tidak boleh mengurangi sisa tagihan.
            */

            $this->paymentAllocationService->allocate(
                $pembayaran,
                $tagihan,
                (float) $pembayaran->nominal
            );

            /*
            |--------------------------------------------------------------------------
            | Update kembalian setelah proses alokasi
            |--------------------------------------------------------------------------
            */

            $pembayaran->update([
                /*
                * Untuk GNS:
                * Seluruh kelebihan pembayaran dipakai
                * melunasi tagihan berikutnya atau menjadi
                * saldo pelanggan.
                *
                * Jadi bukan kembalian tunai.
                */
                'kembalian' => 0,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Aktifkan PPPoE MikroTik
            |--------------------------------------------------------------------------
            */

            $masihAdaTagihan = Tagihan::where(
                'pelanggan_id',
                $tagihan->pelanggan_id
            )
            ->where('sisa', '>', 0)
            ->exists();

            if (! $masihAdaTagihan) {
                $this->aktifkanSecretMikrotik($tagihan);
            }

            /*
            |--------------------------------------------------------------------------
            | Kirim WhatsApp + Invoice PDF
            |--------------------------------------------------------------------------
            */

            $waBerhasil = false;

            try {
                $pembayaran->load('tagihan.pelanggan');

                $pdfUrl = url(
                    '/public-invoice/' .
                    $pembayaran->public_token .
                    '/pdf'
                );

                $waBerhasil = $this->whatsAppService->sendInvoicePdf(
                    $pembayaran,
                    $pdfUrl
                );
            } catch (\Throwable $e) {
                Log::warning('WhatsApp pembayaran gagal dikirim.', [
                    'invoice' => $tagihan->invoice_no,
                    'message' => $e->getMessage(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Logging
            |--------------------------------------------------------------------------
            */

            $this->logPembayaran(
                'Pembayaran berhasil',
                [
                    'invoice'      => $pembayaran->invoice_no,
                    'tagihan_id'   => $tagihan->id,
                    'pelanggan_id' => $tagihan->pelanggan_id,
                    'user_id'      => Auth::id(),
                    'total'        => $total,
                ]
            );

            $this->auditTrailService->pembayaran(
                'create',
                'Pembayaran invoice ' . $pembayaran->invoice_no,
                [
                    'invoice_no'  => $pembayaran->invoice_no,
                    'tagihan_id'  => $tagihan->id,
                    'pelanggan_id'=> $tagihan->pelanggan_id,
                    'user_id'     => Auth::id(),
                    'nominal'     => $pembayaran->nominal,
                    'biaya_admin' => $pembayaran->biaya_admin,
                    'total_bayar' => $pembayaran->total_bayar,
                    'metode'      => $pembayaran->metode,
                ]
            );

            $pembayaran->wa_berhasil = $waBerhasil;

            return $pembayaran;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    /**
     * Simpan data pembayaran.
     */
    private function simpanPembayaran(
        Tagihan $tagihan,
        array $data,
        float $biayaAdmin,
        float $total
    ): Pembayaran {
        return Pembayaran::create([
            'invoice_no'    => $tagihan->invoice_no,
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
            'kembalian'     => max(
                0,
                (float) $data['dibayar'] - $total
            ),
            'status'        => Pembayaran::STATUS_BERHASIL,
            'keterangan'    => $data['keterangan'] ?? null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | MIKROTIK
    |--------------------------------------------------------------------------
    */

    /**
     * Aktifkan kembali PPP Secret MikroTik
     * setelah pembayaran berhasil.
     */
    private function aktifkanSecretMikrotik(
        Tagihan $tagihan
    ): void {
        $pelanggan = $tagihan->pelanggan;

        if (
            !$pelanggan ||
            !$pelanggan->mikrotik_secret_id
        ) {
            return;
        }

        try {

            /*
            |--------------------------------------------------------------------------
            | Enable Secret
            |--------------------------------------------------------------------------
            */

            $this->mikroTikService->enableSecretById(
                $pelanggan->router,
                $pelanggan->mikrotik_secret_id
            );

            /*
            |--------------------------------------------------------------------------
            | Disconnect Active Session
            |--------------------------------------------------------------------------
            */

            $this->mikroTikService
                ->disconnectActiveSessionBySecretId(
                    $pelanggan->router,
                    $pelanggan->mikrotik_secret_id
                );

            $pelanggan->is_isolated = false;
            $pelanggan->isolated_at = null;
            $pelanggan->save();

            /*
            |--------------------------------------------------------------------------
            | Logging Success
            |--------------------------------------------------------------------------
            */

            $this->logPembayaran(
                'PPPoE berhasil diaktifkan',
                [
                    'pelanggan_id' => $pelanggan->id,
                    'secret_id'    => $pelanggan->mikrotik_secret_id,
                    'router_id'    => $pelanggan->router_id,
                ]
            );

        } catch (\Throwable $e) {

            $this->logPembayaran(
                'Gagal mengaktifkan PPP Secret',
                [
                    'pelanggan_id' => $pelanggan->id,
                    'secret_id'    => $pelanggan->mikrotik_secret_id,
                    'message'      => $e->getMessage(),
                ],
                'warning'
            );

        }
    }
}