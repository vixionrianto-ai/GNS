<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $templates = [
            'whatsapp.template_reminder_first' => [
                "Halo {nama},\n\nTagihan internet Anda telah melewati jatuh tempo.\n\nInvoice : {invoice}\nTotal : {total}\n\nTerima kasih.\n{isp}",
                "Halo {nama},\n\nTagihan internet Anda telah melewati jatuh tempo.\n\nInvoice : {invoice}\nTotal : {total_sisa}\n\nTerima kasih.\n{isp}",
            ],
            'whatsapp.template_reminder_second' => [
                "Halo {nama},\n\nSampai hari ini pembayaran belum kami terima.\n\nInvoice : {invoice}\nTotal : {total}\n\nMohon segera melakukan pembayaran.\n\n{isp}",
                "Halo {nama},\n\nSampai hari ini pembayaran belum kami terima.\n\nInvoice : {invoice}\nTotal : {total_sisa}\n\nMohon segera melakukan pembayaran.\n\n{isp}",
            ],
        ];

        $newTemplate = "Halo Bapak/Ibu, {nama},\n\n" .
            "Berikut rincian tagihan internet yang masih harus dibayar:\n\n" .
            "━━━━━━━━━━━━━━━━━━\n📄 RINCIAN TAGIHAN\n━━━━━━━━━━━━━━━━━━\n\n" .
            "{rincian_tagihan}\n\n━━━━━━━━━━━━━━━━━━\n💰 TOTAL HARUS DIBAYAR\n" .
            "{total_harus_dibayar}\n━━━━━━━━━━━━━━━━━━\n\n" .
            "Mohon melakukan pembayaran untuk melunasi seluruh tagihan.\n\n" .
            "Terima kasih.\n{isp}";

        $current = DB::table('settings')
            ->whereIn('key', array_keys($templates))
            ->get()
            ->keyBy('key');

        foreach ($templates as $key => $legacyValues) {
            $setting = $current->get($key);

            // Only replace the application's original templates. Custom templates
            // already edited by the operator are left untouched.
            if ($setting && in_array((string) $setting->value, $legacyValues, true)) {
                DB::table('settings')
                    ->where('id', $setting->id)
                    ->update(['value' => $newTemplate]);
            }
        }
    }

    public function down(): void
    {
        // Intentionally do not overwrite operator-customized templates on rollback.
    }
};
