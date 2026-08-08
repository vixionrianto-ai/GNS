<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;

class LaporanService
{
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

        /*
        |--------------------------------------------------------------------------
        | Query Dasar
        |--------------------------------------------------------------------------
        */

        // Pembayaran Saldo adalah transaksi internal, bukan uang baru masuk.
        $pembayaran = Pembayaran::query()
            ->where('status', Pembayaran::STATUS_BERHASIL)
            ->where('metode', '!=', 'Saldo');

        // Tagihan Dibatalkan tidak boleh masuk KPI/piutang laporan.
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

        /*
        |--------------------------------------------------------------------------
        | Data Tabel Laporan
        |--------------------------------------------------------------------------
        */

        $laporan = Tagihan::with([
            'pelanggan.paket'
        ])
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
        ->when($request?->filled('search'), function ($q) use ($request) {
            $search = $request->search;

            $q->where(function ($query) use ($search) {
                $query->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('pelanggan', function ($pelanggan) use ($search) {
                        $pelanggan->where('nama', 'like', "%{$search}%");
                    });
            });
        })
        ->latest('tanggal_tagihan')
        ->paginate(15);

        /*
        |--------------------------------------------------------------------------
        | Grafik Pendapatan 12 Bulan
        |--------------------------------------------------------------------------
        | Pendapatan tagihan memakai nominal bersih pembayaran.
        | Biaya admin dipisahkan dan tidak dihitung sebagai pembayaran tagihan.
        |--------------------------------------------------------------------------
        */

        $labelChart = [];
        $dataChart = [];

        for ($i = 1; $i <= 12; $i++) {
            $labelChart[] = date(
                'M',
                mktime(0, 0, 0, $i, 1)
            );

            $dataChart[] = Pembayaran::whereYear(
                    'tanggal_bayar',
                    now()->year
                )
                ->whereMonth(
                    'tanggal_bayar',
                    $i
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
        | KPI Enterprise
        |--------------------------------------------------------------------------
        */

        $totalTagihan = (clone $tagihan)->sum('total');

        // Nilai pembayaran yang benar-benar dialokasikan ke tagihan.
        $totalDibayar = (clone $tagihan)->sum('dibayar');

        $piutang = (clone $tagihan)->sum('sisa');

        /*
        |--------------------------------------------------------------------------
        | Grafik Status Tagihan
        |--------------------------------------------------------------------------
        */

        $statusChart = [
            (clone $kpiTagihan)->where('status', Tagihan::STATUS_LUNAS)->count(),
            (clone $kpiTagihan)->where('status', Tagihan::STATUS_SEBAGIAN)->count(),
            (clone $kpiTagihan)->where('status', Tagihan::STATUS_BELUM_BAYAR)->count(),
            (clone $kpiTagihan)->where('status', Tagihan::STATUS_JATUH_TEMPO)->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Top Piutang
        |--------------------------------------------------------------------------
        */

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

        return [
            /*
            |--------------------------------------------------------------------------
            | Pendapatan Hari Ini
            |--------------------------------------------------------------------------
            | Nilai pembayaran tagihan, tidak termasuk biaya admin dan Saldo internal.
            |--------------------------------------------------------------------------
            */

            'pendapatanHariIni' => (clone $pembayaran)
                ->when($tanggalAwal, fn($q) =>
                    $q->whereDate('tanggal_bayar', '>=', $tanggalAwal))
                ->when($tanggalAkhir, fn($q) =>
                    $q->whereDate('tanggal_bayar', '<=', $tanggalAkhir))
                ->when($bulan, fn($q) =>
                    $q->whereMonth('tanggal_bayar', $bulan))
                ->when($tahun, fn($q) =>
                    $q->whereYear('tanggal_bayar', $tahun))
                ->whereDate('tanggal_bayar', today())
                ->sum('nominal'),

            /*
            |--------------------------------------------------------------------------
            | Pendapatan Bulan Ini
            |--------------------------------------------------------------------------
            */

            'pendapatanBulanIni' => (clone $pembayaran)
                ->when($tanggalAwal, fn($q) =>
                    $q->whereDate('tanggal_bayar', '>=', $tanggalAwal))
                ->when($tanggalAkhir, fn($q) =>
                    $q->whereDate('tanggal_bayar', '<=', $tanggalAkhir))
                ->when($bulan, fn($q) =>
                    $q->whereMonth('tanggal_bayar', $bulan))
                ->when($tahun, fn($q) =>
                    $q->whereYear('tanggal_bayar', $tahun))
                ->whereYear('tanggal_bayar', now()->year)
                ->whereMonth('tanggal_bayar', now()->month)
                ->sum('nominal'),

            /*
            |--------------------------------------------------------------------------
            | Biaya Admin Terpisah
            |--------------------------------------------------------------------------
            */

            'biayaAdminHariIni' => (clone $pembayaran)
                ->whereDate('tanggal_bayar', today())
                ->sum('biaya_admin'),

            'biayaAdminBulanIni' => (clone $pembayaran)
                ->whereYear('tanggal_bayar', now()->year)
                ->whereMonth('tanggal_bayar', now()->month)
                ->sum('biaya_admin'),

            'totalBiayaAdmin' => (clone $pembayaran)
                ->sum('biaya_admin'),

            'totalTagihan' => $totalTagihan,
            'totalDibayar' => $totalDibayar,
            'piutang' => $piutang,

            'persenLunas' => $totalTagihan > 0
                ? round(($totalDibayar / $totalTagihan) * 100)
                : 0,

            'persenPiutang' => $totalTagihan > 0
                ? round(($piutang / $totalTagihan) * 100)
                : 0,

            'pelangganAktif' => (clone $pelanggan)
                ->where('status', Pelanggan::STATUS_AKTIF)
                ->count(),

            'totalLunas' => (clone $kpiTagihan)
                ->where('status', Tagihan::STATUS_LUNAS)
                ->count(),

            'totalSebagian' => (clone $kpiTagihan)
                ->where('status', Tagihan::STATUS_SEBAGIAN)
                ->count(),

            'totalBelumBayar' => (clone $kpiTagihan)
                ->where('status', Tagihan::STATUS_BELUM_BAYAR)
                ->count(),

            'totalJatuhTempo' => (clone $kpiTagihan)
                ->where('status', Tagihan::STATUS_JATUH_TEMPO)
                ->count(),

            'laporan' => $laporan,
            'chartLabels' => $labelChart,
            'chartData' => $dataChart,
            'statusChart' => $statusChart,
            'topPiutang' => $topPiutang,
        ];
    }
}
