<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [

            // Billing
            [
                'group' => 'billing',
                'key' => 'billing.due_days',
                'value' => '10',
                'description' => 'Jumlah hari jatuh tempo',
                'type' => 'integer',
            ],

            [
                'group' => 'billing',
                'key' => 'billing.fine_per_day',
                'value' => '1000',
                'description' => 'Denda per hari',
                'type' => 'integer',
            ],

            [
                'group' => 'billing',
                'key' => 'billing.isolate_after',
                'value' => '14',
                'description' => 'Hari isolir setelah jatuh tempo',
                'type' => 'integer',
            ],

            [
                'group' => 'billing',
                'key' => 'billing.auto_apply_saldo',
                'value' => '1',
                'description' => 'Gunakan saldo pelanggan otomatis saat generate tagihan',
                'type' => 'boolean',
            ],

            // WhatsApp
            [
                'group' => 'whatsapp',
                'key' => 'whatsapp.provider',
                'value' => 'fonnte',
                'description' => 'Provider WhatsApp',
                'type' => 'string',
            ],

            [
                'group' => 'whatsapp',
                'key' => 'whatsapp.reminder_h3',
                'value' => '3',
                'description' => 'Reminder pertama',
                'type' => 'integer',
            ],

            [
                'group' => 'whatsapp',
                'key' => 'whatsapp.reminder_h7',
                'value' => '7',
                'description' => 'Reminder kedua',
                'type' => 'integer',
            ],

            [
                'group' => 'whatsapp',
                'key' => 'whatsapp.send_time',
                'value' => '08:00',
                'description' => 'Jam kirim WhatsApp',
                'type' => 'time',
            ],
            [
                'group' => 'whatsapp',
                'key' => 'whatsapp.template_invoice',
                'value' => 
                   'Halo {nama},
                    Tagihan internet Anda untuk periode {periode} telah tersedia.

                    ━━━━━━━━━━━━━━━━━━
                    📄 Invoice : {invoice}
                    💰 Total : {total}
                    📆 Jatuh Tempo : {jatuh_tempo}
                    ━━━━━━━━━━━━━━━━━━

                    Terima kasih.
                    {isp}',
                'description' => 'Template Tagihan Baru',
                'type' => 'textarea',
            ],

            [
                'group' => 'whatsapp',
                'key' => 'whatsapp.template_h3',
                'value' => 'Halo {nama},

            Tagihan internet Anda telah melewati jatuh tempo.

            Invoice : {invoice}
            Total : {total}

            Terima kasih.

            {isp}',
                'description' => 'Template Reminder H+3',
                'type' => 'textarea',
            ],

            [
                'group' => 'whatsapp',
                'key' => 'whatsapp.template_h7',
                'value' => 'Halo {nama},

            Sampai hari ini pembayaran belum kami terima.

            Invoice : {invoice}
            Total : {total}

            Mohon segera melakukan pembayaran.

            {isp}',
                'description' => 'Template Reminder H+7',
                'type' => 'textarea',
            ],

            [
                'group' => 'whatsapp',
                'key' => 'whatsapp.template_paid',
                'value' => 'Terima kasih {nama},

            Pembayaran sebesar {total} telah kami terima.

            Invoice : {invoice}

            {isp}',
                'description' => 'Template Pembayaran',
                'type' => 'textarea',
            ],

            [
                'group' => 'whatsapp',
                'key' => 'whatsapp.template_isolir',
                'value' => 'Halo {nama},

            Layanan internet Anda untuk sementara dinonaktifkan karena tagihan belum diselesaikan.

            Mohon segera melakukan pembayaran agar layanan dapat diaktifkan kembali.

            Terima kasih.

            {isp}',
                'description' => 'Template Isolir',
                'type' => 'textarea',
            ],

            // Invoice
            [
                'group' => 'invoice',
                'key' => 'invoice.prefix',
                'value' => 'INV',
                'description' => 'Prefix Invoice',
                'type' => 'string',
            ],

            // MikroTik
            [
                'group' => 'mikrotik',
                'key' => 'mikrotik.sync',
                'value' => 'true',
                'description' => 'Sinkronisasi MikroTik',
                'type' => 'boolean',
            ],
        ];

        foreach ($settings as $setting) {

            \App\Models\Setting::updateOrCreate(

                ['key' => $setting['key']],

                $setting

            );
        }
    }
}
