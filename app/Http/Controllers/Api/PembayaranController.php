<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PembayaranRequest;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Services\PembayaranService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembayaran::with(['tagihan.pelanggan', 'user'])->latest();
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('tagihan.pelanggan', fn ($p) => $p->where('nama', 'like', "%{$search}%"));
            });
        }
        return response()->json(['success' => true, 'data' => $query->paginate($request->integer('per_page', 15))]);
    }

    public function show(Pembayaran $pembayaran)
    {
        return response()->json(['success' => true, 'data' => $pembayaran->load(['tagihan.pelanggan.paket', 'tagihan.pelanggan.router', 'user'])]);
    }

    public function create(Tagihan $tagihan)
    {
        return response()->json(['success' => true, 'data' => $tagihan->load('pelanggan')]);
    }

    public function store(PembayaranRequest $request, PembayaranService $service)
    {
        try {
            $pembayaran = $service->bayar($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil.',
                'data' => $pembayaran->load(['tagihan.pelanggan', 'user']),
            ], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function cancel(Pembayaran $pembayaran, PembayaranService $service)
    {
        try {
            $pembayaran = $service->batalkan($pembayaran);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dibatalkan dan status tagihan dihitung ulang.',
                'data' => $pembayaran->load(['tagihan.pelanggan', 'user']),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function invoice(Pembayaran $pembayaran)
    {
        return response()->json(['success' => true, 'data' => $pembayaran->load(['tagihan.pelanggan.paket', 'tagihan.pelanggan.router', 'user'])]);
    }

    public function pdf(Pembayaran $pembayaran)
    {
        $pembayaran->load(['tagihan.pelanggan.paket', 'tagihan.pelanggan.router', 'user']);
        return Pdf::loadView('pembayaran.pdf', compact('pembayaran'))->setPaper('A4', 'portrait')
            ->download('Invoice-'.$pembayaran->invoice_no.'.pdf');
    }
}
