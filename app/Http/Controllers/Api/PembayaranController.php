<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Requests\Api\ApiPembayaranRequest;
use App\Http\Resources\PembayaranResource;
use App\Services\PembayaranService;
use App\Services\LaporanService;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PembayaranController extends Controller
{
    use ApiResponse;

    public function index(
        PembayaranService $service
    ): JsonResponse
    {
        $data = $service->getList();

        return $this->success(
            PembayaranResource::collection(
                $data->items()
            ),
            'Data pembayaran berhasil diambil.',
            200,
            [
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page'    => $data->lastPage(),
                    'per_page'     => $data->perPage(),
                    'total'        => $data->total(),
                ],
            ]
        );
    }

    public function show(
        int $id,
        PembayaranService $service
    ): JsonResponse
    {
        return $this->success(
            new PembayaranResource(
                $service->getDetail($id)
            ),
            'Detail pembayaran berhasil diambil.'
        );
    }

    public function store(
        ApiPembayaranRequest $request,
        PembayaranService $service
    ): JsonResponse
    {
        Log::info('MASUK API PEMBAYARAN', [
            'request' => $request->all(),
            'user_id' => auth()->id(),
        ]);

        try {
            $pembayaran = $service->bayar(
                $request->validated()
            );

            return $this->success(
                new PembayaranResource(
                    $pembayaran->load([
                        'tagihan',
                        'user',
                    ])
                ),
                'Pembayaran berhasil.',
                201
            );

        } catch (\Throwable $e) {
            Log::error('API Pembayaran gagal', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Mengambil summary murni 100% dari LaporanService website secara dinamis
     */
    public function summary(
        Request $request,
        LaporanService $laporanService
    ): JsonResponse
    {
        // 1. Ambil data mentah asli dari LaporanService website secara utuh
        $data = $laporanService->dashboard($request);

        // 2. Pembersih angka otomatis untuk nominal keuangan
        $clean = function ($val) {
            if (is_numeric($val)) return (double) $val;
            return (double) preg_replace('/[^0-9.]/', '', str_replace(['.', ','], ['', '.'], (string) $val));
        };

        // 3. Ambil data murni langsung dari hasil perhitungan website
        $hariIni  = $clean($data['pendapatanHariIni'] ?? 0);
        $bulanIni = $clean($data['pendapatanBulanIni'] ?? 0);
        $totalTag = $clean($data['totalTagihan'] ?? 0);
        $piutang  = $clean($data['piutang'] ?? 0);

        // Pelanggan Aktif murni mengambil dari hasil LaporanService website
        $pelAktif = (int) ($data['pelangganAktif'] ?? 0);

        // Status tagihan murni mengambil dari LaporanService website
        $lunas    = (int) ($data['totalLunas'] ?? 0);
        $jatuhTmp = (int) ($data['totalJatuhTempo'] ?? 0);

        return $this->success(
            [
                // Keuangan
                'hari_ini'            => $hariIni,
                'pendapatan_hari_ini' => $hariIni,
                'pendapatanHariIni'   => $hariIni,

                'bulan_ini'           => $bulanIni,
                'pendapatan_bulan'    => $bulanIni,
                'pendapatanBulan'     => $bulanIni,

                'total'               => $totalTag,
                'total_tagihan'       => $totalTag,
                'totalTagihan'        => $totalTag,

                'piutang'             => $piutang,

                // Statistik (Sesuai persis dengan website / MikroTik)
                'pelanggan_aktif'     => $pelAktif,
                'pelangganAktif'      => $pelAktif,
                'lunas'               => $lunas,
                'totalLunas'          => $lunas,
                'jatuh_tempo'         => $jatuhTmp,
                'totalJatuhTempo'     => $jatuhTmp,
            ],
            'Summary pembayaran berhasil diambil.'
        );
    }

    /**
     * Mengambil daftar histori pembayaran
     */
    public function history(
        PembayaranService $service
    ): JsonResponse
    {
        $data = $service->getList();

        return $this->success(
            PembayaranResource::collection(
                $data->items()
            ),
            'History pembayaran berhasil diambil.',
            200,
            [
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page'    => $data->lastPage(),
                    'per_page'     => $data->perPage(),
                    'total'        => $data->total(),
                ],
            ]
        );
    }
}