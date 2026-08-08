<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Services\MikroTikService;
use App\Models\Setting;

class IsolationService
{  
    protected MikroTikService $mikroTikService;

    public function __construct(
        MikroTikService $mikroTikService
    ) {
        $this->mikroTikService = $mikroTikService;
    }
    public function getCandidates()
    {
        return Pelanggan::where('status', Pelanggan::STATUS_AKTIF)
        ->with([
            'router',

            'tagihans' => function ($query) {
                $query->whereIn('status', [
                    Tagihan::STATUS_BELUM_BAYAR,
                    Tagihan::STATUS_SEBAGIAN,
                    Tagihan::STATUS_JATUH_TEMPO,
                ]);
            }
        ])
            ->get()
            ->filter(function ($pelanggan) {

                $jumlahPeriode = $pelanggan->tagihans
                    ->pluck('periode')
                    ->unique()
                    ->count();

                /*
                |--------------------------------------------------------------------------
                | Tentukan batas isolir
                |--------------------------------------------------------------------------
                */

                $batasIsolir = $pelanggan->isolation_use_default
                    ? Setting::defaultIsolationPeriod()
                    : $pelanggan->isolation_period_limit;

                
                /*
                |--------------------------------------------------------------------------
                | Jika batas khusus kosong, gunakan default
                |--------------------------------------------------------------------------
                */

                $batasIsolir = $batasIsolir ?: Setting::defaultIsolationPeriod();

                return $jumlahPeriode >= $batasIsolir;
                            })
            ->each(function ($pelanggan) {

                $pelanggan->jumlah_periode = $pelanggan->tagihans
                    ->pluck('periode')
                    ->unique()
                    ->count();

                $pelanggan->jumlah_tagihan = $pelanggan->tagihans->count();

                $pelanggan->total_tunggakan = $pelanggan->tagihans->sum('sisa');

            })
            ->values();
    }
    /**
 * Memproses pelanggan yang memenuhi syarat isolir.
 *
 * Saat ini hanya menandai database.
 * Belum menghubungi MikroTik.
 */
public function process(bool $execute = false)
{
    $processed = collect();

    $candidates = $this->getCandidates();

foreach ($candidates as $pelanggan) {

([
    'nama' => $pelanggan->nama,
    'is_isolated' => $pelanggan->is_isolated,
    'router' => $pelanggan->router?->id,
    'secret' => $pelanggan->mikrotik_secret_id,
]);
        if ($pelanggan->is_isolated) {
            continue;
        }

        if (!$pelanggan->router) {
            continue;
        }

        if (empty($pelanggan->mikrotik_secret_id)) {
            continue;
        }

        try {

            if ($execute) {
('Masuk execute: ' . $pelanggan->nama);

            // 1. Disable PPP Secret
            $this->mikroTikService->disableSecretById(
                $pelanggan->router,
                $pelanggan->mikrotik_secret_id
            );

            // 2. Disconnect PPP Active (jika gagal jangan batalkan isolasi)
            try {

                $this->mikroTikService->disconnectActiveSessionBySecretId(
                    $pelanggan->router,
                    $pelanggan->mikrotik_secret_id
                );

            } catch (\Throwable $e) {
                report($e);
            }

            // 3. Simpan status isolasi
            unset(
    $pelanggan->jumlah_periode,
    $pelanggan->jumlah_tagihan,
    $pelanggan->total_tunggakan
);
            $pelanggan->is_isolated = true;
            $pelanggan->isolated_at = now();
            $pelanggan->save();
('saved '.$pelanggan->nama);
        }

            $processed->push($pelanggan);

        } catch (\Throwable $e) {

    throw $e;

}

    }

    return $processed;
}
}