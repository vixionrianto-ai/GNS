<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Attributes\Description;
use App\Models\Setting;

#[Signature('wa:reminder')]
#[Description('Mengirim reminder WhatsApp otomatis berdasarkan pengaturan')]
class WhatsAppReminderCommand extends Command
{
    public function handle(ReminderService $reminderService): int
    {
        $this->info('========================================');
        $this->info(' GNS WhatsApp Reminder Engine');
        $this->info('========================================');
        $this->newLine();

        $firstDays = (int) Setting::value('whatsapp.reminder_h3', 3);
        $secondDays = (int) Setting::value('whatsapp.reminder_h7', 7);
        $hasil = $reminderService->reminderConfigured();

        $this->table(
            ['Reminder', 'Hari Setelah Jatuh Tempo', 'Jumlah'],
            [
                ['Pertama', 'H+' . $firstDays, $hasil['reminder_1']],
                ['Kedua', 'H+' . $secondDays, $hasil['reminder_2']],
            ]
        );

        $this->newLine();
        $this->info('Reminder selesai.');

        return self::SUCCESS;
    }
}
