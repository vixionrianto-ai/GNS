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
    /**
     * Service Pembayaran
     */
    protected PembayaranService $pembayaranService;
    protected WhatsAppService $whatsAppService;

    /**
     * Constructor
     */
    public function __construct(
        PembayaranService $pembayaranService,
        WhatsAppService $whatsAppService
    )
    {
        $this->pembayaranService = $pembayaranService;
        $this->whatsAppService = $whatsAppService;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    /**
     * Statistik pembayaran.
     */
    private function statistik(): array
    {
        $today = now()->toDateString();

        return [

            'totalHariIni' => Pembayaran::whereDate(
                'tanggal_bayar',
                $today
            )
            ->where('status', Pembayaran::STATUS_BERHASIL)
            ->where('metode', '!=', 'Saldo')
            ->sum('nominal'),

            'totalBulanIni' => Pembayaran::whereYear(
                'tanggal_bayar',
                now()->year
            )
            ->whereMonth(
                'tanggal_bayar',
                now()->month
            )
            ->where('status', Pembayaran::STATUS_BERHASIL)
            ->where('metode', '!=', 'Saldo')
            ->sum('nominal'),

            'jumlahTransaksi' => Pembayaran::count(),

            'jumlahBerhasil' => 
            Pembayaran::where(
                'status',
                Pembayaran::STATUS_BERHASIL
            )->count(),

            'jumlahPending' => 
            Pembayaran::where(
                'status',
                Pembayaran::STATUS_PENDING
            )->count(),

            'jumlahBatal' => 
            Pembayaran::where(
                'status',
                Pembayaran::STATUS_DIBATALKAN
            )->count(),

        ];
    }

    /**
     * Load relasi pembayaran.
     */
    private function loadPembayaran(
        Pembayaran $pembayaran
    ): Pembayaran {

        return $pembayaran->load([

            'tagihan.pelanggan.paket',

            'tagihan.pelanggan.router',

            'user',

        ]);

    }

    /**
     * Riwayat pembayaran.
     */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Data Pembayaran dengan Filter & Pencarian Nama Pelanggan / Invoice
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Statistik
        |--------------------------------------------------------------------------
        */

        $statistik = $this->statistik();

        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'pembayaran.index',
            array_merge(
                [
                    'pembayarans' => $pembayarans,
                ],
                $statistik
            )
        );
    }

    /**
     * Detail pembayaran.
     */
    public function show(Pembayaran $pembayaran)
    {
        $this->loadPembayaran($pembayaran);
        $waUrl = $this->whatsAppService->pembayaran($pembayaran);
        return view('pembayaran.show',[
            'pembayaran'=>$pembayaran,
            'waUrl'=>$waUrl,
        ]);
    }

    /**
     * Halaman invoice.
     */
    public function invoice(Pembayaran $pembayaran)
    {
        $this->loadPembayaran($pembayaran);
        $waUrl = $this->whatsAppService->pembayaran($pembayaran);
        return view('pembayaran.invoice',[
            'pembayaran'=>$pembayaran,
            'waUrl'=>$waUrl,
        ]);
    }

    /**
     * Download Invoice PDF.
     */
    public function pdf(Pembayaran $pembayaran)
    {
        $this->loadPembayaran($pembayaran);

        try {

            $pdf = Pdf::loadView(
                'pembayaran.pdf',
                compact('pembayaran')
            );

            $pdf->setPaper('A4', 'portrait');

            Log::info('Invoice PDF dibuat', [

                'invoice' => $pembayaran->invoice_no,

                'user_id' => auth()->id(),

            ]);

            return $pdf->download(
                'Invoice-' .
                $pembayaran->invoice_no .
                '.pdf'
            );

        } catch (\Throwable $e) {

            Log::error('Generate PDF gagal', [

                'invoice' => $pembayaran->invoice_no,

                'message' => $e->getMessage(),

            ]);

            return redirect()
                ->route(
                    'pembayaran.show',
                    $pembayaran
                )
                ->with(
                    'error',
                    'PDF gagal dibuat.'
                );

        }
        
    }

    /**
     * Public PDF untuk Fonnte.
     */
    public function publicPdf(string $token)
    {
        $pembayaran = Pembayaran::where(
            'public_token',
            $token
        )->first();

        if (! $pembayaran) {
            abort(404);
        }

        $this->loadPembayaran($pembayaran);

        try {

            $pdf = Pdf::loadView(
                'pembayaran.pdf',
                compact('pembayaran')
            );

            $pdf->setPaper('A4', 'portrait');

            Log::info('Public Invoice PDF', [

                'invoice' => $pembayaran->invoice_no,

                'ip' => request()->ip(),

            ]);

            return $pdf->stream(
                'Invoice-' .
                $pembayaran->invoice_no .
                '.pdf'
            );

        } catch (\Throwable $e) {

            Log::error('Public PDF gagal', [

                'invoice' => $pembayaran->invoice_no,

                'message' => $e->getMessage(),

            ]);

            abort(500);

        }
    }

    /**
     * Form pembayaran.
     */
    public function create(Tagihan $tagihan)
    {
        if ($tagihan->status === Tagihan::STATUS_LUNAS) {
            return redirect()
                ->route('tagihan.show', $tagihan)
                ->with(
                    'warning',
                    'Tagihan ini sudah lunas.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Total seluruh sisa tagihan pelanggan
        |--------------------------------------------------------------------------
        */
        $totalSisaTagihan = Tagihan::where(
            'pelanggan_id',
            $tagihan->pelanggan_id
        )
            ->where('sisa', '>', 0)
            ->sum('sisa');

        return view(
            'pembayaran.create',
            compact(
                'tagihan',
                'totalSisaTagihan'
            )
        );
    }

    /**
     * Simpan pembayaran.
     */
    public function store(
        PembayaranRequest $request
    )
    {
        try {

            $pembayaran = $this->pembayaranService->bayar(
                $request->validated()
            );

            Log::info('Pembayaran berhasil', [

                'invoice' => $pembayaran->invoice_no,

                'tagihan_id' => $pembayaran->tagihan_id,

                'user_id' => auth()->id(),

            ]);

            if ($pembayaran->wa_berhasil) {

                return redirect()
                    ->route(
                        'pembayaran.invoice',
                        $pembayaran
                    )
                    ->with(
                        'success',
                        'Pembayaran berhasil disimpan dan WhatsApp berhasil dikirim ke pelanggan.'
                    );

            }

            return redirect()
                ->route(
                    'pembayaran.invoice',
                    $pembayaran
                )
                ->with(
                    'warning',
                    'Pembayaran berhasil disimpan, tetapi WhatsApp gagal dikirim. Silakan gunakan tombol Kirim WhatsApp pada halaman invoice.'
                );

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
                ->with(
                    'error',
                    'Pembayaran gagal diproses. Silakan coba kembali.'
                );

        }
    }
}