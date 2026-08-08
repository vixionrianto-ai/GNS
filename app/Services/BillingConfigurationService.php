<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Tagihan;
use Carbon\Carbon;

class BillingConfigurationService
{
    /**
     * Sinkronkan seluruh tagihan yang belum lunas
     * dengan konfigurasi billing terbaru.
     */
    public function sync(): void
    {
        $dueDays = Setting::dueDays();
        $finePerDay = Setting::finePerDay();

        Tagihan::query()
            ->whereIn('status', [
                Tagihan::STATUS_BELUM_BAYAR,
                Tagihan::STATUS_JATUH_TEMPO,
            ])
            ->get()
            ->each(function (Tagihan $tagihan) use (
                $dueDays,
                $finePerDay
            ) {

                $jatuhTempo = Carbon::parse(
                    $tagihan->tanggal_tagihan
                )->addDays($dueDays);

                $hariTerlambat = max(
                    0,
                    Carbon::today()->diffInDays(
                        $jatuhTempo,
                        false
                    ) * -1
                );

                $denda = $hariTerlambat * $finePerDay;

                $tagihan->update([
                    'tanggal_jatuh_tempo' => $jatuhTempo,
                    'denda'               => $denda,
                    'total'               => $tagihan->nominal + $denda,
                ]);
            });
    }
}