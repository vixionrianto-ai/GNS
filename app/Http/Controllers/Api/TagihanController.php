<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use App\Services\TagihanService;
use Illuminate\Http\Request;

class TagihanController extends Controller
{
    public function index(Request $request)
    {
        $query = Tagihan::query()->with('pelanggan');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('pelanggan', fn ($p) => $p->where('nama', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('pelanggan_id')) {
            $query->where('pelanggan_id', $request->integer('pelanggan_id'));
        }

        $paginator = $query->latest()->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Tagihan berhasil dimuat.',
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    public function show(Tagihan $tagihan)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail tagihan berhasil dimuat.',
            'data' => $tagihan->load(['pelanggan', 'alokasi']),
        ]);
    }

    public function destroy(Tagihan $tagihan)
    {
        if ($tagihan->pembayaran()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan tidak dapat dihapus karena sudah memiliki riwayat pembayaran.',
            ], 422);
        }

        try {
            $tagihan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tagihan berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function generate(Request $request, TagihanService $tagihanService)
    {
        try {
            $jumlah = $tagihanService->generateHarian();

            return response()->json([
                'success' => true,
                'message' => "{$jumlah} tagihan berhasil dibuat.",
                'data' => ['jumlah' => $jumlah],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}