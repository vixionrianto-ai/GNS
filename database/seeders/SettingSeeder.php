<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
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
                'value' => '5',
                'description' => 'Reminder pertama (hari setelah jatuh tempo)',
                'type' => 'integer',
            ],
            [
                'group' => 'whatsapp',
                'key' => 'whatsapp.reminder_h7',
                'value' => '14',
                'description' => 'Reminder kedua (hari setelah jatuh tempo)',
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
                'value' => 'Halo {nama},\n\nTagihan internet Anda untuk periode {periode} telah tersedia.\n\n━━━━━━━━━━━━━━━━━━\n📄 Invoice : {invoice}\n💰 Total : {total}\n📆 Jatuh Tempo : {jatuh_tempo}\n━━━━━━━━━━━━━━━━━━\n\nTerima kasih.\n{isp}',
                'description' => 'Template Tagihan Baru',
                'type' => 'textarea',
            ],
            [
                'group' => 'whatsapp',
                'key' => 'whatsapp.template_h3',
                'value' => 'Halo {nama},\n\nTagihan internet Anda telah melewati jatuh tempo.\n\nInvoice : {invoice}\nTotal : {total}\n\nTerima kasih.\n{isp}',
                'description' => 'Template Reminder Pertama',
                'type' => 'textarea',
            ],
            [
                'group' => 'whatsapp',
                'key' => 'whatsapp.template_h7',
                'value' => 'Halo {nama},\n\nSampai hari ini pembayaran belum kami terima.\n\nInvoice : {invoice}\nTotal : {total}\n\nMohon segera melakukan pembayaran.\n\n{isp}',
                'description' => 'Template Reminder Kedua',
                'type' => 'textarea',
            ],
            [
                'group' => 'whatsapp',
                'key' => 'whatsapp.template_paid',
                'value' => 'Terima kasih {nama},\n\nPembayaran sebesar {total} telah kami terima.\n\nInvoice : {invoice}\n\n{isp}',
                'description' => 'Template Pembayaran',
                'type' => 'textarea',
            ],
            [
                'group' => 'whatsapp',
                'key' => 'whatsapp.template_isolir',
                'value' => 'Halo {nama},\n\nLayanan internet Anda untuk sementara dinonaktifkan karena tagihan belum diselesaikan.\n\nMohon segera melakukan pembayaran agar layanan dapat diaktifkan kembali.\n\nTerima kasih.\n{isp}',
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
