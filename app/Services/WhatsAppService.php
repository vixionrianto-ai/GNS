<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\WhatsAppLog;
use App\Services\WhatsApp\WhatsAppProvider;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected WhatsAppProvider $provider;
    protected ?string $lastResponse = null;

    public function __construct(WhatsAppProvider $provider)
    {
        $this->provider = $provider;
    }

    public function formatNomor(?string $nomor): ?string
    {
        if (empty($nomor)) {
            return null;
        }

        $nomor = preg_replace('/[^0-9]/', '', $nomor);

        if ($nomor === '') {
            return null;
        }

        if (str_starts_with($nomor, '0')) {
            $nomor = '62' . substr($nomor, 1);
        }

        return $nomor;
    }

    public function url(?string $nomor, string $pesan): ?string
    {
        $nomor = $this->formatNomor($nomor);

        if (!$nomor) {
            return null;
        }

        return "https://wa.me/{$nomor}?text=" . urlencode($pesan);
    }

    public function appUrl(?string $nomor, string $pesan = ''): ?string
    {
        $nomor = $this->formatNomor($nomor);

        if (!$nomor) {
            return null;
        }

        return "whatsapp://send?phone={$nomor}&text=" . urlencode($pesan);
    }

    public function tagihan(Tagihan $tagihan): ?string
    {
        $tagihan->loadMissing('pelanggan');
        return $this->url($tagihan->pelanggan?->no_hp, $this->pesanTagihanBaru($tagihan));
    }

    public function pembayaran(Pembayaran $pembayaran): ?string
    {
        $pembayaran->loadMissing('tagihan.pelanggan');
        return $this->url($pembayaran->tagihan?->pelanggan?->no_hp, $this->pesanPembayaran($pembayaran));
    }

    protected function rupiah($nilai): string
    {
        return number_format((float) $nilai, 0, ',', '.');
    }

    protected function namaBulanIndonesia($bulan): string
    {
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $namaBulan[(int) $bulan] ?? (string) $bulan;
    }

    protected function periodeIndonesia(Tagihan $tagihan): string
    {
        return $this->namaBulanIndonesia($tagihan->bulan) . ' ' . $tagihan->tahun;
    }

    protected function template(string $settingKey, array $replace): string
    {
        $template = match ($settingKey) {
            'whatsapp.template_isolir' => (string) config('whatsapp.template_isolir', ''),
            default => '',
        };

        if ($template === '') {
            return '';
        }

        foreach ($replace as $key => $value) {
            $template = str_replace('{' . $key . '}', (string) $value, $template);
        }

        return $template;
    }

    protected function tagihanPlaceholder(Tagihan $tagihan): array
    {
        $tagihan->loadMissing('pelanggan');
        $pelanggan = $tagihan->pelanggan;

        if (!$pelanggan) {
            return [];
        }

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
            $tagihanTotal = $item->getTotalTagihan();
            $dibayar = $item->getTotalDibayar();
            $sisa = $item->getSisaTagihan();

            $totalTagihan += $tagihanTotal;
            $totalDibayar += $dibayar;
            $totalSisa += $sisa;

            if ($sisa <= 0) {
                continue;
            }

            $nomorRincian++;
            $statusIcon = match ($item->status) {
                Tagihan::STATUS_SEBAGIAN => '🟡',
                Tagihan::STATUS_JATUH_TEMPO => '⏰',
                Tagihan::STATUS_BELUM_BAYAR => '🔴',
                default => '⚪',
            };

            $rincian[] = $nomorRincian . ".\n" .
                "📅 Periode : " . $this->periodeIndonesia($item) . "\n" .
                "📄 Invoice : {$item->invoice_no}\n" .
                "{$statusIcon} Status : {$item->status}\n" .
                "❗ Sisa yang harus dibayar : Rp " . $this->rupiah($sisa);
        }

        return [
            'nama' => $pelanggan->nama,
            'invoice' => $tagihan->invoice_no,
            'periode' => $this->periodeIndonesia($tagihan),
            'bulan' => $tagihan->bulan,
            'tahun' => $tagihan->tahun,
            'nominal' => 'Rp ' . $this->rupiah($tagihan->nominal),
            'denda' => 'Rp ' . $this->rupiah($tagihan->denda),
            'total' => 'Rp ' . $this->rupiah($tagihan->total),
            'jatuh_tempo' => optional($tagihan->tanggal_jatuh_tempo)->format('d-m-Y'),
            'isp' => config('app.name'),
            'rincian_tagihan' => $rincian
                ? implode("\n────────────────────\n", $rincian)
                : 'Tidak ada tagihan yang masih harus dibayar.',
            'jumlah_tagihan' => $nomorRincian,
            'total_tagihan' => 'Rp ' . $this->rupiah($totalTagihan),
            'total_dibayar' => 'Rp ' . $this->rupiah($totalDibayar),
            'total_sisa' => 'Rp ' . $this->rupiah($totalSisa),
            'total_harus_dibayar' => 'Rp ' . $this->rupiah($totalSisa),
        ];
    }

    protected function pembayaranPlaceholder(Pembayaran $pembayaran): array
    {
        $pembayaran->loadMissing('tagihan.pelanggan');
        $tagihan = $pembayaran->tagihan;
        $pelanggan = $tagihan?->pelanggan;

        if (!$tagihan || !$pelanggan) {
            return [];
        }

        $tagihanData = $this->tagihanPlaceholder($tagihan);
        $pdfUrl = $pembayaran->public_token
            ? url('/public-invoice/' . $pembayaran->public_token . '/pdf')
            : null;

        return array_merge([
            'nama' => $pelanggan->nama,
            'invoice' => $pembayaran->invoice_no,
            'invoice_tagihan' => $tagihan->invoice_no,
            'public_token' => $pembayaran->public_token,
            'pdf_url' => $pdfUrl,
            'periode' => $this->periodeIndonesia($tagihan),
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
        $data = $this->tagihanPlaceholder($tagihan);

        return "Halo Bapak/Ibu, {$data['nama']},\n\n" .
            "Berikut rincian tagihan internet yang masih harus dibayar:\n\n" .
            "━━━━━━━━━━━━━━━━━━\n📄 RINCIAN TAGIHAN\n━━━━━━━━━━━━━━━━━━\n\n" .
            $data['rincian_tagihan'] . "\n\n━━━━━━━━━━━━━━━━━━\n💰 TOTAL HARUS DIBAYAR\n" .
            "{$data['total_harus_dibayar']}\n━━━━━━━━━━━━━━━━━━\n\n" .
            "Mohon melakukan pembayaran untuk melunasi seluruh tagihan.\n\nTerima kasih.\n" .
            config('app.name');
    }

    public function pesanPembayaran(Pembayaran $pembayaran): string
    {
        $data = $this->pembayaranPlaceholder($pembayaran);
        $sisaText = $data['total_sisa'] === 'Rp 0'
            ? 'Semua tagihan telah lunas.'
            : $data['total_harus_dibayar'];

        $pdfText = $data['pdf_url']
            ? "🧾 Invoice pembayaran dapat diunduh melalui:\n{$data['pdf_url']}\n\n"
            : '';

        return "Halo Bapak/Ibu, {$data['nama']},\n\n" .
            "Terima kasih, pembayaran Anda telah kami terima.\n\n" .
            "━━━━━━━━━━━━━━━━━━\n" .
            "💳 PEMBAYARAN DITERIMA\n" .
            "━━━━━━━━━━━━━━━━━━\n" .
            "📄 Invoice Pembayaran : {$data['invoice']}\n" .
            "💰 Jumlah Dibayar : {$data['total']}\n" .
            "📆 Tanggal Bayar : {$data['tanggal_bayar']}\n\n" .
            "━━━━━━━━━━━━━━━━━━\n" .
            "📋 SISA TAGIHAN\n" .
            "━━━━━━━━━━━━━━━━━━\n\n" .
            $data['rincian_tagihan'] . "\n\n" .
            "━━━━━━━━━━━━━━━━━━\n" .
            "💰 TOTAL SISA\n" .
            "{$sisaText}\n" .
            "━━━━━━━━━━━━━━━━━━\n\n" .
            $pdfText .
            "Terima kasih.\n" .
            config('app.name');
    }

    public function pesanIsolir(Pelanggan $pelanggan): string
    {
        return $this->template('whatsapp.template_isolir', $this->pelangganPlaceholder($pelanggan));
    }

    public function sendTagihan(Tagihan $tagihan): bool
    {
        $tagihan->loadMissing('pelanggan');
        $pesan = $this->pesanTagihanBaru($tagihan);
        $nomor = $tagihan->pelanggan?->no_hp;
        $berhasil = $nomor ? $this->kirim($nomor, $pesan) : false;

        if ($tagihan->pelanggan) {
            $this->simpanLog($tagihan->pelanggan, $tagihan, 'tagihan', $nomor ?? '', $pesan, $berhasil, $this->lastResponse);
        }

        return $berhasil;
    }

    public function sendPembayaran(Pembayaran $pembayaran): bool
    {
        $pembayaran->loadMissing('tagihan.pelanggan');
        $pesan = $this->pesanPembayaran($pembayaran);
        $pelanggan = $pembayaran->tagihan?->pelanggan;
        $nomor = $pelanggan?->no_hp;
        $berhasil = $nomor ? $this->kirim($nomor, $pesan) : false;

        if ($pelanggan && $pembayaran->tagihan) {
            $this->simpanLog($pelanggan, $pembayaran->tagihan, 'pembayaran', $nomor ?? '', $pesan, $berhasil, $this->lastResponse);
        }

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
        $this->lastResponse = null;

        if (!config('whatsapp.enabled', true)) {
            return false;
        }

        $nomor = $this->formatNomor($nomor);

        if (!$nomor) {
            return false;
        }

        try {
            $start = microtime(true);
            $hasil = $this->provider->send($nomor, $pesan);
            $last = $this->provider->lastResponse();
            $this->lastResponse = $last
                ? json_encode($last, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                : null;

            Log::info('WhatsApp Response Time', [
                'provider' => get_class($this->provider),
                'seconds' => round(microtime(true) - $start, 3),
            ]);

            return $hasil;
        } catch (\Throwable $e) {
            Log::error('WhatsApp Exception', [
                'nomor' => $nomor,
                'provider' => get_class($this->provider),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function lastResponse(): ?string
    {
        return $this->lastResponse;
    }

    public function simpanLog(
        Pelanggan $pelanggan,
        ?Tagihan $tagihan,
        string $jenis,
        string $nomor,
        string $pesan,
        bool $berhasil,
        ?string $response = null
    ): void {
        WhatsAppLog::create([
            'pelanggan_id' => $pelanggan->id,
            'tagihan_id' => $tagihan?->id,
            'jenis' => $jenis,
            'provider' => config('whatsapp.provider', 'fonnte'),
            'nomor' => $nomor,
            'pesan' => $pesan,
            'status' => $berhasil ? 'success' : 'failed',
            'response' => $response,
            'sent_at' => now(),
        ]);
    }

    public function sendInvoicePdf(Pembayaran $pembayaran, string $documentUrl): bool
    {
        if (!config('whatsapp.enabled', true)) {
            return false;
        }

        $pembayaran->loadMissing('tagihan.pelanggan');
        $nomor = $this->formatNomor($pembayaran->tagihan?->pelanggan?->no_hp);

        if (!$nomor || !$pembayaran->tagihan?->pelanggan) {
            return false;
        }

        $pesan = $this->pesanPembayaran($pembayaran);

        try {
            $berhasil = $this->provider->sendDocument($nomor, $pesan, $documentUrl);
            $last = $this->provider->lastResponse();
            $this->lastResponse = $last
                ? json_encode($last, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                : null;
        } catch (\Throwable $e) {
            $berhasil = false;
            $this->lastResponse = $e->getMessage();
            Log::error('WhatsApp Document Exception', [
                'nomor' => $nomor,
                'provider' => get_class($this->provider),
                'error' => $e->getMessage(),
            ]);
        }

        $this->simpanLog(
            $pembayaran->tagihan->pelanggan,
            $pembayaran->tagihan,
            'pembayaran',
            $nomor,
            $pesan,
            $berhasil,
            $this->lastResponse
        );

        return $berhasil;
    }
}