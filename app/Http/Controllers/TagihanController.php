<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use App\Services\TagihanService;
use Illuminate\Http\Request;

class TagihanController extends Controller
{
    protected TagihanService $tagihanService;

    public function __construct(
        TagihanService $tagihanService
    )
    {
        $this->tagihanService = $tagihanService;
    }

    /**
     * Daftar Tagihan
     */
    public function index(Request $request)
    {
        $query = Tagihan::with([
            'pelanggan',
            'pelanggan.paket'
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('periode')) {
            $query->where('periode', $request->periode);
        }

        if ($request->filled('search')) {
            $query->whereHas('pelanggan', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        $tagihans = $query->latest()->paginate(20);

        return view('tagihan.index', compact('tagihans'));
    }

    /**
     * Generate tagihan harian
     */
    public function generate()
    {
        try {
            $jumlah = $this->tagihanService->generateHarian();

            return redirect()
                ->route('tagihan.index')
                ->with('success', "{$jumlah} tagihan berhasil dibuat.");
        } catch (\Exception $e) {
            return redirect()
                ->route('tagihan.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Detail Tagihan
     */
    public function show(Tagihan $tagihan)
    {
        $tagihan->load([
            'pelanggan',
            'pelanggan.paket',
            'pelanggan.router',
        ]);

        return view('tagihan.show', compact('tagihan'));
    }

    /**
     * Hapus Tagihan
     *
     * Invoice yang sudah memiliki riwayat pembayaran tidak boleh dihapus.
     * Menghapusnya akan menghilangkan jejak finansial dan, karena FK alokasi
     * menggunakan cascade, juga dapat menghapus data alokasi pembayaran.
     */
    public function destroy(Tagihan $tagihan)
    {
        try {
            if ($tagihan->pembayaran()->exists()) {
                return redirect()
                    ->route('tagihan.index')
                    ->with('error', 'Tagihan tidak dapat dihapus karena sudah memiliki riwayat pembayaran.');
            }

            $tagihan->delete();

            return redirect()
                ->route('tagihan.index')
                ->with('success', 'Tagihan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->route('tagihan.index')
                ->with('error', $e->getMessage());
        }
    }
}