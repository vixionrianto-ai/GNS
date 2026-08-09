<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LaporanService
{
    /**
     * Query utama laporan tagihan, dipakai dashboard dan export.
     */
    public function laporanQuery(?Request $request = null): Builder
    {
        $request ??= request();

        $tanggalAwal  = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;
        $bulan        = $request->bulan;
        $tahun        = $request->tahun;
        $status       = $request->status;

        return Tagihan::with(['pelanggan.paket'])
            ->where('status', '!=', Tagihan::STATUS_DIBATALKAN)
            ->when($tanggalAwal, fn($q) =>
                $q->whereDate('tanggal_tagihan', '>=', $tanggalAwal))
            ->when($tanggalAkhir, fn($q) =>
                $q->whereDate('tanggal_tagihan', '<=', $tanggalAkhir))
            ->when($bulan, fn($q) =>
                $q->where('bulan', $bulan))
            ->when($tahun, fn($q) =>
                $q->where('tahun', $tahun))
            ->when($status, fn($q) =>
                $q->where('status', $status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->search);

                $q->where(function ($query) use ($search) {
                    $query->where('invoice_no', 'like', "%{$search}%")
                        ->orWhereHas('pelanggan', function ($pelanggan) use ($search) {
                            $pelanggan->where('nama', 'like', "%{$search}%")
                                ->orWhere('kode_pelanggan', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('tanggal_tagihan');
    }

    /**
     * Data dashboard laporan.
     */
    public function dashboard(?Request $request = null): array
    {
        $tanggalAwal  = $request?->tanggal_awal;
        $tanggalAkhir = $request?->tanggal_akhir;
        $bulan        = $request?->bulan;
        $tahun        = $request?->tahun;
        $status       = $request?->status;

        $pembayaran = Pembayaran::query()
            ->where('status', Pembayaran::STATUS_BERHASIL)
            ->where('metode', '!=', 'Saldo');

        // Semua metrik pembayaran harus mengikuti filter laporan yang sama.
        $pembayaranTerfilter = (clone $pembayaran)
            ->when($tanggalAwal, fn($q) =>
                $q->whereDate('tanggal_bayar', '>=', $tanggalAwal))
            ->when($tanggalAkhir, fn($q) =>
                $q->whereDate('tanggal_bayar', '<=', $tanggalAkhir))
            ->when($bulan, fn($q) =>
                $q->whereMonth('tanggal_bayar', $bulan))
            ->when($tahun, fn($q) =>
                $q->whereYear('tanggal_bayar', $tahun));

        $tagihan = Tagihan::query()
            ->where('status', '!=', Tagihan::STATUS_DIBATALKAN)
            ->when($tanggalAwal, fn($q) =>
                $q->whereDate('tanggal_tagihan', '>=', $tanggalAwal))
            ->when($tanggalAkhir, fn($q) =>
                $q->whereDate('tanggal_tagihan', '<=', $tanggalAkhir))
            ->when($bulan, fn($q) =>
                $q->where('bulan', $bulan))
            ->when($tahun, fn($q) =>
                $q->where('tahun', $tahun));

        $pelanggan = Pelanggan::query();

        $kpiTagihan = Tagihan::query()
            ->where('status', '!=', Tagihan::STATUS_DIBATALKAN)
            ->when($tanggalAwal, fn($q) =>
                $q->whereDate('tanggal_tagihan', '>=', $tanggalAwal))
            ->when($tanggalAkhir, fn($q) =>
                $q->whereDate('tanggal_tagihan', '<=', $tanggalAkhir))
            ->when($bulan, fn($q) =>
                $q->where('bulan', $bulan))
            ->when($tahun, fn($q) =>
                $q->where('tahun', $tahun));

        $laporan = $this->laporanQuery($request)->paginate(15)->withQueryString();

        $labelChart = [];
        $dataChart = [];

        $chartYear = $tahun ?: now()->year;

        for ($i = 1; $i <= 12; $i++) {
            $labelChart[] = date('M', mktime(0, 0, 0, $i, 1));

            $dataChart[] = Pembayaran::whereYear('tanggal_bayar', $chartYear)
                ->whereMonth('tanggal_bayar', $i)
                ->where('status', Pembayaran::STATUS_BERHASIL)
                ->where('metode', '!=', 'Saldo')
                ->sum('nominal');
        }

        $totalTagihan = (clone $tagihan)->sum('total');
        $totalDibayar = (clone $tagihan)->sum('dibayar');
        $piutang = (clone $tagihan)->sum('sisa');

        $statusChart = [
            (clone $kpiTagihan)->where('status', Tagihan::STATUS_LUNAS)->count(),
            (clone $kpiTagihan)->where('status', Tagihan::STATUS_SEBAGIAN)->count(),
            (clone $kpiTagihan)->where('status', Tagihan::STATUS_BELUM_BAYAR)->count(),
            (clone $kpiTagihan)->where('status', Tagihan::STATUS_JATUH_TEMPO)->count(),
        ];

        $topPiutang = Tagihan::with('pelanggan')
            ->where('status', '!=', Tagihan::STATUS_DIBATALKAN)
            ->when($tanggalAwal, fn($q) =>
                $q->whereDate('tanggal_tagihan', '>=', $tanggalAwal))
            ->when($tanggalAkhir, fn($q) =>
                $q->whereDate('tanggal_tagihan', '<=', $tanggalAkhir))
            ->when($bulan, fn($q) =>
                $q->where('bulan', $bulan))
            ->when($tahun, fn($q) =>
                $q->where('tahun', $tahun))
            ->when($status, fn($q) =>
                $q->where('status', $status))
            ->where('sisa', '>', 0)
            ->orderByDesc('sisa')
            ->limit(10)
            ->get();

        $adaFilterPeriode = $tanggalAwal || $tanggalAkhir || $bulan || $tahun;

        if ($adaFilterPeriode) {
            $kasMasukBulanIni = (clone $pembayaranTerfilter)->sum('total_bayar');
            $biayaAdminBulanIni = (clone $pembayaranTerfilter)->sum('biaya_admin');
        } else {
            $kasMasukBulanIni = (clone $pembayaranTerfilter)
                ->whereYear('tanggal_bayar', now()->year)
                ->whereMonth('tanggal_bayar', now()->month)
                ->sum('total_bayar');
            $biayaAdminBulanIni = (clone $pembayaranTerfilter)
                ->whereYear('tanggal_bayar', now()->year)
                ->whereMonth('tanggal_bayar', now()->month)
                ->sum('biaya_admin');
        }

        $saldoMasukBulanIni = max(
            0,
            $kasMasukBulanIni
            - (clone $pembayaranTerfilter)->sum('nominal')
            - (clone $pembayaranTerfilter)->sum('biaya_admin')
        );

        return [
            'pendapatanHariIni' => (clone $pembayaranTerfilter)
                ->whereDate('tanggal_bayar', today())
                ->sum('nominal'),

            'pendapatanBulanIni' => $adaFilterPeriode
                ? (clone $pembayaranTerfilter)->sum('nominal')
                : (clone $pembayaranTerfilter)
                    ->whereYear('tanggal_bayar', now()->year)
                    ->whereMonth('tanggal_bayar', now()->month)
                    ->sum('nominal'),

            'biayaAdminHariIni' => (clone $pembayaranTerfilter)
                ->whereDate('tanggal_bayar', today())
                ->sum('biaya_admin'),
            'biayaAdminBulanIni' => $biayaAdminBulanIni,
            'totalBiayaAdmin' => (clone $pembayaranTerfilter)->sum('biaya_admin'),
            'kasMasukBulanIni' => $kasMasukBulanIni,
            'saldoMasukBulanIni' => $saldoMasukBulanIni,
            'totalTagihan' => $totalTagihan,
            'totalDibayar' => $totalDibayar,
            'piutang' => $piutang,
            'persenLunas' => $totalTagihan > 0 ? round(($totalDibayar / $totalTagihan) * 100) : 0,
            'persenPiutang' => $totalTagihan > 0 ? round(($piutang / $totalTagihan) * 100) : 0,
            'pelangganAktif' => (clone $pelanggan)->where('status', Pelanggan::STATUS_AKTIF)->count(),
            'totalLunas' => (clone $kpiTagihan)->where('status', Tagihan::STATUS_LUNAS)->count(),
            'totalSebagian' => (clone $kpiTagihan)->where('status', Tagihan::STATUS_SEBAGIAN)->count(),
            'totalBelumBayar' => (clone $kpiTagihan)->where('status', Tagihan::STATUS_BELUM_BAYAR)->count(),
            'totalJatuhTempo' => (clone $kpiTagihan)->where('status', Tagihan::STATUS_JATUH_TEMPO)->count(),
            'laporan' => $laporan,
            'chartLabels' => $labelChart,
            'chartData' => $dataChart,
            'statusChart' => $statusChart,
            'topPiutang' => $topPiutang,
        ];
    }
}
