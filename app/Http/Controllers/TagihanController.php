<?php

namespace App\Http\Controllers;

use App\Models\AlokasiPembayaran;
use App\Models\Tagihan;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\SaldoPelanggan;
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

    /**
     * Cari pembayaran berhasil yang benar-benar mengalokasikan dana ke tagihan.
     * Penting untuk FIFO karena relasi Tagihan->pembayaran hanya mewakili
     * pembayaran lama/asal dan tidak selalu merupakan pembayaran yang melunasi
     * tagihan tersebut.
     */
    private function pembayaranUntukTagihan(Tagihan $tagihan): ?Pembayaran
    {
        $alokasi = AlokasiPembayaran::with('pembayaran')
            ->where('tagihan_id', $tagihan->id)
            ->whereHas('pembayaran', function ($query) {
                $query->where('status', Pembayaran::STATUS_BERHASIL);
            })
            ->latest('id')
            ->first();

        return $alokasi?->pembayaran;
    }

    public function index(Request $request)
    {
        $this->tagihanService->updateStatusOtomatis();

        $query = Tagihan::with(['pelanggan', 'pelanggan.paket'])
            ->withCount([
                'pembayaran as pembayaran_count' => function ($query) {
                    $query->where('metode', '!=', 'Saldo');
                },
                'alokasi',
                'saldoUsages',
            ]);
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

        if ($tagihan->status === Tagihan::STATUS_LUNAS) {
            $pembayaran = $this->pembayaranUntukTagihan($tagihan);
            $waUrl = $pembayaran
                ? $whatsapp->pembayaran($pembayaran)
                : $whatsapp->tagihan($tagihan);
        } else {
            $waUrl = $whatsapp->tagihan($tagihan);
        }

        return view('tagihan.show', compact('tagihan', 'waUrl'));
    }

    public function sendWhatsapp(Tagihan $tagihan, WhatsAppService $whatsapp)
    {
        try {
            if ($tagihan->status === Tagihan::STATUS_LUNAS) {
                $pembayaran = $this->pembayaranUntukTagihan($tagihan);
                $waUrl = $pembayaran
                    ? $whatsapp->pembayaran($pembayaran)
                    : $whatsapp->tagihan($tagihan);
            } else {
                $waUrl = $whatsapp->tagihan($tagihan);
            }

            if (! $waUrl) {
                return redirect()->route('tagihan.index')->with('error', 'Nomor WhatsApp pelanggan tidak tersedia.');
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
                    ->with('error', 'Tagihan tidak dapat dihapus karena sudah memiliki histori pembayaran, alokasi, atau penggunaan saldo. Gunakan Batalkan Alokasi & Hapus untuk tagihan yang hanya memiliki alokasi/penggunaan saldo.');
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

    /**
     * Batalkan alokasi/penggunaan saldo untuk tagihan yang tidak memiliki
     * pembayaran manual. Pembayaran otomatis metode Saldo boleh dibatalkan
     * bersama tagihan; pembayaran normal tetap dipertahankan.
     */
    public function destroyWithRollback(Tagihan $tagihan)
    {
        try {
            $tagihan->loadMissing(['pembayaran', 'alokasi.pembayaran', 'saldoUsages']);

            $pembayaranLangsung = $tagihan->pembayaran;
            $pembayaranSaldoOtomatis = $pembayaranLangsung
                && $pembayaranLangsung->metode === 'Saldo'
                && $pembayaranLangsung->status === Pembayaran::STATUS_BERHASIL;

            if ($pembayaranLangsung && ! $pembayaranSaldoOtomatis) {
                return redirect()
                    ->route('tagihan.index')
                    ->with('error', 'Tagihan memiliki pembayaran langsung. Pembayaran harus dibatalkan/dikelola dari menu Pembayaran terlebih dahulu.');
            }

            if (! $tagihan->alokasi()->exists() && ! $tagihan->saldoUsages()->exists()) {
                return redirect()
                    ->route('tagihan.index')
                    ->with('error', 'Tagihan ini tidak memiliki alokasi atau penggunaan saldo untuk dibatalkan.');
            }

            if ($pembayaranSaldoOtomatis) {
                $alokasiPembayaranSaldo = $tagihan->alokasi()
                    ->where('pembayaran_id', $pembayaranLangsung->id)
                    ->get();

                $alokasiPembayaranLain = $tagihan->alokasi()
                    ->where('pembayaran_id', '!=', $pembayaranLangsung->id)
                    ->exists();

                $alokasiDiPembayaranLain = AlokasiPembayaran::where('pembayaran_id', $pembayaranLangsung->id)
                    ->where('tagihan_id', '!=', $tagihan->id)
                    ->exists();

                if ($alokasiPembayaranLain || $alokasiDiPembayaranLain || $alokasiPembayaranSaldo->isEmpty()) {
                    return redirect()
                        ->route('tagihan.index')
                        ->with('error', 'Pembayaran otomatis Saldo pada tagihan ini memiliki struktur alokasi yang tidak aman untuk dihapus otomatis. Kelola dari menu Pembayaran terlebih dahulu.');
                }
            }

            $invoice = $tagihan->invoice_no;
            $pelangganId = $tagihan->pelanggan_id;

            $hasil = DB::transaction(function () use ($tagihan, $pelangganId, $pembayaranLangsung, $pembayaranSaldoOtomatis) {
                $saldo = SaldoPelanggan::milik($pelangganId);
                $saldo = SaldoPelanggan::whereKey($saldo->id)->lockForUpdate()->firstOrFail();

                $saldoDikembalikan = 0.0;

                $saldoUsages = $tagihan->saldoUsages()->lockForUpdate()->get();
                foreach ($saldoUsages as $usage) {
                    $jumlah = (float) $usage->jumlah;
                    if ($jumlah > 0) {
                        $saldo->tambah(
                            $jumlah,
                            'Pengembalian saldo dari pembatalan tagihan ' . $tagihan->invoice_no
                        );
                        $saldoDikembalikan += $jumlah;
                    }

                    $usage->delete();
                }

                $alokasiSaldoUsage = $saldoUsages->sum(fn ($usage) => (float) $usage->jumlah);
                $alokasi = $tagihan->alokasi()->with('pembayaran')->lockForUpdate()->get();

                foreach ($alokasi as $item) {
                    $nominal = (float) $item->nominal;
                    $metodeSaldo = $item->pembayaran?->metode === 'Saldo';

                    if ($metodeSaldo && $alokasiSaldoUsage >= $nominal) {
                        $alokasiSaldoUsage -= $nominal;
                    } elseif (! $pembayaranSaldoOtomatis && $nominal > 0) {
                        $saldo->tambah(
                            $nominal,
                            'Pengembalian alokasi dari pembatalan tagihan ' . $tagihan->invoice_no
                        );
                        $saldoDikembalikan += $nominal;
                    }

                    $item->delete();
                }

                if ($pembayaranSaldoOtomatis && $pembayaranLangsung) {
                    $pembayaranLangsung->delete();
                }

                $tagihan->delete();

                return $saldoDikembalikan;
            });

            Log::warning('Tagihan dihapus setelah pembatalan alokasi/saldo', [
                'invoice' => $invoice,
                'pelanggan_id' => $pelangganId,
                'saldo_dikembalikan' => $hasil,
                'pembayaran_saldo_otomatis' => $pembayaranSaldoOtomatis,
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->route('tagihan.index')
                ->with('success', "Tagihan {$invoice} berhasil dihapus. Alokasi/penggunaan saldo dibatalkan dan saldo pelanggan dikembalikan Rp " . number_format($hasil, 0, ',', '.') . '.');
        } catch (\Throwable $e) {
            Log::error('Batalkan alokasi dan hapus tagihan gagal', [
                'tagihan_id' => $tagihan->id,
                'invoice' => $tagihan->invoice_no,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('tagihan.index')->with('error', 'Gagal membatalkan alokasi dan menghapus tagihan: ' . $e->getMessage());
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
