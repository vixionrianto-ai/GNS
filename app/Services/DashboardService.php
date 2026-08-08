<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Router;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\SaldoPelanggan;

class DashboardService
{
    public function getStatistics(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Statistik Pelanggan
        |--------------------------------------------------------------------------
        */

        $totalPelanggan = Pelanggan::count();

        $pelangganAktif = Pelanggan::where('status', 'Aktif')->count();

        $pelangganNonaktif = Pelanggan::where('status', 'Nonaktif')->count();

        /*
        |--------------------------------------------------------------------------
        | Statistik Router
        |--------------------------------------------------------------------------
        */

        $totalRouter = Router::count();

        $routerAktif = Router::where('status', 'Aktif')->count();

        $routerOffline = max($totalRouter - $routerAktif, 0);

        /*
        |--------------------------------------------------------------------------
        | Statistik Tagihan
        |--------------------------------------------------------------------------
        */

        $tagihanBelumLunas = Tagihan::whereIn('status', [
            Tagihan::STATUS_BELUM_BAYAR,
            Tagihan::STATUS_SEBAGIAN,
            Tagihan::STATUS_JATUH_TEMPO,
        ])->count();

        $tagihanBelumBayar = Tagihan::where(
            'status',
            Tagihan::STATUS_BELUM_BAYAR
        )->count();

        $tagihanSebagian = Tagihan::where(
            'status',
            Tagihan::STATUS_SEBAGIAN
        )->count();

        $tagihanJatuhTempoCount = Tagihan::where(
            'status',
            Tagihan::STATUS_JATUH_TEMPO
        )->count();

        $tagihanLunas = Tagihan::where(
            'status',
            Tagihan::STATUS_LUNAS
        )->count();

        $tagihanHariIni = Tagihan::whereDate(
            'created_at',
            today()
        )->count();

        $totalPiutang = Tagihan::whereIn('status', [
            Tagihan::STATUS_BELUM_BAYAR,
            Tagihan::STATUS_SEBAGIAN,
            Tagihan::STATUS_JATUH_TEMPO,
        ])->sum('sisa');

        /*
        |--------------------------------------------------------------------------
        | Statistik Pembayaran
        |--------------------------------------------------------------------------
        */

        $totalPembayaran = Pembayaran::where(
            'status',
            Pembayaran::STATUS_BERHASIL
        )->count();

        $pembayaranHariIni = Pembayaran::whereDate(
            'tanggal_bayar',
            today()
        )
        ->where(
            'status',
            Pembayaran::STATUS_BERHASIL
        )
        ->count();

        $pendapatanHariIni = Pembayaran::whereDate(
            'tanggal_bayar',
            today()
        )
        ->where('status', Pembayaran::STATUS_BERHASIL)
        ->where('metode', '!=', 'Saldo')
        ->sum('total_bayar');

        $pendapatanBulanIni = Pembayaran::whereYear(
            'tanggal_bayar',
            now()->year
        )
        ->whereMonth(
            'tanggal_bayar',
            now()->month
        )
        ->where('status', Pembayaran::STATUS_BERHASIL)
        ->where('metode', '!=', 'Saldo')
        ->sum('total_bayar');

        $totalPendapatan = Pembayaran::where(
            'status',
            Pembayaran::STATUS_BERHASIL
        )
        ->where('metode', '!=', 'Saldo')
        ->sum('total_bayar');

        /*
        |--------------------------------------------------------------------------
        | Saldo
        |--------------------------------------------------------------------------
        */

        $totalSaldoPelanggan = SaldoPelanggan::sum('saldo');

        return compact(

            'totalPelanggan',
            'pelangganAktif',
            'pelangganNonaktif',

            'totalRouter',
            'routerAktif',
            'routerOffline',

            'tagihanBelumLunas',
            'tagihanBelumBayar',
            'tagihanSebagian',
            'tagihanJatuhTempoCount',
            'tagihanLunas',
            'tagihanHariIni',
            'totalPiutang',

            'totalPembayaran',
            'pembayaranHariIni',
            'pendapatanHariIni',
            'pendapatanBulanIni',
            'totalPendapatan',

            'totalSaldoPelanggan'
        );
    }
}