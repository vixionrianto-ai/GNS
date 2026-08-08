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
    protected TagihanService $tagihanService;

    public function __construct(TagihanService $tagihanService)
    {
        $this->tagihanService = $tagihanService;
    }

    private function statistik(): array
    {
        return [
            'totalTagihan' => Tagihan::count(),
            'totalLunas' => Tagihan::where('status', Tagihan::STATUS_LUNAS)->count(),
            'totalBelumBayar' => Tagihan::where('status', Tagihan::STATUS_BELUM_BAYAR)->count(),
            'totalJatuhTempo' => Tagihan::where('status', Tagihan::STATUS_JATUH_TEMPO)->count(),
        ];
    }

    private function filter($query, Request $request)
    {
        if ($request->filled('status') && $request->status !== 'Semua') {
            $query->where('status', $request->status);
        }

        if ($request->filled('periode') && $request->periode !== 'Pilih Periode') {
            $query->where('periode', 'like', '%' . $request->periode . '%');
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

    public function index(Request $request)
    {
        $this->tagihanService->updateStatusOtomatis();

        $query = Tagihan::with(['pelanggan', 'pelanggan.paket']);
        $query = $this->filter($query, $request);
        $tagihans = $query->latest('id')->paginate(50)->withQueryString();
        $statistik = $this->statistik();
        $pelanggans = Pelanggan::where('status', 'Aktif')->orderBy('nama', 'asc')->get();

        return view('tagihan.index', array_merge([
            'tagihans' => $tagihans,
            'pelanggans' => $pelanggans,
        ], $statistik));
    }

    public function generate()
    {
        try {
            $hasil = $this->tagihanService->generateHarian();
            Log::info('Generate tagihan harian', $hasil);
            return redirect()->route('tagihan.index')->with('success', "Berhasil : {$hasil['berhasil']} | Sudah Ada : {$hasil['sudah_ada']} | Gagal : {$hasil['gagal']}");
        } catch (\Throwable $e) {
            Log::error('Generate harian gagal', ['message' => $e->getMessage()]);
            return redirect()->route('tagihan.index')->with('error', $e->getMessage());
        }
    }

    public function generateSemua()
    {
        try {
            $hasil = $this->tagihanService->generateSemua();
            Log::info('Generate semua tagihan', $hasil);
            return redirect()->route('tagihan.index')->with('success', "Berhasil : {$hasil['berhasil']} | Sudah Ada : {$hasil['sudah_ada']} | Gagal : {$hasil['gagal']}");
        } catch (\Throwable $e) {
            Log::error('Generate semua gagal', ['message' => $e->getMessage()]);
            return redirect()->route('tagihan.index')->with('error', $e->getMessage());
        }
    }

    public function generatePeriode(Request $request)
    {
        $request->validate([
            'periode' => ['required', 'date_format:Y-m'],
            'pelanggan_id' => ['nullable', 'exists:pelanggans,id'],
        ]);

        try {
            $carbonPeriode = Carbon::createFromFormat('Y-m', $request->periode);

            if ($request->filled('pelanggan_id')) {
                $pelanggan = Pelanggan::with('paket')->findOrFail($request->pelanggan_id);
                $this->tagihanService->generateUntukPeriode($pelanggan, $carbonPeriode);

                return redirect()->route('tagihan.index')->with('success', "Berhasil generate tagihan periode {$request->periode} untuk pelanggan {$pelanggan->nama}.");
            }

            $hasil = $this->tagihanService->generatePeriode($carbonPeriode);
            return redirect()->route('tagihan.index')->with('success', "Generate periode {$hasil['periode']} selesai. Berhasil {$hasil['berhasil']}, Sudah Ada {$hasil['sudah_ada']}, Gagal {$hasil['gagal']}.");
        } catch (\Throwable $e) {
            Log::error('Generate periode gagal', [
                'periode' => $request->periode,
                'message' => $e->getMessage(),
            ]);
            return redirect()->route('tagihan.index')->with('error', $e->getMessage());
        }
    }

    public function show(Tagihan $tagihan, WhatsAppService $whatsapp)
    {
        $tagihan->load(['pelanggan', 'pelanggan.paket', 'pelanggan.router', 'pembayaran']);

        if ($tagihan->status === Tagihan::STATUS_LUNAS && $tagihan->pembayaran) {
            $waUrl = $whatsapp->pembayaran($tagihan->pembayaran);
        } else {
            $waUrl = $whatsapp->tagihan($tagihan);
        }

        return view('tagihan.show', compact('tagihan', 'waUrl'));
    }

    public function sendWhatsapp(Tagihan $tagihan, WhatsAppService $whatsapp)
    {
        try {
            if ($tagihan->status === Tagihan::STATUS_LUNAS && $tagihan->pembayaran) {
                $waUrl = $whatsapp->pembayaran($tagihan->pembayaran);
            } else {
                $waUrl = $whatsapp->tagihan($tagihan);
            }

            return redirect()->away($waUrl);
        } catch (\Throwable $e) {
            Log::error('WhatsApp Tagihan gagal', [
                'invoice' => $tagihan->invoice_no,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('tagihan.index')->with('error', 'Gagal membuka WhatsApp.');
        }
    }

    public function destroy(Tagihan $tagihan)
    {
        try {
            $tagihan->loadMissing(['pembayaran', 'alokasi', 'saldoUsages']);

            if (
                $tagihan->pembayaran()->exists() ||
                $tagihan->alokasi()->exists() ||
                $tagihan->saldoUsages()->exists()
            ) {
                return redirect()
                    ->route('tagihan.index')
                    ->with('error', 'Tagihan tidak dapat dihapus karena sudah memiliki histori pembayaran, alokasi, atau penggunaan saldo.');
            }

            $invoice = $tagihan->invoice_no;

            DB::transaction(function () use ($tagihan) {
                $tagihan->delete();
            });

            Log::info('Tagihan dihapus', [
                'invoice' => $invoice,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('tagihan.index')->with('success', 'Tagihan berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('Hapus tagihan gagal', ['message' => $e->getMessage()]);
            return redirect()->route('tagihan.index')->with('error', $e->getMessage());
        }
    }

    public function maintenance()
    {
        try {
            $this->tagihanService->maintenanceHarian();
            return redirect()->route('tagihan.index')->with('success', 'Maintenance harian selesai dijalankan.');
        } catch (\Throwable $e) {
            Log::error('Maintenance gagal', ['message' => $e->getMessage()]);
            return redirect()->route('tagihan.index')->with('error', $e->getMessage());
        }
    }
}
