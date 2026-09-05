<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Tagihan;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TagihanService
{
    /**
     * Generate nomor invoice.
     * Contoh: INV-202607-00001
     */
    public function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ym') . '-';

        $last = Tagihan::where('invoice_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        if (!$last) {
            return $prefix . '00001';
        }

        $lastNumber = (int) substr($last->invoice_no, -5);

        return $prefix . str_pad(
            $lastNumber + 1,
            5,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Generate tagihan untuk satu pelanggan.
     *
     * Invoice number is protected by a small retry loop because the
     * application-level number lookup cannot by itself serialize invoice
     * generation across different customers.
     */
    public function generate(Pelanggan $pelanggan): Tagihan
    {
        if (empty($pelanggan->tanggal_aktif)) {
            throw new \InvalidArgumentException(
                "Pelanggan {$pelanggan->nama} belum memiliki tanggal aktif."
            );
        }

        if (!$pelanggan->paket) {
            throw new \InvalidArgumentException(
                "Pelanggan {$pelanggan->nama} belum memiliki paket."
            );
        }

        $tanggalAktif = Carbon::parse($pelanggan->tanggal_aktif);
        $hariIni = Carbon::today();
        $periode = $hariIni->format('Y-m');

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                return DB::transaction(function () use (
                    $pelanggan,
                    $periode,
                    $tanggalAktif,
                    $hariIni
                ) {
                    // Lock the customer row so cron/manual generation for the same
                    // customer cannot pass the duplicate check concurrently.
                    $pelangganTerkunci = Pelanggan::query()
                        ->whereKey($pelanggan->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $exists = Tagihan::where('pelanggan_id', $pelangganTerkunci->id)
                        ->where('periode', $periode)
                        ->exists();

                    if ($exists) {
                        throw new \RuntimeException(
                            "Tagihan periode {$periode} sudah ada."
                        );
                    }

                    $paket = $pelangganTerkunci->paket;
                    if (!$paket) {
                        throw new \InvalidArgumentException(
                            "Pelanggan {$pelangganTerkunci->nama} belum memiliki paket."
                        );
                    }

                    $jumlahHariBulanIni = Carbon::create(
                        $hariIni->year,
                        $hariIni->month,
                        1
                    )->daysInMonth;

                    $hariTagihan = min($tanggalAktif->day, $jumlahHariBulanIni);

                    $tanggalTagihan = Carbon::create(
                        $hariIni->year,
                        $hariIni->month,
                        $hariTagihan
                    );

                    $tanggalJatuhTempo = $tanggalTagihan->copy()->addDays(10);
                    $nominal = (float) $paket->harga;

                    return Tagihan::create([
                        'pelanggan_id' => $pelangganTerkunci->id,
                        'invoice_no' => $this->generateInvoiceNumber(),
                        'periode' => $periode,
                        'bulan' => $tanggalTagihan->month,
                        'tahun' => $tanggalTagihan->year,
                        'tanggal_tagihan' => $tanggalTagihan,
                        'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
                        'nominal' => $nominal,
                        'denda' => 0,
                        'total' => $nominal,
                        'dibayar' => 0,
                        'sisa' => $nominal,
                        'status' => Tagihan::STATUS_BELUM_BAYAR,
                        'keterangan' => 'Tagihan Internet Periode ' . $periode,
                    ]);
                });
            } catch (QueryException $e) {
                $message = strtolower($e->getMessage());
                $isInvoiceDuplicate = (string) $e->getCode() === '23000'
                    && str_contains($message, 'invoice_no');

                if (!$isInvoiceDuplicate || $attempt === 3) {
                    throw $e;
                }

                usleep(100000 * $attempt);
            }
        }

        throw new \RuntimeException('Gagal membuat nomor invoice unik.');
    }

    /**
     * Generate tagihan otomatis berdasarkan tanggal aktif pelanggan.
     */
    public function generateHarian(): int
    {
        $hariIni = now()->day;

        $pelanggans = Pelanggan::with('paket')
            ->where('status', 'Aktif')
            ->get();

        $jumlah = 0;

        foreach ($pelanggans as $pelanggan) {
            if (empty($pelanggan->tanggal_aktif)) {
                continue;
            }

            if (Carbon::parse($pelanggan->tanggal_aktif)->day !== $hariIni) {
                continue;
            }

            try {
                $this->generate($pelanggan);
                $jumlah++;
            } catch (\RuntimeException $e) {
                // Duplicate generation is expected when cron and a manual
                // trigger overlap. Other runtime errors are logged below.
                if (!str_contains($e->getMessage(), 'sudah ada')) {
                    Log::error('Generate tagihan gagal', [
                        'pelanggan_id' => $pelanggan->id,
                        'message' => $e->getMessage(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Generate tagihan gagal', [
                    'pelanggan_id' => $pelanggan->id,
                    'message' => $e->getMessage(),
                    'exception' => $e::class,
                ]);
            }
        }

        return $jumlah;
    }
}
