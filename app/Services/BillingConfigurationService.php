<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Tagihan;
use Carbon\Carbon;

class BillingConfigurationService
{
    /**
     * Sinkronkan tagihan yang masih memiliki sisa dengan konfigurasi billing terbaru.
     */
    public function sync(): void
    {
        $dueDays = max(0, Setting::dueDays());
        $finePerDay = max(0, Setting::finePerDay());
        $today = Carbon::today();

        Tagihan::query()
            ->whereIn('status', [
                Tagihan::STATUS_BELUM_BAYAR,
                Tagihan::STATUS_SEBAGIAN,
                Tagihan::STATUS_JATUH_TEMPO,
            ])
            ->get()
            ->each(function (Tagihan $tagihan) use ($dueDays, $finePerDay, $today) {
                $jatuhTempo = Carbon::parse($tagihan->tanggal_tagihan)->addDays($dueDays);

                $hariTerlambat = $today->greaterThan($jatuhTempo)
                    ? $jatuhTempo->diffInDays($today)
                    : 0;

                $denda = $hariTerlambat * $finePerDay;
                $total = (float) $tagihan->nominal + $denda;
                $dibayar = (float) $tagihan->getTotalDibayar();
                $sisa = max(0, $total - $dibayar);

                if ($sisa <= 0.01) {
                    $status = Tagihan::STATUS_LUNAS;
                } elseif ($dibayar > 0) {
                    // Pembayaran sebagian tetap berstatus Sebagian walaupun lewat jatuh tempo.
                    $status = Tagihan::STATUS_SEBAGIAN;
                } elseif ($today->greaterThan($jatuhTempo)) {
                    $status = Tagihan::STATUS_JATUH_TEMPO;
                } else {
                    $status = Tagihan::STATUS_BELUM_BAYAR;
                }

                $tagihan->update([
                    'tanggal_jatuh_tempo' => $jatuhTempo,
                    'denda' => $denda,
                    'total' => $total,
                    'dibayar' => $dibayar,
                    'sisa' => $sisa,
                    'status' => $status,
                ]);
            });
    }
}
