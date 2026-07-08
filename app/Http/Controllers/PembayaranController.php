<?php

namespace App\Http\Controllers;

use App\Http\Requests\PembayaranRequest;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Services\PembayaranService;
use Barryvdh\DomPDF\Facade\Pdf;

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
     * Halaman Invoice
     */
    public function invoice(Pembayaran $pembayaran)
    {
        $pembayaran->load([
            'tagihan.pelanggan.paket',
            'tagihan.pelanggan.router',
            'user',
        ]);

        return view(
            'pembayaran.invoice',
            compact('pembayaran')
        );
    }

/**
 * Download PDF
 */
public function pdf(Pembayaran $pembayaran)
{
    $pembayaran->load([
        'tagihan.pelanggan.paket',
        'tagihan.pelanggan.router',
        'user',
    ]);

    $pdf = Pdf::loadView(
        'pembayaran.pdf',
        compact('pembayaran')
    );

    $pdf->setPaper('A4', 'portrait');

    return $pdf->download(
        'Invoice-'.$pembayaran->invoice_no.'.pdf'
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

            $pembayaran = $service->bayar(
                $request->validated()
            );

            return redirect()->route(
                'pembayaran.invoice',
                $pembayaran
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