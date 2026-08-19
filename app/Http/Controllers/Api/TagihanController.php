<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TagihanController as WebTagihanController;
use App\Models\Tagihan;
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
                $q->where('invoice', 'like', "%{$search}%")
                    ->orWhereHas('pelanggan', fn ($p) => $p->where('nama', 'like', "%{$search}%"));
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'Tagihan berhasil dimuat.',
            'data' => $query->latest()->paginate($request->integer('per_page', 20)),
        ]);
    }

    public function show(Tagihan $tagihan)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail tagihan berhasil dimuat.',
            'data' => $tagihan->load(['pelanggan', 'alokasiPembayarans']),
        ]);
    }

    public function destroy(Tagihan $tagihan)
    {
        $response = app(WebTagihanController::class)->destroy($tagihan);
        return $this->normalizeResponse($response);
    }

    public function generate(Request $request)
    {
        $response = app(WebTagihanController::class)->generate($request);
        return $this->normalizeResponse($response);
    }

    private function normalizeResponse($response)
    {
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            return $response;
        }

        return response()->json([
            'success' => true,
            'message' => 'Operasi tagihan berhasil.',
        ]);
    }
}
