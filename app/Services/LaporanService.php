<?php

namespace App\Services;

use App\Models\AlokasiPembayaran;
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
     *
     * Prinsip keuangan:
     * - Tagihan tetap menjadi sumber periode laporan.
     * - Pendapatan tagihan dihitung dari AlokasiPembayaran yang benar-benar
     *   masuk ke tagihan, bukan Pembayaran::nominal mentah.
     * - Saldo pelanggan dihitung dari alokasi tanpa tagihan.
     * - Kas masuk tetap memakai total_bayar dari pembayaran yang terkait
     *   dengan dataset tagihan terpilih.
     */
    public function dashboard(?Request $request = null): array
    {
        $request ??= request();

        $tanggalAwal  = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;
        $bulan        = $request->bulan;
        $tahun        = $request->tahun;
        $status       = $request->status;

        $filterTagihan = function ($q) use ($tanggalAwal, $tanggalAkhir, $bulan, $tahun, $status) {
            $q->where('status', '!=', Tagihan::STATUS_DIBATALKAN)
                ->when($tanggalAwal, fn($q) =>
                    $q->whereDate('tanggal_tagihan', '>=', $tanggalAwal))
                ->when($tanggalAkhir, fn($q) =>
                    $q->whereDate('tanggal_tagihan', '<=', $tanggalAkhir))
                ->when($bulan, fn($q) =>
                    $q->where('bulan', $bulan))
                ->when($tahun, fn($q) =>
                    $q->where('tahun', $tahun))
                ->when($status, fn($q) =>
                    $q->where('status', $status));
        };

        $pembayaran = Pembayaran::query()
            ->where('status', Pembayaran::STATUS_BERHASIL)
            ->where('metode', '!=', 'Saldo')
            ->whereHas('tagihan', $filterTagihan);

        $adaFilterPeriode = (bool) ($tanggalAwal || $tanggalAkhir || $bulan || $tahun);

        $pembayaranTerfilter = clone $pembayaran;

        if (!$adaFilterPeriode) {
            $pembayaranTerfilter
                ->whereYear('tanggal_bayar', now()->year)
                ->whereMonth('tanggal_bayar', now()->month);
        }

        $tagihan = Tagihan::query();
        $filterTagihan($tagihan);

        $pelanggan = Pelanggan::query();

        $kpiTagihan = Tagihan::query();
        $filterTagihan($kpiTagihan);

        $laporan = $this->laporanQuery($request)->paginate(15)->withQueryString();

        /*
         * Pendapatan = nominal alokasi yang benar-benar masuk ke tagihan.
         * Ini mencegah uang yang langsung menjadi saldo pelanggan dihitung
         * sebagai pendapatan tagihan.
         */
        $alokasiTagihan = AlokasiPembayaran::query()
            ->whereNotNull('tagihan_id')
            ->whereHas('pembayaran', function ($q) use ($pembayaranTerfilter) {
                $q->whereIn('id', $pembayaranTerfilter->select('id'));
            })
            ->whereHas('tagihan', $filterTagihan);

        $pendapatanTerfilter = (clone $alokasiTagihan)->sum('nominal');

        $alokasiSaldo = AlokasiPembayaran::query()
            ->whereNull('tagihan_id')
            ->whereHas('pembayaran', function ($q) use ($pembayaranTerfilter) {
                $q->whereIn('id', $pembayaranTerfilter->select('id'));
            });

        $saldoMasukBulanIni = (float) $alokasiSaldo->sum('nominal');

        $kasMasukBulanIni = (float) (clone $pembayaranTerfilter)->sum('total_bayar');
        $biayaAdminBulanIni = (float) (clone $pembayaranTerfilter)->sum('biaya_admin');

        $pendapatanHariIni = (clone $alokasiTagihan)
            ->whereHas('pembayaran', function ($q) {
                $q->whereDate('tanggal_bayar', today());
            })
            ->sum('nominal');

        $biayaAdminHariIni = (clone $pembayaranTerfilter)
            ->whereDate('tanggal_bayar', today())
            ->sum('biaya_admin');

        $labelChart = [];
        $dataChart = [];
        $chartYear = $tahun ?: now()->year;

        for ($i = 1; $i <= 12; $i++) {
            $labelChart[] = date('M', mktime(0, 0, 0, $i, 1));

            $dataChart[] = AlokasiPembayaran::query()
                ->whereNotNull('tagihan_id')
                ->whereHas('pembayaran', function ($q) {
                    $q->where('status', Pembayaran::STATUS_BERHASIL)
                        ->where('metode', '!=', 'Saldo');
                })
                ->whereHas('tagihan', function ($q) use ($i, $chartYear, $status) {
                    $q->where('status', '!=', Tagihan::STATUS_DIBATALKAN)
                        ->whereYear('tanggal_tagihan', $chartYear)
                        ->whereMonth('tanggal_tagihan', $i)
                        ->when($status, fn($q) =>
                            $q->where('status', $status));
                })
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

        $topPiutang = Tagihan::with('pelanggan');
        $filterTagihan($topPiutang);
        $topPiutang = $topPiutang
            ->where('sisa', '>', 0)
            ->orderByDesc('sisa')
            ->limit(10)
            ->get();

        return [
            'pendapatanHariIni' => $pendapatanHariIni,
            'pendapatanBulanIni' => $pendapatanTerfilter,
            'biayaAdminHariIni' => $biayaAdminHariIni,
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
