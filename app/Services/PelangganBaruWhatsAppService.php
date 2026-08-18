<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Setting;
use Carbon\Carbon;

class PelangganBaruWhatsAppService
{
    public function __construct(
        private WhatsAppService $whatsapp,
    ) {
    }

    public function message(Pelanggan $pelanggan): string
    {
        $pelanggan->loadMissing(['paket', 'router']);

        $formatTanggal = static function ($tanggal): string {
            if (empty($tanggal)) {
                return '-';
            }

            try {
                return Carbon::parse($tanggal)->format('d-m-Y');
            } catch (\Throwable) {
                return (string) $tanggal;
            }
        };

        $data = [
            'nama' => $pelanggan->nama,
            'kode_pelanggan' => $pelanggan->kode_pelanggan,
            'paket' => $pelanggan->paket?->nama_paket ?? '-',
            'username_pppoe' => $pelanggan->username_pppoe ?? '-',
            'router' => $pelanggan->router?->nama_router ?? ($pelanggan->router?->nama ?? '-'),
            'ip_address' => $pelanggan->ip_address ?: '-',
            'tanggal_pasang' => $formatTanggal($pelanggan->tanggal_pasang),
            'tanggal_aktif' => $formatTanggal($pelanggan->tanggal_aktif),
            'status' => $pelanggan->status,
            'isp' => config('app.name'),
        ];

        $template = Setting::value(
            'whatsapp.template_pelanggan_baru',
            "Halo Bapak/Ibu, {nama},\n\n" .
            "Data layanan internet Anda telah berhasil dibuat.\n\n" .
            "━━━━━━━━━━━━━━━━━━\n" .
            "👤 DATA PELANGGAN\n" .
            "━━━━━━━━━━━━━━━━━━\n" .
            "🔖 Kode Pelanggan : {kode_pelanggan}\n" .
            "📡 Paket Internet : {paket}\n" .
            "👤 Username PPPoE : {username_pppoe}\n" .
            "📡 Router : {router}\n" .
            "🌐 IP Address : {ip_address}\n" .
            "📅 Tanggal Pasang : {tanggal_pasang}\n" .
            "📅 Tanggal Aktif : {tanggal_aktif}\n" .
            "✅ Status : {status}\n" .
            "━━━━━━━━━━━━━━━━━━\n\n" .
            "Terima kasih.\n{isp}"
        );

        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', (string) $value, $template);
        }

        return $template;
    }

    public function send(Pelanggan $pelanggan): bool
    {
        $pelanggan->loadMissing(['paket', 'router']);
        $pesan = $this->message($pelanggan);
        $nomor = $pelanggan->no_hp;
        $berhasil = $this->whatsapp->kirim($nomor, $pesan);

        $this->whatsapp->simpanLog(
            $pelanggan,
            null,
            'pelanggan_baru',
            $nomor,
            $pesan,
            $berhasil,
            $this->whatsapp->lastResponse()
        );

        return $berhasil;
    }
}
