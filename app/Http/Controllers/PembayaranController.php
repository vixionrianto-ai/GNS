<?php

namespace App\Http\Controllers;

use App\Http\Requests\PembayaranRequest;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Services\PembayaranService;
use App\Services\WhatsAppService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class PembayaranController extends Controller
{
    protected PembayaranService $pembayaranService;
    protected WhatsAppService $whatsAppService;

    public function __construct(
        PembayaranService $pembayaranService,
        WhatsAppService $whatsAppService
    )
    {
        $this->pembayaranService = $pembayaranService;
        $this->whatsAppService = $whatsAppService;
    }

    private function statistik(): array
    {
        $today = now()->toDateString();

        return [
            'totalHariIni' => Pembayaran::whereDate('tanggal_bayar', $today)
                ->where('status', Pembayaran::STATUS_BERHASIL)
                ->where('metode', '!=', 'Saldo')
                ->sum('nominal'),

            'totalBulanIni' => Pembayaran::whereYear('tanggal_bayar', now()->year)
                ->whereMonth('tanggal_bayar', now()->month)
                ->where('status', Pembayaran::STATUS_BERHASIL)
                ->where('metode', '!=', 'Saldo')
                ->sum('nominal'),

            'jumlahTransaksi' => Pembayaran::count(),
            'jumlahBerhasil' => Pembayaran::where('status', Pembayaran::STATUS_BERHASIL)->count(),
            'jumlahPending' => Pembayaran::where('status', Pembayaran::STATUS_PENDING)->count(),
            'jumlahBatal' => Pembayaran::where('status', Pembayaran::STATUS_DIBATALKAN)->count(),
        ];
    }

    private function loadPembayaran(Pembayaran $pembayaran): Pembayaran
    {
        return $pembayaran->load([
            'tagihan.pelanggan.paket',
            'tagihan.pelanggan.router',
            'user',
        ]);
    }

    public function index()
    {
        $search = request('search');
        $periode = request('periode');
        $status = request('status');
        $metode = request('metode');

        $pembayarans = Pembayaran::with([
            'tagihan.pelanggan',
            'user',
        ])
        ->when($search, function ($query, $search) {
            $query->where('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('tagihan.pelanggan', function ($q) use ($search) {
                      $q->where('nama', 'like', "%{$search}%");
                  });
        })
        ->when($periode, function ($query, $periode) {
            $query->whereHas('tagihan', function ($q) use ($periode) {
                $q->where('periode', 'like', "%{$periode}%");
            });
        })
        ->when($status, function ($query, $status) {
            $query->where('status', $status);
        })
        ->when($metode, function ($query, $metode) {
            $query->where('metode', $metode);
        })
        ->latest('id')
        ->paginate(15)
        ->withQueryString();

        $statistik = $this->statistik();

        return view(
            'pembayaran.index',
            array_merge(
                ['pembayarans' => $pembayarans],
                $statistik
            )
        );
    }

    public function show(Pembayaran $pembayaran)
    {
        $this->loadPembayaran($pembayaran);
        $waUrl = $this->whatsAppService->pembayaran($pembayaran);
        return view('pembayaran.show', [
            'pembayaran' => $pembayaran,
            'waUrl' => $waUrl,
        ]);
    }

    public function invoice(Pembayaran $pembayaran)
    {
        $this->loadPembayaran($pembayaran);
        $waUrl = $this->whatsAppService->pembayaran($pembayaran);
        return view('pembayaran.invoice', [
            'pembayaran' => $pembayaran,
            'waUrl' => $waUrl,
        ]);
    }

    public function pdf(Pembayaran $pembayaran)
    {
        $this->loadPembayaran($pembayaran);

        try {
            $pdf = Pdf::loadView('pembayaran.pdf', compact('pembayaran'));
            $pdf->setPaper('A4', 'portrait');

            Log::info('Invoice PDF dibuat', [
                'invoice' => $pembayaran->invoice_no,
                'user_id' => auth()->id(),
            ]);

            return $pdf->download('Invoice-' . $pembayaran->invoice_no . '.pdf');
        } catch (\Throwable $e) {
            Log::error('Generate PDF gagal', [
                'invoice' => $pembayaran->invoice_no,
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('pembayaran.show', $pembayaran)
                ->with('error', 'PDF gagal dibuat.');
        }
    }

    public function publicPdf(string $token)
    {
        $pembayaran = Pembayaran::where('public_token', $token)->first();

        if (! $pembayaran) {
            abort(404);
        }

        $this->loadPembayaran($pembayaran);

        try {
            $pdf = Pdf::loadView('pembayaran.pdf', compact('pembayaran'));
            $pdf->setPaper('A4', 'portrait');

            Log::info('Public Invoice PDF', [
                'invoice' => $pembayaran->invoice_no,
                'ip' => request()->ip(),
            ]);

            return $pdf->stream('Invoice-' . $pembayaran->invoice_no . '.pdf');
        } catch (\Throwable $e) {
            Log::error('Public PDF gagal', [
                'invoice' => $pembayaran->invoice_no,
                'message' => $e->getMessage(),
            ]);

            abort(500);
        }
    }

    public function create(Tagihan $tagihan)
    {
        if ($tagihan->status === Tagihan::STATUS_LUNAS) {
            return redirect()
                ->route('tagihan.show', $tagihan)
                ->with('warning', 'Tagihan ini sudah lunas.');
        }

        $totalSisaTagihan = Tagihan::where('pelanggan_id', $tagihan->pelanggan_id)
            ->where('sisa', '>', 0)
            ->sum('sisa');

        return view('pembayaran.create', compact('tagihan', 'totalSisaTagihan'));
    }

    public function store(PembayaranRequest $request)
    {
        try {
            $pembayaran = $this->pembayaranService->bayar($request->validated());

            Log::info('Pembayaran berhasil', [
                'invoice' => $pembayaran->invoice_no,
                'tagihan_id' => $pembayaran->tagihan_id,
                'user_id' => auth()->id(),
            ]);

            if ($pembayaran->wa_berhasil) {
                return redirect()
                    ->route('pembayaran.invoice', $pembayaran)
                    ->with('success', 'Pembayaran berhasil disimpan dan WhatsApp berhasil dikirim ke pelanggan.');
            }

            return redirect()
                ->route('pembayaran.invoice', $pembayaran)
                ->with('warning', 'Pembayaran berhasil disimpan, tetapi WhatsApp gagal dikirim. Silakan gunakan tombol Kirim WhatsApp pada halaman invoice.');
        } catch (\Throwable $e) {
            Log::error('Pembayaran gagal', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Pembayaran gagal diproses. Silakan coba kembali.');
        }
    }

    /**
     * Batalkan pembayaran melalui service agar seluruh alokasi FIFO,
     * saldo dan status tagihan dikembalikan secara konsisten.
     */
    public function destroy(Pembayaran $pembayaran)
    {
        try {
            $pembayaran = $this->pembayaranService->batalkan($pembayaran);

            return redirect()
                ->route('pembayaran.show', $pembayaran)
                ->with('success', 'Pembayaran berhasil dibatalkan dan alokasi/saldo telah dikembalikan.');
        } catch (\Throwable $e) {
            Log::error('Pembatalan pembayaran gagal', [
                'pembayaran_id' => $pembayaran->id,
                'invoice' => $pembayaran->invoice_no,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Pembayaran gagal dibatalkan: ' . $e->getMessage());
        }
    }
}
