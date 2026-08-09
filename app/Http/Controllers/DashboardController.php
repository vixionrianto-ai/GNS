<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Router;
use App\Models\Tagihan;
use App\Models\SaldoPelanggan;
use App\Services\MikroTikService;
use App\Services\DashboardService;
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
   public function index(
    MikroTikService $mikrotik,
    DashboardService $dashboardService
    )
    {
        $stats = $dashboardService->getStatistics();

        extract($stats);
        

        /*
        |--------------------------------------------------------------------------
        | Dashboard Analytics
        |--------------------------------------------------------------------------
        */

        // Hanya tagihan aktif yang masuk KPI Dashboard.
        $totalTagihan = Tagihan::where(
            'status',
            '!=',
            Tagihan::STATUS_DIBATALKAN
        )->count();

        $persenLunas = $totalTagihan > 0
            ? round(($tagihanLunas / $totalTagihan) * 100)
            : 0;

        $persenSebagian = $totalTagihan > 0
            ? round(($tagihanSebagian / $totalTagihan) * 100)
            : 0;

        $persenBelumBayar = $totalTagihan > 0
            ? round(($tagihanBelumBayar / $totalTagihan) * 100)
            : 0;

        $persenJatuhTempo = $totalTagihan > 0
            ? round(($tagihanJatuhTempoCount / $totalTagihan) * 100)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Collection Rate berdasarkan tagihan yang sudah dialokasikan
        |
        | Tidak memakai Pembayaran::total_bayar karena nilai tersebut dapat
        | mengandung biaya admin dan kelebihan pembayaran.
        |--------------------------------------------------------------------------
        */

        $totalNominalTagihan = Tagihan::where(
            'status',
            '!=',
            Tagihan::STATUS_DIBATALKAN
        )->sum('total');

        $totalNominalTerbayar = Tagihan::where(
            'status',
            '!=',
            Tagihan::STATUS_DIBATALKAN
        )->sum('dibayar');

        $collectionRate = $totalNominalTagihan > 0
            ? round(($totalNominalTerbayar / $totalNominalTagihan) * 100, 1)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Pembayaran Terakhir
        |--------------------------------------------------------------------------
        */

        $pembayaranTerakhir = Pembayaran::with([
            'tagihan.pelanggan',
            'user',
        ])
        ->where('metode', '!=', 'Saldo')
        ->latest('tanggal_bayar')
        ->take(10)
        ->get();


        /*
        |--------------------------------------------------------------------------
        | Tagihan Jatuh Tempo
        |--------------------------------------------------------------------------
        */

        $tagihanJatuhTempo = Tagihan::with([
            'pelanggan',
        ])
        ->whereIn('status', [
            Tagihan::STATUS_BELUM_BAYAR,
            Tagihan::STATUS_JATUH_TEMPO,
        ])
        ->whereDate(
            'tanggal_jatuh_tempo',
            '<=',
            today()
        )
        ->orderBy('tanggal_jatuh_tempo')
        ->take(10)
        ->get();


        /*
        |--------------------------------------------------------------------------
        | Grafik Pendapatan 12 Bulan
        |--------------------------------------------------------------------------
        */

        $grafikLabel = [];

        $grafikPendapatan = [];

        for ($bulan = 1; $bulan <= 12; $bulan++) {

            $grafikLabel[] = date(
                'M',
                mktime(0, 0, 0, $bulan, 1)
            );

            $grafikPendapatan[] = Pembayaran::whereYear(
                    'tanggal_bayar',
                    now()->year
                )
                ->whereMonth(
                    'tanggal_bayar',
                    $bulan
                )
                ->where(
                    'status',
                    Pembayaran::STATUS_BERHASIL
                )
                ->where('metode', '!=', 'Saldo')
                ->sum('nominal');
        }

        /*
        |--------------------------------------------------------------------------
        | Monitoring MikroTik
        |--------------------------------------------------------------------------
        */

        $routerMonitor = null;

        $mikrotikStatus = false;

        $pppActive = 0;

        $pppSecret = 0;

        $routerIdentity = '-';

        $routerVersion = '-';

        $routerUptime = '-';

        $routerCpu = 0;

        $routerMemory = '-';

        try {

            $routerMonitor = Router::where('status', 'Aktif')->first();

            if ($routerMonitor) {

                $routerIdentity = $mikrotik->getIdentity($routerMonitor);

                $routerVersion = $mikrotik->getRouterVersion($routerMonitor);

                /*
                |--------------------------------------------------------------
                | Method berikut disiapkan untuk GNS v3
                | Bila belum ada di MikroTikService cukup biarkan
                | return default agar Dashboard tidak error.
                |--------------------------------------------------------------
                */

                if (method_exists($mikrotik,'getSystemResource')) {

                    $resource = $mikrotik->getSystemResource($routerMonitor);

                    $routerCpu = $resource['cpu-load'] ?? 0;

                    $routerMemory = $resource['free-memory'] ?? '-';

                    $routerUptime = $resource['uptime'] ?? '-';

                }

                if (method_exists($mikrotik,'getActivePppCount')) {

                    $pppActive = $mikrotik->getActivePppCount($routerMonitor);

                }

                if (method_exists($mikrotik,'getSecretCount')) {

                    $pppSecret = $mikrotik->getSecretCount($routerMonitor);

                }

                $mikrotikStatus = true;

            }

        } catch (\Throwable $e) {

            $mikrotikStatus = false;

        }

        /*
        |--------------------------------------------------------------------------
        | Audit Trail
        |--------------------------------------------------------------------------
        */

        $auditTerbaru = AuditTrail::with('user')
            ->latest()
            ->take(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        return view(
            'dashboard_enterprise',
            compact(

                'totalPelanggan',
                'pelangganAktif',
                'pelangganNonaktif',

                'totalRouter',
                'routerAktif',
                'routerOffline',

                'tagihanLunas',
                'tagihanHariIni',

                'totalPembayaran',
                'pembayaranHariIni',
                'pendapatanHariIni',
                'pendapatanBulanIni',
                'totalPendapatan',

                'pembayaranTerakhir',
                'tagihanJatuhTempo',

                'grafikLabel',
                'grafikPendapatan',

                'routerMonitor',
                'mikrotikStatus',
                'routerIdentity',
                'routerVersion',
                'routerCpu',
                'routerMemory',
                'routerUptime',
                'pppActive',
                'pppSecret',
                'auditTerbaru',

                'tagihanBelumLunas',
                'tagihanBelumBayar',
                'tagihanSebagian',
                'tagihanJatuhTempoCount',
                'totalPiutang',
                'totalSaldoPelanggan',

                'totalTagihan',
                'persenLunas',
                'persenSebagian',
                'persenBelumBayar',
                'persenJatuhTempo',
                'collectionRate',

                
            )
        );
    }
    public function monitoring(MikroTikService $mikrotik)
{
    $routerMonitor = null;

    $mikrotikStatus = false;

    $pppActive = 0;

    $pppSecret = 0;

    $routerIdentity = '-';

    $routerVersion = '-';

    $routerUptime = '-';

    $routerCpu = 0;

    $routerMemory = '-';

    try {

        $routerMonitor = Router::where('status', 'Aktif')->first();

        if ($routerMonitor) {

            $routerIdentity = $mikrotik->getIdentity($routerMonitor);

            $routerVersion = $mikrotik->getRouterVersion($routerMonitor);

            if (method_exists($mikrotik, 'getSystemResource')) {

                $resource = $mikrotik->getSystemResource($routerMonitor);

                $routerCpu = $resource['cpu-load'] ?? 0;

                $routerMemory = $resource['free-memory'] ?? '-';

                $routerUptime = $resource['uptime'] ?? '-';
            }

            if (method_exists($mikrotik, 'getActivePppCount')) {
                $pppActive = $mikrotik->getActivePppCount($routerMonitor);
            }

            if (method_exists($mikrotik, 'getSecretCount')) {
                $pppSecret = $mikrotik->getSecretCount($routerMonitor);
            }

            $mikrotikStatus = true;
        }

    } catch (\Throwable $e) {

        $mikrotikStatus = false;

    }

    return view('monitoring_mikrotik', compact(
        'routerMonitor',
        'mikrotikStatus',
        'routerIdentity',
        'routerVersion',
        'routerCpu',
        'routerMemory',
        'routerUptime',
        'pppActive',
        'pppSecret'
    ));
}
}