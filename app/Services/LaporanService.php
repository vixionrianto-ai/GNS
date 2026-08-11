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
    public function laporanQuery(?Request $request = null): Builder
    {
        $request ??= request();

        $tanggalAwal  = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;
        $bulan        = $request->bulan;
        $tahun        = $request->tahun;
        $status       = $request->status;

        $query = Tagihan::with(['pelanggan.paket'])
            ->where('status', '!=', Tagihan::STATUS_DIBATALKAN)
            ->when($bulan, fn($q) => $q->where('bulan', $bulan))
            ->when($tahun, fn($q) => $q->where('tahun', $tahun))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->search);

                $q->where(function ($query) use ($search) {
                    $query->where('invoice_no', 'like', "%{$search}%")
                        ->orWhereHas('pelanggan', function ($pelanggan) use ($search) {
                            $pelanggan->where('nama', 'like', "%{$search}%")
                                ->orWhere('kode_pelanggan', 'like', "%{$search}%");
                        });
                });
            });

        if ($tanggalAwal || $tanggalAkhir) {
            if (in_array($status, [Tagihan::STATUS_LUNAS, Tagihan::STATUS_SEBAGIAN], true)) {
                $query->where(function ($q) use ($tanggalAwal, $tanggalAkhir) {
                    $q->whereHas('pembayaran', function ($p) use ($tanggalAwal, $tanggalAkhir) {
                        $p->where('status', Pembayaran::STATUS_BERHASIL)
                            ->where('metode', '!=', 'Saldo')
                            ->when($tanggalAwal, fn($p) => $p->whereDate('tanggal_bayar', '>=', $tanggalAwal))
                            ->when($tanggalAkhir, fn($p) => $p->whereDate('tanggal_bayar', '<=', $tanggalAkhir));
                    })->orWhereHas('alokasi.pembayaran', function ($p) use ($tanggalAwal, $tanggalAkhir) {
                        $p->where('status', Pembayaran::STATUS_BERHASIL)
                            ->where('metode', '!=', 'Saldo')
                            ->when($tanggalAwal, fn($p) => $p->whereDate('tanggal_bayar', '>=', $tanggalAwal))
                            ->when($tanggalAkhir, fn($p) => $p->whereDate('tanggal_bayar', '<=', $tanggalAkhir));
                    });
                });
            } else {
                $query
                    ->when($tanggalAwal, fn($q) => $q->whereDate('tanggal_tagihan', '>=', $tanggalAwal))
                    ->when($tanggalAkhir, fn($q) => $q->whereDate('tanggal_tagihan', '<=', $tanggalAkhir));
            }
        }

        return $query->latest('tanggal_tagihan');
    }

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
                ->when($bulan, fn($q) => $q->where('bulan', $bulan))
                ->when($tahun, fn($q) => $q->where('tahun', $tahun))
                ->when($status, fn($q) => $q->where('status', $status));

            if ($tanggalAwal || $tanggalAkhir) {
                if (in_array($status, [Tagihan::STATUS_LUNAS, Tagihan::STATUS_SEBAGIAN], true)) {
                    $q->where(function ($q) use ($tanggalAwal, $tanggalAkhir) {
                        $q->whereHas('pembayaran', function ($p) use ($tanggalAwal, $tanggalAkhir) {
                            $p->where('status', Pembayaran::STATUS_BERHASIL)
                                ->where('metode', '!=', 'Saldo')
                                ->when($tanggalAwal, fn($p) => $p->whereDate('tanggal_bayar', '>=', $tanggalAwal))
                                ->when($tanggalAkhir, fn($p) => $p->whereDate('tanggal_bayar', '<=', $tanggalAkhir));
                        })->orWhereHas('alokasi.pembayaran', function ($p) use ($tanggalAwal, $tanggalAkhir) {
                            $p->where('status', Pembayaran::STATUS_BERHASIL)
                                ->where('metode', '!=', 'Saldo')
                                ->when($tanggalAwal, fn($p) => $p->whereDate('tanggal_bayar', '>=', $tanggalAwal))
                                ->when($tanggalAkhir, fn($p) => $p->whereDate('tanggal_bayar', '<=', $tanggalAkhir));
                        });
                    });
                } else {
                    $q->when($tanggalAwal, fn($q) => $q->whereDate('tanggal_tagihan', '>=', $tanggalAwal))
                        ->when($tanggalAkhir, fn($q) => $q->whereDate('tanggal_tagihan', '<=', $tanggalAkhir));
                }
            }
        };

        $tagihan = Tagihan::query();
        $filterTagihan($tagihan);

        $pelanggan = Pelanggan::query();

        $kpiTagihan = Tagihan::query();
        $filterTagihan($kpiTagihan);

        $laporan = $this->laporanQuery($request)->paginate(15)->withQueryString();

        // UANG MASUK: berdasarkan tanggal pembayaran, bukan periode tagihan.
        // Status filter laporan tidak membatasi uang masuk karena pembayaran
        // untuk tagihan Juli/September/Oktober tetap merupakan uang yang masuk
        // pada bulan pembayaran tersebut.
        $pembayaranMasuk = Pembayaran::query()
            ->where('status', Pembayaran::STATUS_BERHASIL)
            ->where('metode', '!=', 'Saldo');

        if ($tanggalAwal || $tanggalAkhir) {
            $pembayaranMasuk
                ->when($tanggalAwal, fn($q) => $q->whereDate('tanggal_bayar', '>=', $tanggalAwal))
                ->when($tanggalAkhir, fn($q) => $q->whereDate('tanggal_bayar', '<=', $tanggalAkhir));
        }

        if ($bulan) {
            $pembayaranMasuk->whereMonth('tanggal_bayar', $bulan);
        }

        if ($tahun) {
            $pembayaranMasuk->whereYear('tanggal_bayar', $tahun);
        }

        if (!$tanggalAwal && !$tanggalAkhir && !$bulan && !$tahun) {
            $pembayaranMasuk
                ->whereYear('tanggal_bayar', now()->year)
                ->whereMonth('tanggal_bayar', now()->month);
        }

        $kasMasukBulanIni = (float) (clone $pembayaranMasuk)->sum('total_bayar');
        $biayaAdminBulanIni = (float) (clone $pembayaranMasuk)->sum('biaya_admin');
        $pendapatanBulanIni = $kasMasukBulanIni;

        $pendapatanHariIni = (float) (clone $pembayaranMasuk)
            ->whereDate('tanggal_bayar', today())
            ->sum('total_bayar');

        $biayaAdminHariIni = (float) (clone $pembayaranMasuk)
            ->whereDate('tanggal_bayar', today())
            ->sum('biaya_admin');

        $alokasiSaldo = AlokasiPembayaran::query()
            ->whereNull('tagihan_id')
            ->whereHas('pembayaran', function ($q) use ($pembayaranMasuk) {
                $q->whereIn('id', $pembayaranMasuk->select('id'));
            });

        $saldoMasukBulanIni = (float) $alokasiSaldo->sum('nominal');

        // Grafik Uang Masuk: gunakan total pembayaran berhasil berdasarkan
        // tanggal pembayaran. Jangan gunakan AlokasiPembayaran karena alokasi
        // hanya menunjukkan uang yang sudah ditempelkan ke tagihan tertentu.
        $labelChart = [];
        $dataChart = [];
        $chartYear = $tahun ?: now()->year;

        for ($i = 1; $i <= 12; $i++) {
            $labelChart[] = date('M', mktime(0, 0, 0, $i, 1));

            $dataChart[] = Pembayaran::query()
                ->where('status', Pembayaran::STATUS_BERHASIL)
                ->where('metode', '!=', 'Saldo')
                ->whereYear('tanggal_bayar', $chartYear)
                ->whereMonth('tanggal_bayar', $i)
                ->sum('total_bayar');
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
            'pendapatanBulanIni' => $pendapatanBulanIni,
            'biayaAdminHariIni' => $biayaAdminHariIni,
            'biayaAdminBulanIni' => $biayaAdminBulanIni,
            'totalBiayaAdmin' => (clone $pembayaranMasuk)->sum('biaya_admin'),
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
