<?php

namespace App\Http\Controllers;

use App\Http\Requests\PembayaranRequest;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Services\PembayaranService;

class PembayaranController extends Controller
{
    /**
     * Riwayat pembayaran
     */
    public function index()
    {
        $pembayarans = Pembayaran::with([
            'tagihan.pelanggan',
            'user',
        ])
        ->latest()
        ->paginate(15);

        return view(
            'pembayaran.index',
            compact('pembayarans')
        );
    }

    /**
     * Detail pembayaran
     */
    public function show(Pembayaran $pembayaran)
    {
        $pembayaran->load([
            'tagihan.pelanggan.paket',
            'tagihan.pelanggan.router',
            'user',
        ]);

        return view(
            'pembayaran.show',
            compact('pembayaran')
        );
    }

    /**
     * Form pembayaran
     */
    public function create(Tagihan $tagihan)
    {
        return view(
            'pembayaran.create',
            compact('tagihan')
        );
    }

    /**
     * Simpan pembayaran
     */
    public function store(
        PembayaranRequest $request,
        PembayaranService $service
    ) {
        try {
            $service->bayar(
                $request->validated()
            );

            return redirect()
                ->route('tagihan.index')
                ->with(
                    'success',
                    'Pembayaran berhasil disimpan.'
                );
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'message' => $e->getMessage(),
                ]);
        }
    }
}