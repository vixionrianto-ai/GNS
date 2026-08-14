<?php

namespace App\Http\Controllers;

use App\Models\AlokasiPembayaran;
use App\Models\Pembayaran;
use App\Models\Pelanggan;
use App\Models\SaldoPelanggan;
use App\Models\SaldoUsage;
use App\Models\Tagihan;
use App\Models\WhatsAppLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class SuperAdminController extends Controller
{
    /**
     * Halaman Reset Data.
     */
    public function index()
    {
        return view('superadmin.reset');
    }

    /**
     * Reset data yang dipilih.
     *
     * Data yang tidak dicentang tidak disentuh.
     * Relasi anak yang terkait ikut dibersihkan agar foreign key tetap aman.
     */
    public function reset(Request $request)
    {
        $validated = $request->validate([
            'pelanggan' => ['nullable', 'boolean'],
            'tagihan' => ['nullable', 'boolean'],
            'pembayaran' => ['nullable', 'boolean'],
            'confirm' => ['required', 'in:RESET GNS'],
        ], [
            'confirm.in' => 'Konfirmasi harus diketik tepat: RESET GNS.',
        ]);

        $resetPelanggan = (bool) ($validated['pelanggan'] ?? false);
        $resetTagihan = (bool) ($validated['tagihan'] ?? false);
        $resetPembayaran = (bool) ($validated['pembayaran'] ?? false);

        if (! $resetPelanggan && ! $resetTagihan && ! $resetPembayaran) {
            return back()
                ->withErrors(['reset' => 'Pilih minimal satu jenis data yang akan direset.'])
                ->withInput();
        }

        try {
            $counts = DB::transaction(function () use (
                $resetPelanggan,
                $resetTagihan,
                $resetPembayaran
            ) {
                $counts = [
                    'pelanggan' => 0,
                    'tagihan' => 0,
                    'pembayaran' => 0,
                ];

                $tagihanIds = collect();
                $pembayaranIds = collect();

                // Jika pelanggan direset, seluruh transaksi milik pelanggan tersebut
                // ikut dibersihkan agar data tidak menjadi yatim.
                if ($resetPelanggan) {
                    $pelangganIds = Pelanggan::query()->pluck('id');
                    $counts['pelanggan'] = $pelangganIds->count();

                    if ($pelangganIds->isNotEmpty()) {
                        WhatsAppLog::query()
                            ->whereIn('pelanggan_id', $pelangganIds)
                            ->delete();
                    }

                    $tagihanIds = Tagihan::query()
                        ->whereIn('pelanggan_id', $pelangganIds)
                        ->pluck('id');

                    $pembayaranIds = Pembayaran::query()
                        ->whereIn('tagihan_id', $tagihanIds)
                        ->pluck('id');

                    $this->deleteTransactions(
                        $tagihanIds,
                        $pembayaranIds
                    );

                    $saldoIds = SaldoPelanggan::query()
                        ->whereIn('pelanggan_id', $pelangganIds)
                        ->pluck('id');

                    if ($saldoIds->isNotEmpty()) {
                        SaldoUsage::query()
                            ->whereIn('saldo_pelanggan_id', $saldoIds)
                            ->delete();
                    }

                    SaldoPelanggan::query()
                        ->whereIn('pelanggan_id', $pelangganIds)
                        ->delete();

                    Pelanggan::query()
                        ->whereIn('id', $pelangganIds)
                        ->delete();

                    $counts['tagihan'] = $tagihanIds->count();
                    $counts['pembayaran'] = $pembayaranIds->count();
                } else {
                    // Reset tagihan: pembayaran dan alokasi yang melekat pada tagihan
                    // ikut dihapus agar status tagihan tidak menyisakan data lama.
                    if ($resetTagihan) {
                        $tagihanIds = Tagihan::query()->pluck('id');
                        $pembayaranIds = Pembayaran::query()
                            ->whereIn('tagihan_id', $tagihanIds)
                            ->pluck('id');

                        $counts['tagihan'] = $tagihanIds->count();
                        $counts['pembayaran'] = $pembayaranIds->count();

                        $this->deleteTransactions(
                            $tagihanIds,
                            $pembayaranIds
                        );

                        Tagihan::query()
                            ->whereIn('id', $tagihanIds)
                            ->delete();
                    }

                    // Reset pembayaran saja: tagihan dipertahankan dan statusnya
                    // dihitung ulang setelah pembayaran/alokasi dihapus.
                    if ($resetPembayaran) {
                        $payments = Pembayaran::query()->get(['id', 'tagihan_id']);
                        $pembayaranIds = $payments->pluck('id');
                        $affectedTagihanIds = $payments
                            ->pluck('tagihan_id')
                            ->filter()
                            ->unique();

                        if ($pembayaranIds->isNotEmpty()) {
                            AlokasiPembayaran::query()
                                ->whereIn('pembayaran_id', $pembayaranIds)
                                ->delete();

                            Pembayaran::query()
                                ->whereIn('id', $pembayaranIds)
                                ->delete();
                        }

                        $counts['pembayaran'] = $pembayaranIds->count();

                        Tagihan::query()
                            ->whereIn('id', $affectedTagihanIds)
                            ->get()
                            ->each(fn (Tagihan $tagihan) => $tagihan->refreshStatus());
                    }
                }

                return $counts;
            });
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors([
                    'reset' => 'Reset gagal. Tidak ada perubahan yang disimpan. Periksa log Laravel untuk detailnya.',
                ])
                ->withInput();
        }

        $parts = [];

        if ($resetPelanggan) {
            $parts[] = $counts['pelanggan'] . ' pelanggan';
        }

        if ($resetTagihan || $resetPelanggan) {
            $parts[] = $counts['tagihan'] . ' tagihan';
        }

        if ($resetPembayaran || $resetTagihan || $resetPelanggan) {
            $parts[] = $counts['pembayaran'] . ' pembayaran';
        }

        return back()->with(
            'success',
            'Reset berhasil: ' . implode(', ', $parts) . ' dihapus.'
        );
    }

    /**
     * Hapus data transaksi dan relasi turunannya.
     */
    private function deleteTransactions($tagihanIds, $pembayaranIds): void
    {
        if ($tagihanIds->isNotEmpty()) {
            WhatsAppLog::query()
                ->whereIn('tagihan_id', $tagihanIds)
                ->delete();

            SaldoUsage::query()
                ->whereIn('tagihan_id', $tagihanIds)
                ->delete();

            AlokasiPembayaran::query()
                ->whereIn('tagihan_id', $tagihanIds)
                ->delete();
        }

        if ($pembayaranIds->isNotEmpty()) {
            AlokasiPembayaran::query()
                ->whereIn('pembayaran_id', $pembayaranIds)
                ->delete();

            Pembayaran::query()
                ->whereIn('id', $pembayaranIds)
                ->delete();
        }
    }
}
