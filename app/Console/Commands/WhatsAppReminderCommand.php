<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Attributes\Description;

#[Signature('wa:reminder')]
#[Description('Mengirim reminder WhatsApp otomatis')]
class WhatsAppReminderCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ReminderService $reminderService): int
    {
        $this->info('========================================');
        $this->info(' GNS WhatsApp Reminder Engine');
        $this->info('========================================');
        $this->newLine();

        $hasil3 = $reminderService->reminderH3();
        $hasil7 = $reminderService->reminderH7();

        $this->table(
            ['Reminder', 'Jumlah'],
            [
                ['H+3', $hasil3],
                ['H+7', $hasil7],
            ]
        );

        $this->newLine();
        $this->info('Reminder selesai.');

        return self::SUCCESS;
    }
}