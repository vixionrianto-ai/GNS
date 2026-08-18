<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Tagihan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use App\Services\PaymentAllocationService;

class TagihanService
{
    protected AuditTrailService $auditTrail;
    protected PaymentAllocationService $paymentAllocationService;
    protected WhatsAppService $whatsAppService;

    public function __construct(
        AuditTrailService $auditTrail,
        PaymentAllocationService $paymentAllocationService,
        WhatsAppService $whatsAppService
    ) {
        $this->auditTrail = $auditTrail;
        $this->paymentAllocationService = $paymentAllocationService;
        $this->whatsAppService = $whatsAppService;
    }

    public function generateInvoiceNumber(Carbon $tanggal): string
    {
        $prefix = Setting::invoicePrefix()
            . '-'
            . $tanggal->format('Ym')
            . '-';

        $lastInvoice = Tagihan::where('invoice_no', 'like', $prefix . '%')
            ->latest('id')
            ->lockForUpdate()
            ->first();

        if (!$lastInvoice) {
            return $prefix . '00001';
        }

        $lastNumber = (int) substr($lastInvoice->invoice_no, -5);

        return $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }

    private function getPeriode(?Carbon $tanggal = null): string
    {
        return ($tanggal ?? now())->format('Y-m');
    }

    private function sudahAdaTagihan(int $pelangganId, string $periode): bool
    {
        return Tagihan::where('pelanggan_id', $pelangganId)
            ->where('periode', $periode)
            ->exists();
    }

    private function hitungJatuhTempo(Carbon $tanggal): Carbon
    {
        return $tanggal->copy()->addDays(Setting::dueDays());
    }

    private function getNominal(Pelanggan $pelanggan): float
    {
        return (float) ($pelanggan->paket->harga ?? 0);
    }

    private function logGenerate(Pelanggan $pelanggan, string $pesan, string $level = 'info'): void
    {
        match ($level) {
            'warning' => Log::warning($pesan, ['pelanggan_id' => $pelanggan->id, 'pelanggan' => $pelanggan->nama]),
            'error' => Log::error($pesan, ['pelanggan_id' => $pelanggan->id, 'pelanggan' => $pelanggan->nama]),
            default => Log::info($pesan, ['pelanggan_id' => $pelanggan->id, 'pelanggan' => $pelanggan->nama]),
        };
    }

    public function generate(Pelanggan $pelanggan, bool $sendWhatsApp = true): Tagihan
    {
        return $this->generateUntukPeriode($pelanggan, Carbon::today(), $sendWhatsApp);
    }

