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
        | Statistik Pembayaran Eksternal
        |
        | Saldo adalah perpindahan internal pelanggan dan tidak dihitung
        | sebagai pembayaran kas baru.
        |--------------------------------------------------------------------------
        */

        $pembayaranEksternal = Pembayaran::where(
            'status',
            Pembayaran::STATUS_BERHASIL
        )->where('metode', '!=', 'Saldo');

        $totalPembayaran = (clone $pembayaranEksternal)->count();

        $pembayaranHariIni = (clone $pembayaranEksternal)
            ->whereDate('tanggal_bayar', today())
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Pendapatan
        |
        | Gunakan nominal pembayaran tagihan, bukan total_bayar.
        | total_bayar dapat mengandung biaya admin dan kelebihan pembayaran.
        |--------------------------------------------------------------------------
        */

        $pendapatanHariIni = (clone $pembayaranEksternal)
            ->whereDate('tanggal_bayar', today())
            ->sum('nominal');

        $pendapatanBulanIni = (clone $pembayaranEksternal)
            ->whereYear('tanggal_bayar', now()->year)
            ->whereMonth('tanggal_bayar', now()->month)
            ->sum('nominal');

        $totalPendapatan = (clone $pembayaranEksternal)
            ->sum('nominal');

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
