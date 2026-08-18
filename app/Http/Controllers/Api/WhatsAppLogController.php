<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Models\WhatsAppLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppLogController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = WhatsAppLog::with(['pelanggan:id,nama,kode_pelanggan', 'tagihan:id,invoice_no,periode']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('sent_at', $request->tanggal);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor', 'like', "%{$search}%")
                    ->orWhereHas('pelanggan', function ($pelanggan) use ($search) {
                        $pelanggan->where('nama', 'like', "%{$search}%");
                    })
                    ->orWhereHas('tagihan', function ($tagihan) use ($search) {
                        $tagihan->where('invoice_no', 'like', "%{$search}%");
                    });
            });
        }

        $logs = $query->latest('id')->paginate($request->integer('per_page', 20));

        return $this->success(
            $logs->items(),
            'Riwayat WhatsApp berhasil diambil.',
            200,
            [
                'pagination' => [
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total(),
                    'from' => $logs->firstItem(),
                    'to' => $logs->lastItem(),
                ],
                'statistics' => [
                    'total' => WhatsAppLog::count(),
                    'success' => WhatsAppLog::where('status', 'success')->count(),
                    'failed' => WhatsAppLog::where('status', 'failed')->count(),
                    'pending' => WhatsAppLog::where('status', 'pending')->count(),
                    'today' => WhatsAppLog::whereDate('sent_at', today())->count(),
                ],
            ]
        );
    }

    public function show(int $id): JsonResponse
    {
        $log = WhatsAppLog::with(['pelanggan:id,nama,kode_pelanggan', 'tagihan:id,invoice_no,periode'])
            ->find($id);

        if (!$log) {
            return $this->notFound('Riwayat WhatsApp tidak ditemukan.');
        }

        return $this->success($log, 'Detail riwayat WhatsApp berhasil diambil.');
    }
}