    public function generateUntukPeriode(
        Pelanggan $pelanggan,
        Carbon $tanggal,
        bool $sendWhatsApp = true
    ): Tagihan {
        if (empty($pelanggan->tanggal_aktif)) {
            throw new \Exception("Pelanggan {$pelanggan->nama} belum memiliki tanggal aktif.");
        }

        if (!$pelanggan->paket) {
            throw new \Exception("Pelanggan {$pelanggan->nama} belum memiliki paket.");
        }

        $hariIni = $tanggal;
        $periode = $this->getPeriode($hariIni);

        if ($this->sudahAdaTagihan($pelanggan->id, $periode)) {
            throw new \Exception("Tagihan periode {$periode} sudah ada.");
        }

        $tanggalAktif = Carbon::parse($pelanggan->tanggal_aktif);

        $hariTagihan = min(
            $tanggalAktif->day,
            Carbon::create($hariIni->year, $hariIni->month, 1)->daysInMonth
        );

        $tanggalTagihan = Carbon::create($hariIni->year, $hariIni->month, $hariTagihan);
        $tanggalJatuhTempo = $this->hitungJatuhTempo($tanggalTagihan);
        $nominal = $this->getNominal($pelanggan);

        return DB::transaction(function () use (
            $pelanggan,
            $periode,
            $tanggalTagihan,
            $tanggalJatuhTempo,
            $nominal,
            $sendWhatsApp
        ) {
            $tagihan = Tagihan::create([
                'pelanggan_id'        => $pelanggan->id,
                'invoice_no'          => $this->generateInvoiceNumber($tanggalTagihan),
                'periode'             => $periode,
                'bulan'               => $tanggalTagihan->month,
                'tahun'               => $tanggalTagihan->year,
                'tanggal_tagihan'     => $tanggalTagihan,
                'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
                'nominal'             => $nominal,
                'subtotal'            => $nominal,
                'tunggakan'           => 0,
                'denda'               => 0,
                'total'               => $nominal,
                'dibayar'             => 0,
                'sisa'                => $nominal,
                'status'              => Tagihan::STATUS_BELUM_BAYAR,
                'keterangan'          => 'Tagihan Internet Periode ' . $periode,
            ]);

            $this->logGenerate($pelanggan, "Generate invoice {$tagihan->invoice_no}");

            $this->auditTrail->tagihan('generate', 'Generate tagihan ' . $tagihan->invoice_no, [
                'tagihan_id' => $tagihan->id,
                'pelanggan_id' => $pelanggan->id,
                'invoice_no' => $tagihan->invoice_no,
                'periode' => $tagihan->periode,
                'nominal' => $tagihan->nominal,
            ]);

            if (Setting::autoApplySaldo()) {
                $this->paymentAllocationService->applySaldo($tagihan);
                $tagihan->refresh();
            }

            if ($sendWhatsApp) {
                try {
                    $this->whatsAppService->sendTagihan($tagihan);
                } catch (\Throwable $e) {
                    Log::error('Gagal mengirim WhatsApp invoice.', [
                        'invoice' => $tagihan->invoice_no,
                        'pelanggan_id' => $pelanggan->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $tagihan;
        });
    }

    public function generateHarian(): array
    {
        return $this->generateSemua();
    }

    public function generateSemua(?Carbon $periode = null): array
    {
        $pelanggans = Pelanggan::with('paket')
            ->where('status', 'Aktif')
            ->orderBy('nama')
            ->get();

        $berhasil = 0;
        $sudahAda = 0;
        $gagal = 0;

        foreach ($pelanggans as $pelanggan) {
            try {
                $this->generateUntukPeriode($pelanggan, $periode ?? Carbon::today(), true);
                $berhasil++;
            } catch (\Throwable $e) {
                if (str_contains(strtolower($e->getMessage()), 'sudah ada')) {
                    $sudahAda++;
                } else {
                    $gagal++;
                    $this->logGenerate($pelanggan, $e->getMessage(), 'error');
                }
            }
        }

        $this->auditTrail->tagihan('generate_mass', 'Generate seluruh tagihan bulanan', [
            'berhasil' => $berhasil,
            'sudah_ada' => $sudahAda,
            'gagal' => $gagal,
            'periode' => ($periode ?? Carbon::today())->format('Y-m'),
        ]);

        Log::info('Generate Semua Tagihan', [
            'berhasil' => $berhasil,
            'sudah_ada' => $sudahAda,
            'gagal' => $gagal,
        ]);

        return ['berhasil' => $berhasil, 'sudah_ada' => $sudahAda, 'gagal' => $gagal];
    }

    public function generatePeriode(Carbon $periode): array
    {
        $hasil = $this->generateSemua($periode);
        $hasil['periode'] = $periode->format('Y-m');

        $this->auditTrail->tagihan('generate_period', 'Generate tagihan periode ' . $periode->format('Y-m'), $hasil);

        return $hasil;
    }

    public function regenerate(Tagihan $tagihan): Tagihan
    {
        return DB::transaction(function () use ($tagihan) {
            if ($tagihan->alokasi()->exists() || $tagihan->saldoUsages()->exists() || $tagihan->pembayaran()->exists()) {
                throw new \Exception('Invoice sudah memiliki histori transaksi dan tidak dapat diregenerate.');
            }

            $pelanggan = $tagihan->pelanggan()->with('paket')->first();
            $invoiceLama = $tagihan->invoice_no;
            $tagihan->delete();
            $baru = $this->generate($pelanggan, true);

            $this->auditTrail->tagihan('regenerate', 'Regenerate invoice ' . $invoiceLama, [
                'invoice_lama' => $invoiceLama,
                'invoice_baru' => $baru->invoice_no,
                'pelanggan_id' => $pelanggan->id,
            ]);

            return $baru;
        });
    }

    public function updateStatusOtomatis(): int
    {
        $tagihans = Tagihan::where('status', Tagihan::STATUS_BELUM_BAYAR)
            ->whereDate('tanggal_jatuh_tempo', '<', today())
            ->get();

        $jumlah = 0;

        foreach ($tagihans as $tagihan) {
            $tagihan->update(['status' => Tagihan::STATUS_JATUH_TEMPO]);
            $jumlah++;

            $this->auditTrail->tagihan('jatuh_tempo', 'Tagihan jatuh tempo ' . $tagihan->invoice_no, [
                'tagihan_id' => $tagihan->id,
                'invoice_no' => $tagihan->invoice_no,
                'pelanggan_id' => $tagihan->pelanggan_id,
            ]);
        }

        return $jumlah;
    }

    public function updateDenda(): int
    {
        $jumlah = 0;

        $tagihans = Tagihan::where('status', Tagihan::STATUS_JATUH_TEMPO)->get();

        foreach ($tagihans as $tagihan) {
            $hariTerlambat = Carbon::parse($tagihan->tanggal_jatuh_tempo)->diffInDays(today());
            $dendaPerHari = Setting::finePerDay();
            $denda = $hariTerlambat * $dendaPerHari;

            $total = (float) $tagihan->nominal + $denda;
            $dibayar = (float) $tagihan->getTotalDibayar();
            $sisa = max(0, $total - $dibayar);

            $tagihan->update([
                'denda' => $denda,
                'total' => $total,
                'dibayar' => $dibayar,
                'sisa' => $sisa,
                'status' => $sisa <= 0.01
                    ? Tagihan::STATUS_LUNAS
                    : Tagihan::STATUS_JATUH_TEMPO,
            ]);

            $jumlah++;
        }

        Log::info('Update Denda', ['jumlah' => $jumlah]);

        return $jumlah;
    }

    public function maintenanceHarian(): void
    {
        $jatuhTempo = $this->updateStatusOtomatis();
        $updateDenda = $this->updateDenda();

        $this->auditTrail->tagihan('maintenance', 'Maintenance harian tagihan', [
            'status_updated' => $jatuhTempo,
            'denda_updated' => $updateDenda,
            'tanggal' => now()->toDateString(),
        ]);

        Log::info('Maintenance Harian', [
            'status' => $jatuhTempo,
            'denda' => $updateDenda,
        ]);
    }
}
