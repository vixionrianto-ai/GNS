<?php

namespace App\Services;

use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\Pelanggan;
use App\Services\WhatsApp\WhatsAppProvider;
use Illuminate\Support\Facades\Log;
use App\Models\WhatsAppLog;
use App\Models\Setting;

class WhatsAppService
{
    protected WhatsAppProvider $provider;
    protected ?string $lastResponse = null;

    public function __construct(WhatsAppProvider $provider)
    {
        $this->provider = $provider;
    }

    public function formatNomor(string|null $nomor): ?string
    {
        if (empty($nomor)) return null;
        $nomor = preg_replace('/[^0-9]/', '', $nomor);
        if (str_starts_with($nomor, '0')) $nomor = '62' . substr($nomor, 1);
        return $nomor;
    }

    public function url(?string $nomor, string $pesan): ?string
    {
        $nomor = $this->formatNomor($nomor);
        if (!$nomor) return null;
        return "https://web.whatsapp.com/send?phone={$nomor}&text=" . urlencode($pesan);
    }

    public function tagihan(Tagihan $tagihan): ?string
    {
        return $this->url($tagihan->pelanggan->no_hp, $this->pesanTagihanBaru($tagihan));
    }

    public function reminder3(Tagihan $tagihan): ?string
    {
        return $this->url($tagihan->pelanggan->no_hp, $this->pesanReminder3Hari($tagihan));
    }

    public function reminder7(Tagihan $tagihan): ?string
    {
        return $this->url($tagihan->pelanggan->no_hp, $this->pesanReminder7Hari($tagihan));
    }

    public function pembayaran(Pembayaran $pembayaran): ?string
    {
        return $this->url(
            $pembayaran->tagihan->pelanggan->no_hp,
            $this->pesanPembayaran($pembayaran)
        );
    }

    protected function rupiah($nilai): string
    {
        return number_format($nilai, 0, ',', '.');
    }

    protected function template(string $settingKey, array $replace): string
    {
        $template = Setting::value($settingKey);
        if (!$template) return '';

        foreach ($replace as $key => $value) {
            $template = str_replace('{' . $key . '}', (string) $value, $template);
        }

        return $template;
    }

    /**
     * Placeholder tagihan.
     *
     * Rincian WhatsApp hanya menampilkan kewajiban yang MASIH HARUS DIBAYAR.
     * Tagihan lunas tidak ditampilkan. Urutan mengikuti FIFO: tahun lalu bulan.
     * Nilai sisa berasal dari Tagihan::getSisaTagihan(), sehingga mengikuti
     * AlokasiPembayaran/FIFO yang sudah berjalan di sistem.
     */
    protected function tagihanPlaceholder(Tagihan $tagihan): array
    {
        $pelanggan = $tagihan->pelanggan;

        $tagihans = Tagihan::where('pelanggan_id', $pelanggan->id)
            ->where('status', '!=', Tagihan::STATUS_DIBATALKAN)
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->orderBy('id')
            ->get();

        $rincian = [];
        $totalTagihan = 0;
        $totalDibayar = 0;
        $totalSisa = 0;
        $nomorRincian = 0;

        foreach ($tagihans as $item) {
            $tagihanTotal = (float) $item->getTotalTagihan();
            $dibayar = (float) $item->getTotalDibayar();
            $sisa = (float) $item->getSisaTagihan();

            $totalTagihan += $tagihanTotal;
            $totalDibayar += $dibayar;
            $totalSisa += $sisa;

            // Jangan masukkan tagihan yang sudah lunas ke rincian kewajiban.
            if ($sisa <= 0) continue;

            $nomorRincian++;

            $statusIcon = match ($item->status) {
                Tagihan::STATUS_SEBAGIAN => '🟡',
                Tagihan::STATUS_JATUH_TEMPO => '⏰',
                Tagihan::STATUS_BELUM_BAYAR => '🔴',
                default => '⚪',
            };

            $rincian[] =
                $nomorRincian . ".\n" .
                "📅 Periode : " . optional($item->tanggal_tagihan)->translatedFormat('F Y') . "\n" .
                "📄 Invoice : {$item->invoice_no}\n" .
                "{$statusIcon} Status : {$item->status}\n" .
                "❗ Sisa yang harus dibayar : Rp " . $this->rupiah($sisa) . "\n";
        }

        $rincianText = $rincian
            ? implode("\n────────────────────\n", $rincian)
            : 'Tidak ada tagihan yang masih harus dibayar.';

        return [
            'nama' => $pelanggan->nama,
            'invoice' => $tagihan->invoice_no,
            'periode' => $tagihan->periode,
            'bulan' => $tagihan->bulan,
            'tahun' => $tagihan->tahun,
            'nominal' => 'Rp ' . $this->rupiah($tagihan->nominal),
            'denda' => 'Rp ' . $this->rupiah($tagihan->denda),
            'total' => 'Rp ' . $this->rupiah($tagihan->total),
            'jatuh_tempo' => optional($tagihan->tanggal_jatuh_tempo)->format('d-m-Y'),
            'isp' => config('app.name'),
            'rincian_tagihan' => $rincianText,
            'jumlah_tagihan' => $nomorRincian,
            'total_tagihan' => 'Rp ' . $this->rupiah($totalTagihan),
            'total_dibayar' => 'Rp ' . $this->rupiah($totalDibayar),
            'total_sisa' => 'Rp ' . $this->rupiah($totalSisa),
            'total_harus_dibayar' => 'Rp ' . $this->rupiah($totalSisa),
        ];
    }

