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
        $totalPelanggan = Pelanggan::count();
        $pelangganAktif = Pelanggan::where('status', 'Aktif')->count();
        $pelangganNonaktif = Pelanggan::where('status', 'Nonaktif')->count();

        $totalRouter = Router::count();
        $routerAktif = Router::where('status', 'Aktif')->count();
        $routerOffline = max($totalRouter - $routerAktif, 0);

        $tagihanBelumBayar = Tagihan::where('status', Tagihan::STATUS_BELUM_BAYAR)->count();
        $tagihanSebagian = Tagihan::where('status', Tagihan::STATUS_SEBAGIAN)->count();
        $tagihanJatuhTempoCount = Tagihan::where('status', Tagihan::STATUS_JATUH_TEMPO)->count();
        $tagihanLunas = Tagihan::where('status', Tagihan::STATUS_LUNAS)->count();

        $tagihanBelumLunas = $tagihanBelumBayar + $tagihanSebagian + $tagihanJatuhTempoCount;

        $tagihanHariIni = Tagihan::whereDate('created_at', today())
            ->where('status', '!=', Tagihan::STATUS_DIBATALKAN)
            ->count();

        $totalPiutang = Tagihan::whereIn('status', [
            Tagihan::STATUS_BELUM_BAYAR,
            Tagihan::STATUS_SEBAGIAN,
            Tagihan::STATUS_JATUH_TEMPO,
        ])->sum('sisa');

        $pembayaranEksternal = Pembayaran::query()
            ->where('status', Pembayaran::STATUS_BERHASIL)
            ->where('metode', '!=', 'Saldo');

        $totalPembayaran = (clone $pembayaranEksternal)->count();

        $pembayaranHariIni = (clone $pembayaranEksternal)
            ->whereDate('tanggal_bayar', today())
            ->count();

        // Pendapatan Dashboard = uang yang benar-benar masuk berdasarkan tanggal pembayaran.
        // Gunakan total_bayar agar konsisten dengan LaporanService dan metrik kas masuk.
        $pendapatanHariIni = (clone $pembayaranEksternal)
            ->whereDate('tanggal_bayar', today())
            ->sum('total_bayar');

        $pendapatanBulanIni = (clone $pembayaranEksternal)
            ->whereYear('tanggal_bayar', now()->year)
            ->whereMonth('tanggal_bayar', now()->month)
            ->sum('total_bayar');

        $totalPendapatan = (clone $pembayaranEksternal)->sum('total_bayar');

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
