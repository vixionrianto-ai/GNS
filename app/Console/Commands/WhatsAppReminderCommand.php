<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\Tagihan;
use App\Services\ReminderService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Attributes\Description;

#[Signature('wa:reminder {--dry-run : Cek kandidat reminder tanpa mengirim WhatsApp}')]
#[Description('Mengirim reminder WhatsApp otomatis berdasarkan pengaturan')]
class WhatsAppReminderCommand extends Command
{
    public function handle(ReminderService $reminderService): int
    {
        $this->info('========================================');
        $this->info(' GNS WhatsApp Reminder Engine');
        $this->info('========================================');
        $this->newLine();

        $firstDays = max(0, (int) Setting::value('whatsapp.reminder_h3', 3));
        $secondDays = max(0, (int) Setting::value('whatsapp.reminder_h7', 7));

        if ($this->option('dry-run')) {
            $firstCount = $this->countCandidates($firstDays);
            $secondCount = $this->countCandidates($secondDays);

            $this->table(
                ['Reminder', 'Hari Setelah Jatuh Tempo', 'Kandidat'],
                [
                    ['Pertama', 'H+' . $firstDays, $firstCount],
                    ['Kedua', 'H+' . $secondDays, $secondCount],
                ]
            );

            $this->newLine();
            $this->info('DRY-RUN selesai. Tidak ada WhatsApp yang dikirim.');

            return self::SUCCESS;
        }

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

    protected function countCandidates(int $days): int
    {
        $tanggalBatas = Carbon::today()->subDays($days);

        return Tagihan::query()
            ->whereIn('status', [
                Tagihan::STATUS_BELUM_BAYAR,
                Tagihan::STATUS_JATUH_TEMPO,
                Tagihan::STATUS_SEBAGIAN,
            ])
            ->whereDate('tanggal_jatuh_tempo', '<=', $tanggalBatas)
            ->whereHas('pelanggan', function ($query) {
                $query->whereNotNull('no_hp')
                    ->where('no_hp', '!=', '');
            })
            ->count();
    }
}
