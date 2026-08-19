<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Router;
use App\Models\Tagihan;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $pembayaranTerakhir = Pembayaran::with(['tagihan.pelanggan'])
            ->latest('tanggal_bayar')
            ->take(5)
            ->get();

        $tagihanJatuhTempo = Tagihan::with('pelanggan')
            ->whereIn('status', [
                Tagihan::STATUS_BELUM_BAYAR,
                Tagihan::STATUS_JATUH_TEMPO,
            ])
            ->orderBy('tanggal_jatuh_tempo')
            ->take(5)
            ->get();

        $data = [
            'totalPelanggan' => Pelanggan::count(),
            'pelangganAktif' => Pelanggan::where('status', 'Aktif')->count(),
            'pelangganNonaktif' => Pelanggan::where('status', 'Nonaktif')->count(),
            'totalRouter' => Router::count(),
            'routerAktif' => Router::where('status', 'Aktif')->count(),
            'tagihanBelumBayar' => Tagihan::where('status', Tagihan::STATUS_BELUM_BAYAR)->count(),
            'tagihanLunas' => Tagihan::where('status', Tagihan::STATUS_LUNAS)->count(),
            'pendapatanHariIni' => (float) Pembayaran::whereDate('tanggal_bayar', today())
                ->where('status', Pembayaran::STATUS_BERHASIL)
                ->sum('total_bayar'),
            'pendapatanBulanIni' => (float) Pembayaran::whereYear('tanggal_bayar', now()->year)
                ->whereMonth('tanggal_bayar', now()->month)
                ->where('status', Pembayaran::STATUS_BERHASIL)
                ->sum('total_bayar'),
            'pembayaranTerakhir' => $pembayaranTerakhir,
            'tagihanJatuhTempo' => $tagihanJatuhTempo,
            'serverTime' => now()->toIso8601String(),
            'routerOffline' => Router::where('status', '!=', 'Aktif')->count(),
            'tagihanBelumLunas' => Tagihan::where('status', '!=', Tagihan::STATUS_LUNAS)->count(),
            'tagihanSebagian' => Tagihan::where('status', 'Sebagian')->count(),
            'tagihanJatuhTempoCount' => Tagihan::where('status', Tagihan::STATUS_JATUH_TEMPO)->count(),
            'tagihanHariIni' => Tagihan::whereDate('tanggal_tagihan', today())->count(),
            'totalPembayaran' => Pembayaran::count(),
            'pembayaranHariIni' => Pembayaran::whereDate('tanggal_bayar', today())->count(),
            'totalPendapatan' => (float) Pembayaran::where('status', Pembayaran::STATUS_BERHASIL)->sum('total_bayar'),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Dashboard berhasil dimuat.',
            'data' => $data,
        ]);
    }
}
