<?php

namespace App\Services;

use App\Models\Tagihan;
use Carbon\Carbon;

class ReminderService
{
    public function __construct(
        protected WhatsAppService $whatsAppService
    ) {
    }

    /**
     * Reminder H+3
     */
    public function reminderH3(): int
    {
        $jumlah = 0;

        $tagihans = Tagihan::with('pelanggan')
            ->whereIn('status', [
                    Tagihan::STATUS_BELUM_BAYAR,
                    Tagihan::STATUS_JATUH_TEMPO,
                ])
            ->whereDate(
                'tanggal_jatuh_tempo',
                '<=',
                Carbon::today()->subDays(
                    (int) setting('whatsapp.reminder_h3', 3)
                )
            )
            ->get();

            foreach ($tagihans as $tagihan) {

                try {

                    if (
                        $this->whatsAppService
                            ->sendReminder($tagihan, 'h3')
                    ) {
                        $jumlah++;
                    }

                } catch (\Throwable $e) {

                    report($e);

                }

            }

        return $jumlah;
    }

    /**
     * Reminder H+7
     */
    public function reminderH7(): int
    {
        $jumlah = 0;

        $tagihans = Tagihan::with('pelanggan')
            ->whereIn('status', [
                Tagihan::STATUS_BELUM_BAYAR,
                Tagihan::STATUS_JATUH_TEMPO,
            ])
            ->whereDate(
                'tanggal_jatuh_tempo',
                '<=',
                Carbon::today()->subDays(
                    (int) setting('whatsapp.reminder_h7', 7)
                )
            )
            ->get();

        foreach ($tagihans as $tagihan) {

        try {

            if (
                $this->whatsAppService
                    ->sendReminder($tagihan, 'h7')
            ) {
                $jumlah++;
            }

        } catch (\Throwable $e) {

            report($e);

        }

    }

        return $jumlah;
    }
}