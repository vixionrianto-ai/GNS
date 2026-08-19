<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PembayaranController as WebPembayaranController;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembayaran::query()->with(['tagihan.pelanggan']);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pembayaran', 'like', "%{$search}%")
                    ->orWhereHas('tagihan.pelanggan', fn ($p) => $p->where('nama', 'like', "%{$search}%"));
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dimuat.',
            'data' => $query->latest()->paginate($request->integer('per_page', 20)),
        ]);
    }

    public function show(Pembayaran $pembayaran)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail pembayaran berhasil dimuat.',
            'data' => $pembayaran->load(['tagihan.pelanggan', 'alokasiPembayarans']),
        ]);
    }

    public function store(Request $request)
    {
        // Reuse the exact website payment flow/service instead of duplicating
        // allocation, status, balance, and transaction rules in Android.
        return app(WebPembayaranController::class)->store($request);
    }

    public function invoice(Pembayaran $pembayaran)
    {
        return app(WebPembayaranController::class)->invoice($pembayaran);
    }

    public function pdf(Pembayaran $pembayaran)
    {
        return app(WebPembayaranController::class)->pdf($pembayaran);
    }

    public function create(Tagihan $tagihan)
    {
        return response()->json([
            'success' => true,
            'message' => 'Form pembayaran siap.',
            'data' => $tagihan->load('pelanggan'),
        ]);
    }
}
