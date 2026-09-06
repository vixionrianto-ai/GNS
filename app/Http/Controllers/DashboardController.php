<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Router;
use App\Models\Tagihan;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPelanggan = Pelanggan::count();
        $pelangganAktif = Pelanggan::where('status', 'Aktif')->count();
        $pelangganNonaktif = Pelanggan::where('status', 'Nonaktif')->count();

        $totalRouter = Router::count();
        $routerAktif = Router::where('status', 'Aktif')->count();

        $tagihanBelumBayar = Tagihan::where('status', Tagihan::STATUS_BELUM_BAYAR)->count();
        $tagihanLunas = Tagihan::where('status', Tagihan::STATUS_LUNAS)->count();

        $pendapatanHariIni = Pembayaran::whereDate('tanggal_bayar', today())
            ->where('status', Pembayaran::STATUS_BERHASIL)
            ->sum('total_bayar');

        $pendapatanBulanIni = Pembayaran::whereYear('tanggal_bayar', now()->year)
            ->whereMonth('tanggal_bayar', now()->month)
            ->where('status', Pembayaran::STATUS_BERHASIL)
            ->sum('total_bayar');

        $pembayaranTerakhir = Pembayaran::with([
            'tagihan.pelanggan',
            'user',
        ])
            ->latest('tanggal_bayar')
            ->take(5)
            ->get();

        // Tampilkan semua tagihan yang sudah jatuh tempo dan masih memiliki sisa,
        // termasuk tagihan yang baru terbayar sebagian.
        $tagihanJatuhTempo = Tagihan::with('pelanggan')
            ->whereNotIn('status', [
                Tagihan::STATUS_LUNAS,
                Tagihan::STATUS_DIBATALKAN,
            ])
            ->whereDate('tanggal_jatuh_tempo', '<=', today())
            ->where(function ($query) {
                $query->where('sisa', '>', 0)
                    ->orWhereNull('sisa');
            })
            ->orderBy('tanggal_jatuh_tempo')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalPelanggan',
            'pelangganAktif',
            'pelangganNonaktif',
            'totalRouter',
            'routerAktif',
            'tagihanBelumBayar',
            'tagihanLunas',
            'pendapatanHariIni',
            'pendapatanBulanIni',
            'pembayaranTerakhir',
            'tagihanJatuhTempo'
        ));
    }
}
