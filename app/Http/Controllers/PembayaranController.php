<?php

namespace App\Http\Controllers;

use App\Http\Requests\PembayaranRequest;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Services\PembayaranService;
use Barryvdh\DomPDF\Facade\Pdf;

class PembayaranController extends Controller
{
    public function index()
    {
        $pembayarans = Pembayaran::with([
            'tagihan.pelanggan',
            'user',
        ])->latest()->paginate(15);

        return view('pembayaran.index', compact('pembayarans'));
    }

    public function show(Pembayaran $pembayaran)
    {
        $pembayaran->load([
            'tagihan.pelanggan.paket',
            'tagihan.pelanggan.router',
            'user',
        ]);

        return view('pembayaran.show', compact('pembayaran'));
    }

    public function invoice(Pembayaran $pembayaran)
    {
        $pembayaran->load([
            'tagihan.pelanggan.paket',
            'tagihan.pelanggan.router',
            'user',
        ]);

        return view('pembayaran.invoice', compact('pembayaran'));
    }

    public function pdf(Pembayaran $pembayaran)
    {
        $pembayaran->load([
            'tagihan.pelanggan.paket',
            'tagihan.pelanggan.router',
            'user',
        ]);

        $pdf = Pdf::loadView('pembayaran.pdf', compact('pembayaran'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('Invoice-' . $pembayaran->invoice_no . '.pdf');
    }

    /**
     * PDF invoice publik menggunakan token acak.
     * Tidak memakai middleware auth agar link WhatsApp dapat dibuka pelanggan.
     */
    public function publicPdf(string $token)
    {
        $pembayaran = Pembayaran::where('public_token', $token)
            ->where('status', Pembayaran::STATUS_BERHASIL)
            ->firstOrFail();

        $pembayaran->load([
            'tagihan.pelanggan.paket',
            'tagihan.pelanggan.router',
            'user',
        ]);

        $pdf = Pdf::loadView('pembayaran.pdf', compact('pembayaran'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('Invoice-' . $pembayaran->invoice_no . '.pdf');
    }

    public function create(Tagihan $tagihan)
    {
        return view('pembayaran.create', compact('tagihan'));
    }

    public function store(
        PembayaranRequest $request,
        PembayaranService $service
    ) {
        try {
            $pembayaran = $service->bayar($request->validated());

            return redirect()->route('pembayaran.invoice', $pembayaran);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['message' => $e->getMessage()]);
        }
    }

    public function cancel(
        Pembayaran $pembayaran,
        PembayaranService $service
    ) {
        try {
            $service->batalkan($pembayaran);

            return redirect()
                ->route('pembayaran.show', $pembayaran)
                ->with('success', 'Pembayaran berhasil dibatalkan dan status tagihan dihitung ulang.');
        } catch (\Throwable $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }
    }
}
