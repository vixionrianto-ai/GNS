<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use App\Models\Pelanggan;
use App\Services\TagihanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsAppService;

class TagihanController extends Controller
{
    /**
     * Service Tagihan.
     */
    protected TagihanService $tagihanService;

    /**
     * Constructor.
     */
    public function __construct(
        TagihanService $tagihanService
    ) {
        $this->tagihanService = $tagihanService;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    /**
     * Statistik Dashboard Tagihan.
     */
    private function statistik(): array
    {
        return [
            'totalTagihan' => Tagihan::count(),

            'totalLunas' => Tagihan::where(
                'status',
                Tagihan::STATUS_LUNAS
            )->count(),

            'totalBelumBayar' => Tagihan::where(
                'status',
                Tagihan::STATUS_BELUM_BAYAR
            )->count(),

            'totalJatuhTempo' => Tagihan::where(
                'status',
                Tagihan::STATUS_JATUH_TEMPO
            )->count(),
        ];
    }

    /**
     * Terapkan filter.
     */
    private function filter(
        $query,
        Request $request
    )
    {
        if ($request->filled('status') && $request->status !== 'Semua') {
            $query->where(
                'status',
                $request->status
            );
        }

        if ($request->filled('periode') && $request->periode !== 'Pilih Periode') {
            $query->where(
                'periode',
                'like',
                '%' . $request->periode . '%'
            );
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->where('invoice_no', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('pelanggan', function ($pelangganQuery) use ($searchTerm) {
                      $pelangganQuery->where('nama', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        return $query;
    }

    /**
     * Daftar Tagihan.
     */
    public function index(Request $request)
    {
        $this->tagihanService->updateStatusOtomatis();

        $query = Tagihan::with([
            'pelanggan',
            'pelanggan.paket',
        ]);

        $query = $this->filter(
            $query,
            $request
        );

        $tagihans = $query
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        $statistik = $this->statistik();

        // Ambil daftar pelanggan aktif khusus untuk dropdown pilihan di modal generate periode
        $pelanggans = Pelanggan::where('status', 'Aktif')->orderBy('nama', 'asc')->get();

        return view(
            'tagihan.index',
            array_merge(
                [
                    'tagihans'   => $tagihans,
                    'pelanggans' => $pelanggans,
                ],
                $statistik
            )
        );
    }

    /**
     * Generate tagihan harian.
     */
    public function generate()
    {
        try {
            $hasil = $this->tagihanService->generateHarian();

            Log::info('Generate tagihan harian', $hasil);

            return redirect()
                ->route('tagihan.index')
                ->with(
                    'success',
                    "Berhasil : {$hasil['berhasil']} | Sudah Ada : {$hasil['sudah_ada']} | Gagal : {$hasil['gagal']}"
                );
        } catch (\Throwable $e) {
            Log::error('Generate harian gagal', [
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('tagihan.index')
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /**
     * Generate seluruh pelanggan aktif.
     */
    public function generateSemua()
    {
        try {
            $hasil = $this->tagihanService->generateSemua();

            Log::info('Generate semua tagihan', $hasil);

            return redirect()
                ->route('tagihan.index')
                ->with(
                    'success',
                    "Berhasil : {$hasil['berhasil']} | Sudah Ada : {$hasil['sudah_ada']} | Gagal : {$hasil['gagal']}"
                );
        } catch (\Throwable $e) {
            Log::error('Generate semua gagal', [
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('tagihan.index')
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /**
     * Generate berdasarkan periode (Mendukung Massal atau 1 Pelanggan Tertentu via Service).
     */
    public function generatePeriode(Request $request)
    {
        $request->validate([
            'periode'      => ['required', 'date_format:Y-m'],
            'pelanggan_id' => ['nullable', 'exists:pelanggans,id'],
        ]);

        try {
            $carbonPeriode = Carbon::createFromFormat('Y-m', $request->periode);

            // Jika user memilih pelanggan spesifik dari modal
            if ($request->filled('pelanggan_id')) {
                $pelanggan = Pelanggan::with('paket')->findOrFail($request->pelanggan_id);
                
                // Memanfaatkan fungsi generateUntukPeriode bawaan TagihanService agar logika denda, saldo, dan WhatsApp tetap utuh
                $tagihan = $this->tagihanService->generateUntukPeriode($pelanggan, $carbonPeriode);

                return redirect()
                    ->route('tagihan.index')
                    ->with(
                        'success',
                        "Berhasil generate tagihan periode {$request->periode} untuk pelanggan {$pelanggan->nama}."
                    );
            } else {
                // Jika dikosongkan, jalankan generate periode massal standar bawaan service
                $hasil = $this->tagihanService->generatePeriode($carbonPeriode);

                return redirect()
                    ->route('tagihan.index')
                    ->with(
                        'success',
                        "Generate periode {$hasil['periode']} selesai. Berhasil {$hasil['berhasil']}, Sudah Ada {$hasil['sudah_ada']}, Gagal {$hasil['gagal']}."
                    );
            }
        } catch (\Throwable $e) {
            Log::error('Generate periode gagal', [
                'periode' => $request->periode,
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('tagihan.index')
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /**
     * Detail Tagihan.
     */
    public function show(
        Tagihan $tagihan,
        WhatsAppService $whatsapp
    )
    {
        $tagihan->load([
            'pelanggan',
            'pelanggan.paket',
            'pelanggan.router',
            'pembayaran',
        ]);

        if (
            $tagihan->status === Tagihan::STATUS_LUNAS &&
            $tagihan->pembayaran
        ) {
            $waUrl = $whatsapp->pembayaran(
                $tagihan->pembayaran
            );
        } else {
            $waUrl = $whatsapp->tagihan($tagihan);
        }

        return view(
            'tagihan.show',
            compact(
                'tagihan',
                'waUrl'
            )
        );
    }

    /**
     * Kirim WhatsApp Tagihan.
     */
    public function sendWhatsapp(
        Tagihan $tagihan,
        WhatsAppService $whatsapp
    )
    {
        try {
            if (
                $tagihan->status === Tagihan::STATUS_LUNAS &&
                $tagihan->pembayaran
            ) {
                $waUrl = $whatsapp->pembayaran(
                    $tagihan->pembayaran
                );
            } else {
                $waUrl = $whatsapp->tagihan(
                    $tagihan
                );
            }

            return redirect()->away($waUrl);
        } catch (\Throwable $e) {
            Log::error('WhatsApp Tagihan gagal', [
                'invoice' => $tagihan->invoice_no,
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('tagihan.index')
                ->with(
                    'error',
                    'Gagal membuka WhatsApp.'
                );
        }
    }

    /**
     * Hapus Tagihan.
     */
    public function destroy(Tagihan $tagihan)
    {
        try {
            $invoice = $tagihan->invoice_no;

            DB::transaction(function () use ($tagihan) {
                if ($tagihan->pembayaran) {
                    $tagihan->pembayaran()->delete();
                }

                $tagihan->delete();
            });

            Log::info('Tagihan dihapus', [
                'invoice' => $invoice,
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->route('tagihan.index')
                ->with(
                    'success',
                    'Tagihan berhasil dihapus.'
                );
        } catch (\Throwable $e) {
            Log::error('Hapus tagihan gagal', [
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('tagihan.index')
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /**
     * Maintenance harian.
     */
    public function maintenance()
    {
        try {
            $this->tagihanService->maintenanceHarian();

            return redirect()
                ->route('tagihan.index')
                ->with(
                    'success',
                    'Maintenance harian selesai dijalankan.'
                );
        } catch (\Throwable $e) {
            Log::error('Maintenance gagal', [
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('tagihan.index')
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }
}