    protected function pembayaranPlaceholder(Pembayaran $pembayaran): array
    {
        $tagihan = $pembayaran->tagihan;
        $pelanggan = $tagihan->pelanggan;
        $tagihanData = $this->tagihanPlaceholder($tagihan);

        return array_merge([
            'nama' => $pelanggan->nama,
            'invoice' => $tagihan->invoice_no,
            'periode' => $tagihan->periode,
            'bulan' => $tagihan->bulan,
            'tahun' => $tagihan->tahun,
            'nominal' => 'Rp ' . $this->rupiah($tagihan->nominal),
            'denda' => 'Rp ' . $this->rupiah($tagihan->denda),
            'total' => 'Rp ' . $this->rupiah($pembayaran->total_bayar),
            'jatuh_tempo' => optional($tagihan->tanggal_jatuh_tempo)->format('d-m-Y'),
            'tanggal_bayar' => optional($pembayaran->tanggal_bayar)->format('d-m-Y H:i'),
            'isp' => config('app.name'),
        ], [
            'rincian_tagihan' => $tagihanData['rincian_tagihan'],
            'jumlah_tagihan' => $tagihanData['jumlah_tagihan'],
            'total_tagihan' => $tagihanData['total_tagihan'],
            'total_dibayar' => $tagihanData['total_dibayar'],
            'total_sisa' => $tagihanData['total_sisa'],
            'total_harus_dibayar' => $tagihanData['total_harus_dibayar'],
        ]);
    }

    protected function pelangganPlaceholder(Pelanggan $pelanggan): array
    {
        return [
            'nama' => $pelanggan->nama,
            'isp' => config('app.name'),
        ];
    }

    public function pesanTagihanBaru(Tagihan $tagihan): string
    {
        return $this->template('whatsapp.template_invoice', $this->tagihanPlaceholder($tagihan));
    }

    public function pesanReminder3Hari(Tagihan $tagihan): string
    {
        return $this->template('whatsapp.template_h3', $this->tagihanPlaceholder($tagihan));
    }

    public function pesanReminder7Hari(Tagihan $tagihan): string
    {
        return $this->template('whatsapp.template_h7', $this->tagihanPlaceholder($tagihan));
    }

    public function pesanPembayaran(Pembayaran $pembayaran): string
    {
        return $this->template('whatsapp.template_paid', $this->pembayaranPlaceholder($pembayaran));
    }

    public function pesanIsolir(Pelanggan $pelanggan): string
    {
        return $this->template('whatsapp.template_isolir', $this->pelangganPlaceholder($pelanggan));
    }

    public function sendReminder(Tagihan $tagihan, string $jenis): bool
    {
        if ($this->sudahPernahKirim($tagihan, $jenis)) {
            Log::info('Reminder sudah pernah dikirim.', ['tagihan_id' => $tagihan->id, 'jenis' => $jenis]);
            return false;
        }

        $pesan = match ($jenis) {
            'h3' => $this->pesanReminder3Hari($tagihan),
            'h7' => $this->pesanReminder7Hari($tagihan),
            default => null,
        };

        if ($pesan === null) return false;

        $berhasil = $this->kirim($tagihan->pelanggan->no_hp, $pesan);

        $this->simpanLog(
            $tagihan->pelanggan,
            $tagihan,
            $jenis,
            $tagihan->pelanggan->no_hp,
            $pesan,
            $berhasil,
            $this->lastResponse
        );

        return $berhasil;
    }

