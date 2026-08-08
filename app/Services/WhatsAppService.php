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
    /**
     * Response terakhir dari provider.
     */
    protected ?string $lastResponse = null;
    public function __construct(
        WhatsAppProvider $provider
    ){
        $this->provider = $provider;
    }

    /**
     * Format nomor HP menjadi format WhatsApp.
     */
    public function formatNomor(string|null $nomor): ?string
    {
        if (empty($nomor)) {
            return null;
        }

        $nomor = preg_replace('/[^0-9]/', '', $nomor);

        if (str_starts_with($nomor, '0')) {
            $nomor = '62' . substr($nomor, 1);
        }

        return $nomor;
    }

    /**
     * Membuat URL WhatsApp.
     */
    public function url(?string $nomor, string $pesan): ?string
    {
        $nomor = $this->formatNomor($nomor);

        if (!$nomor) {
            return null;
        }

        return "https://web.whatsapp.com/send?phone={$nomor}&text=" . urlencode($pesan);
    }

    /**
     * URL WhatsApp Tagihan Baru.
     */
    public function tagihan(Tagihan $tagihan): ?string
    {
        return $this->url(
            $tagihan->pelanggan->no_hp,
            $this->pesanTagihanBaru($tagihan)
        );
    }

    /**
     * URL WhatsApp Reminder H+3.
     */
    public function reminder3(Tagihan $tagihan): ?string
    {
        return $this->url(
            $tagihan->pelanggan->no_hp,
            $this->pesanReminder3Hari($tagihan)
        );
    }

    /**
     * URL WhatsApp Reminder H+7.
     */
    public function reminder7(Tagihan $tagihan): ?string
    {
        return $this->url(
            $tagihan->pelanggan->no_hp,
            $this->pesanReminder7Hari($tagihan)
        );
    }

    /**
     * URL WhatsApp Bukti Pembayaran.
     */
    public function pembayaran(Pembayaran $pembayaran): ?string
    {
        return $this->url(
            $pembayaran->tagihan->pelanggan->no_hp,
            $this->pesanPembayaran($pembayaran)
        );
    }
    
    /**
     * Format Rupiah.
     */
    protected function rupiah($nilai): string
    {
        return number_format($nilai, 0, ',', '.');
    }

    /**
     * Mengambil template dari tabel settings
     * lalu mengganti placeholder.
     */
    protected function template(
        string $settingKey,
        array $replace
    ): string {

        $template = Setting::value($settingKey);

        if (!$template) {
            return '';
        }

        foreach ($replace as $key => $value) {

            $template = str_replace(
                '{' . $key . '}',
                (string) $value,
                $template
            );

        }

        return $template;
    }

    /**
     * Placeholder untuk Tagihan.
     */
    protected function tagihanPlaceholder(
        Tagihan $tagihan
    ): array {

        $pelanggan = $tagihan->pelanggan;

        // Ambil seluruh tagihan pelanggan
        $tagihans = Tagihan::where('pelanggan_id', $pelanggan->id)
            ->where('status', '!=', Tagihan::STATUS_DIBATALKAN)
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get();

        $rincian = [];

        $totalTagihan = 0;
        $totalDibayar = 0;
        $totalSisa = 0;

        foreach ($tagihans as $index => $item) {

            $tagihanTotal = $item->getTotalTagihan();
            $dibayar = $item->getTotalDibayar();
            $sisa = $item->getSisaTagihan();

            $statusIcon = match ($item->status) {
                Tagihan::STATUS_LUNAS => '🟢',
                Tagihan::STATUS_SEBAGIAN => '🟡',
                Tagihan::STATUS_BELUM_BAYAR => '🔴',
                Tagihan::STATUS_JATUH_TEMPO => '⏰',
                default => '⚪',
            };

            $totalTagihan += $tagihanTotal;
            $totalDibayar += $dibayar;
            $totalSisa += $sisa;

            $rincian[] =
                ($index + 1).".\n" .
                "📅 Periode : " .
                optional($item->tanggal_tagihan)->translatedFormat('F Y') . "\n" .
                "📄 Invoice : {$item->invoice_no}\n" .
                "{$statusIcon} Status : {$item->status}\n" .
                "💰 Tagihan : Rp ".$this->rupiah($tagihanTotal)."\n" .
                "✅ Dibayar : Rp ".$this->rupiah($dibayar)."\n" .
                "❗ Sisa    : Rp ".$this->rupiah($sisa)."\n";
        }

        return [

            // Placeholder lama (tetap dipertahankan)
            'nama' => $pelanggan->nama,

            'invoice' => $tagihan->invoice_no,

            'periode' => $tagihan->periode,

            'bulan' => $tagihan->bulan,

            'tahun' => $tagihan->tahun,

            'nominal' => 'Rp '.$this->rupiah($tagihan->nominal),

            'denda' => 'Rp '.$this->rupiah($tagihan->denda),

            'total' => 'Rp '.$this->rupiah($tagihan->total),

            'jatuh_tempo' => optional(
                $tagihan->tanggal_jatuh_tempo
            )->format('d-m-Y'),

            'isp' => config('app.name'),

            // Placeholder baru
            'rincian_tagihan' => implode(
                "\n────────────────────\n",
                $rincian
            ),

            'jumlah_tagihan' => $tagihans->count(),

            'total_tagihan' => 'Rp '.number_format(
                $totalTagihan,
                0,
                ',',
                '.'
            ),

            'total_dibayar' => 'Rp '.number_format(
                $totalDibayar,
                0,
                ',',
                '.'
            ),

            'total_sisa' => 'Rp '.number_format(
                $totalSisa,
                0,
                ',',
                '.'
            ),

        ];

    }

    /**
     * Placeholder untuk Pembayaran.
     */
    protected function pembayaranPlaceholder(
        Pembayaran $pembayaran
    ): array {

        return [

            'nama' => $pembayaran->tagihan->pelanggan->nama,

            'invoice' => $pembayaran->tagihan->invoice_no,

            'periode' => $pembayaran->tagihan->periode,

            'bulan' => $pembayaran->tagihan->bulan,

            'tahun' => $pembayaran->tagihan->tahun,

            'nominal' => 'Rp '.$this->rupiah($pembayaran->tagihan->nominal),

            'denda' => 'Rp '.$this->rupiah($pembayaran->tagihan->denda),

            'total' => 'Rp '.$this->rupiah($pembayaran->total_bayar),

            'jatuh_tempo' => optional(
                $pembayaran->tagihan->tanggal_jatuh_tempo
            )->format('d-m-Y'),

            'tanggal_bayar' => optional(
                $pembayaran->tanggal_bayar
            )->format('d-m-Y H:i'),

            'isp' => config('app.name'),

        ];

    }

    /**
     * Placeholder untuk Pelanggan.
     */
    protected function pelangganPlaceholder(
        Pelanggan $pelanggan
    ): array {

        return [

            'nama' => $pelanggan->nama,

            'isp' => config('app.name'),

        ];

    }

    public function pesanTagihanBaru(
        Tagihan $tagihan
    ): string {

        return $this->template(
            'whatsapp.template_invoice',
            $this->tagihanPlaceholder($tagihan)
        );

    }

    public function pesanReminder3Hari(
        Tagihan $tagihan
    ): string {

        return $this->template(
            'whatsapp.template_h3',
            $this->tagihanPlaceholder($tagihan)
        );

    }    

    public function pesanReminder7Hari(
        Tagihan $tagihan
    ): string {

        return $this->template(
            'whatsapp.template_h7',
            $this->tagihanPlaceholder($tagihan)
        );

    }
    
    public function pesanPembayaran(
        Pembayaran $pembayaran
    ): string {

        return $this->template(
            'whatsapp.template_paid',
            $this->pembayaranPlaceholder($pembayaran)
        );

    }
    

    /**
     * Isolir.
     */
    public function pesanIsolir(
        Pelanggan $pelanggan
    ): string {

        return $this->template(
            'whatsapp.template_isolir',
            $this->pelangganPlaceholder($pelanggan)
        );

    }
    
    public function sendReminder(
        Tagihan $tagihan,
        string $jenis
    ): bool
    {
        if ($this->sudahPernahKirim($tagihan, $jenis)) {
       
             Log::info('Reminder sudah pernah dikirim.', [
                'tagihan_id' => $tagihan->id,
                'jenis'      => $jenis,
            ]);
            return false;
        }

        switch ($jenis) {

            case 'h3':
                $pesan = $this->pesanReminder3Hari($tagihan);
                break;

            case 'h7':
                $pesan = $this->pesanReminder7Hari($tagihan);
                break;

            default:
                return false;
        }

        $berhasil = $this->kirim(
            $tagihan->pelanggan->no_hp,
            $pesan
        );

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

    /**
     * Kirim WhatsApp Tagihan Baru.
     */
    public function sendTagihan(
        Tagihan $tagihan
    ): bool
    {
        $pesan = $this->pesanTagihanBaru($tagihan);

        $berhasil = $this->kirim(
            $tagihan->pelanggan->no_hp,
            $pesan
        );

        $this->simpanLog(
            $tagihan->pelanggan,
            $tagihan,
            'tagihan',
            $tagihan->pelanggan->no_hp,
            $pesan,
            $berhasil,
            $this->lastResponse
        );

        return $berhasil;
    }

    /**
     * Kirim WhatsApp Pembayaran.
     */
    public function sendPembayaran(
        Pembayaran $pembayaran
    ): bool
    {
        $pesan = $this->pesanPembayaran($pembayaran);

        $berhasil = $this->kirim(
            $pembayaran->tagihan->pelanggan->no_hp,
            $pesan
        );

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

    /**
     * Cek apakah reminder sudah pernah dikirim.
     */
    public function sudahPernahKirim(
        Tagihan $tagihan,
        string $jenis
    ): bool
    {
        return WhatsAppLog::where('tagihan_id', $tagihan->id)
            ->where('jenis', $jenis)
            ->where('status', 'success')
            ->exists();
    }

    public function kirim(
        string $nomor,
        string $pesan
    ): bool
    {
        // Pengecekan agar pengiriman otomatis mati jika WHATSAPP_ENABLED bernilai false di .env
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

                    $response = json_encode(
                        $last,
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                    );

                }

            }

            $this->lastResponse = $response;
            $duration = round(
                microtime(true) - $start,
                3
            );

            Log::info('WhatsApp Response Time', [

                'provider' => get_class($this->provider),

                'seconds' => $duration,

            ]);

            if ($hasil) {
                Log::info('WhatsApp berhasil dikirim.', [
                    'nomor' => $nomor,
                ]);
            } else {
                Log::warning('WhatsApp gagal dikirim.', [
                    'nomor' => $nomor,
                ]);
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

    public function simpanLog(
        Pelanggan $pelanggan,
        ?Tagihan $tagihan,
        string $jenis,
        string $nomor,
        string $pesan,
        bool $berhasil,
        ?string $response = null
    ): void
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

    /**
     * Kirim invoice PDF melalui Fonnte.
     */
    public function sendInvoicePdf(
        Pembayaran $pembayaran,
        string $documentUrl
    ): bool
    {
        // Pengecekan keamanan jika WhatsApp dimatikan
        if (config('app.whatsapp_enabled', env('WHATSAPP_ENABLED', true)) === false) {
            Log::info('Pengiriman Invoice PDF WhatsApp dilewati karena WHATSAPP_ENABLED bernilai false.');
            return false;
        }

        $pesan = $this->pesanPembayaran($pembayaran);

        $berhasil = $this->provider->sendDocument(
        
            $this->formatNomor(
                $pembayaran->tagihan->pelanggan->no_hp
            ),
            $pesan,
            $documentUrl
        );
        if (method_exists($this->provider, 'lastResponse')) {

            $last = $this->provider->lastResponse();

            $this->lastResponse = $last
                ? json_encode(
                    $last,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                )
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