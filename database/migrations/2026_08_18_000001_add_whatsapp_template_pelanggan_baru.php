<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        Setting::updateOrCreate(
            ['key' => 'whatsapp.template_pelanggan_baru'],
            [
                'group' => 'whatsapp',
                'value' => "Halo Bapak/Ibu, {nama},\n\n" .
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
                    "Terima kasih.\n{isp}",
                'description' => 'Template Pelanggan Baru',
                'type' => 'textarea',
                'is_active' => true,
            ]
        );
    }

    public function down(): void
    {
        Setting::where('key', 'whatsapp.template_pelanggan_baru')->delete();
    }
};
