<?php

namespace App\Http\Controllers;

use App\Services\LaporanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    protected LaporanService $laporanService;

    public function __construct(LaporanService $laporanService)
    {
        $this->laporanService = $laporanService;
    }

    public function index(Request $request)
    {
        return view(
            'laporan.index',
            $this->laporanService->dashboard($request)
        );
    }

    /**
     * Export laporan sesuai filter aktif ke PDF.
     */
    public function exportPdf(Request $request)
    {
        $laporan = $this->laporanService->laporanQuery($request)->get();

        $pdf = Pdf::loadView('laporan.exports.pdf', [
            'laporan' => $laporan,
            'filters' => $request->only([
                'tanggal_awal', 'tanggal_akhir', 'bulan', 'tahun', 'status', 'search'
            ]),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-tagihan-' . now()->format('Ymd-His') . '.pdf');
    }

    /**
     * Export laporan sesuai filter aktif ke CSV yang kompatibel dengan Excel.
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $laporan = $this->laporanService->laporanQuery($request)->get();
        $filename = 'laporan-tagihan-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($laporan) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM agar Excel membaca karakter Indonesia dengan benar.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'Tanggal Tagihan',
                'Invoice',
                'Pelanggan',
                'Paket',
                'Periode',
                'Total',
                'Dibayar',
                'Sisa',
                'Status',
                'Jatuh Tempo',
            ], ';');

            foreach ($laporan as $index => $item) {
                fputcsv($handle, [
                    $index + 1,
                    optional($item->tanggal_tagihan)->format('d-m-Y'),
                    $item->invoice_no,
                    optional($item->pelanggan)->nama,
                    optional(optional($item->pelanggan)->paket)->nama_paket ?? '-',
                    $item->periode,
                    $item->total,
                    $item->dibayar,
                    $item->sisa,
                    $item->status,
                    optional($item->tanggal_jatuh_tempo)->format('d-m-Y'),
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