    public function sendTagihan(Tagihan $tagihan): bool
    {
        $pesan = $this->pesanTagihanBaru($tagihan);
        $berhasil = $this->kirim($tagihan->pelanggan->no_hp, $pesan);

        $this->simpanLog($tagihan->pelanggan, $tagihan, 'tagihan', $tagihan->pelanggan->no_hp, $pesan, $berhasil, $this->lastResponse);
        return $berhasil;
    }

    public function sendPembayaran(Pembayaran $pembayaran): bool
    {
        $pesan = $this->pesanPembayaran($pembayaran);
        $berhasil = $this->kirim($pembayaran->tagihan->pelanggan->no_hp, $pesan);

        $this->simpanLog($pembayaran->tagihan->pelanggan, $pembayaran->tagihan, 'pembayaran', $pembayaran->tagihan->pelanggan->no_hp, $pesan, $berhasil, $this->lastResponse);
        return $berhasil;
    }

    public function sudahPernahKirim(Tagihan $tagihan, string $jenis): bool
    {
        return WhatsAppLog::where('tagihan_id', $tagihan->id)
            ->where('jenis', $jenis)
            ->where('status', 'success')
            ->exists();
    }

    public function kirim(string $nomor, string $pesan): bool
    {
        if (config('app.whatsapp_enabled', env('WHATSAPP_ENABLED', true)) === false) {
            Log::info('Pengiriman WhatsApp dilewati karena WHATSAPP_ENABLED bernilai false.');
            return false;
        }

        $nomor = $this->formatNomor($nomor);
        if (!$nomor) {
            Log::warning('Nomor WhatsApp kosong.');
            return false;
        }

        try {
            $start = microtime(true);
            $hasil = $this->provider->send($nomor, $pesan);
            $response = null;

            if (method_exists($this->provider, 'lastResponse')) {
                $last = $this->provider->lastResponse();
                if ($last) {
                    $response = json_encode($last, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                }
            }

            $this->lastResponse = $response;
            Log::info('WhatsApp Response Time', [
                'provider' => get_class($this->provider),
                'seconds' => round(microtime(true) - $start, 3),
            ]);

            if ($hasil) {
                Log::info('WhatsApp berhasil dikirim.', ['nomor' => $nomor]);
            } else {
                Log::warning('WhatsApp gagal dikirim.', ['nomor' => $nomor]);
            }

            return $hasil;
        } catch (\Throwable $e) {
            Log::error('WhatsApp Exception', [
                'nomor' => $nomor,
                'provider' => get_class($this->provider),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    public function simpanLog(Pelanggan $pelanggan, ?Tagihan $tagihan, string $jenis, string $nomor, string $pesan, bool $berhasil, ?string $response = null): void
    {
        WhatsAppLog::create([
            'pelanggan_id' => $pelanggan->id,
            'tagihan_id' => $tagihan?->id,
            'jenis' => $jenis,
            'provider' => setting('whatsapp.provider', 'fonnte'),
            'nomor' => $nomor,
            'pesan' => $pesan,
            'status' => $berhasil ? 'success' : 'failed',
            'response' => $response,
            'sent_at' => now(),
        ]);
    }

    public function sendInvoicePdf(Pembayaran $pembayaran, string $documentUrl): bool
    {
        if (config('app.whatsapp_enabled', env('WHATSAPP_ENABLED', true)) === false) {
            Log::info('Pengiriman Invoice PDF WhatsApp dilewati karena WHATSAPP_ENABLED bernilai false.');
            return false;
        }

        $pesan = $this->pesanPembayaran($pembayaran);
        $berhasil = $this->provider->sendDocument(
            $this->formatNomor($pembayaran->tagihan->pelanggan->no_hp),
            $pesan,
            $documentUrl
        );

        if (method_exists($this->provider, 'lastResponse')) {
            $last = $this->provider->lastResponse();
            $this->lastResponse = $last
                ? json_encode($last, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                : null;
        }

        $this->simpanLog(
            $pembayaran->tagihan->pelanggan,
            $pembayaran->tagihan,
            'pembayaran',
            $pembayaran->tagihan->pelanggan->no_hp,
            $pesan,
            $berhasil,
            $this->lastResponse
        );

        return $berhasil;
    }
}
