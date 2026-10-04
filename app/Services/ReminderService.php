<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Tagihan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReminderService
{
    public function __construct(
        protected WhatsAppService $whatsAppService
    ) {
    }

    public function reminderConfigured(): array
    {
        $firstDays = max(0, (int) Setting::value('whatsapp.reminder_first_days', 5));
        $secondDays = max(0, (int) Setting::value('whatsapp.reminder_second_days', 14));

        return [
            // WA tagihan utama dikirim pada reminder pertama (H+N dari setting website).
            // Tidak ada pengiriman otomatis pada tanggal jatuh tempo H.
            'tagihan' => $this->sendFirstConfiguredTagihan($firstDays),
            // Reminder kedua tetap mengikuti H+N dari setting website.
            'reminder_second' => $this->sendConfiguredReminder('reminder_second', $secondDays, 'whatsapp.template_reminder_second'),
        ];
    }

    protected function sendFirstConfiguredTagihan(int $days): int
    {
        $jumlah = 0;
        $tanggalBatas = Carbon::today()->subDays($days);

        $tagihans = Tagihan::with('pelanggan')
            ->whereIn('status', [
                Tagihan::STATUS_BELUM_BAYAR,
                Tagihan::STATUS_JATUH_TEMPO,
                Tagihan::STATUS_SEBAGIAN,
            ])
            ->whereDate('tanggal_jatuh_tempo', '=', $tanggalBatas)
            ->whereHas('pelanggan', function ($query) {
                $query->whereNotNull('no_hp')
                    ->where('no_hp', '!=', '')
                    ->where('no_hp', '!=', '-')
                    ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(no_hp, ' ', ''), '-', ''), '+', ''), '(', '') REGEXP '[0-9]{8,}'");
            })
            ->get();

        foreach ($tagihans as $tagihan) {
            try {
                if (
                    $this->whatsAppService->sudahPernahKirim($tagihan, 'reminder_first') ||
                    $this->whatsAppService->sudahPernahKirim($tagihan, 'tagihan')
                ) {
                    continue;
                }

                // Pesan otomatis HARUS identik dengan pesan manual tombol WhatsApp
                // pada halaman Tagihan.
                $pesan = $this->whatsAppService->pesanTagihanBaru($tagihan);
                $nomor = $tagihan->pelanggan?->no_hp;

                if (!$this->validNomor($nomor)) {
                    continue;
                }

                $berhasil = $this->whatsAppService->kirim($nomor, $pesan);
                $response = $this->whatsAppService->lastResponse();

                $this->whatsAppService->simpanLog(
                    $tagihan->pelanggan,
                    $tagihan,
                    'reminder_first',
                    $nomor,
                    $pesan,
                    $berhasil,
                    $response
                );

                if ($berhasil) {
                    $jumlah++;
                }
            } catch (Throwable $e) {
                Log::error('WhatsApp Tagihan Reminder Pertama Error', [
                    'tagihan_id' => $tagihan->id ?? null,
                    'pelanggan_id' => $tagihan->pelanggan_id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $jumlah;
    }

    protected function sendConfiguredReminder(string $jenis, int $days, string $templateKey): int
    {
        $jumlah = 0;
        $tanggalBatas = Carbon::today()->subDays($days);

        $tagihans = Tagihan::with('pelanggan')
            ->whereIn('status', [
                Tagihan::STATUS_BELUM_BAYAR,
                Tagihan::STATUS_JATUH_TEMPO,
                Tagihan::STATUS_SEBAGIAN,
            ])
            ->whereDate('tanggal_jatuh_tempo', '=', $tanggalBatas)
            ->whereHas('pelanggan', function ($query) {
                $query->whereNotNull('no_hp')
                    ->where('no_hp', '!=', '')
                    ->where('no_hp', '!=', '-')
                    ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(no_hp, ' ', ''), '-', ''), '+', ''), '(', '') REGEXP '[0-9]{8,}'");
            })
            ->get();

        foreach ($tagihans as $tagihan) {
            try {
                if ($this->whatsAppService->sudahPernahKirim($tagihan, $jenis)) {
                    continue;
                }

                // Reminder kedua juga menggunakan pesan manual yang sama persis.
                // Parameter $templateKey tetap dipertahankan agar kompatibel dengan
                // pemanggil yang sudah ada, tetapi tidak mengubah isi pesan.
                $pesan = $this->whatsAppService->pesanTagihanBaru($tagihan);
                $nomor = $tagihan->pelanggan?->no_hp;

                if (!$this->validNomor($nomor)) {
                    continue;
                }

                $berhasil = $this->whatsAppService->kirim($nomor, $pesan);
                $response = $this->whatsAppService->lastResponse();

                $this->whatsAppService->simpanLog(
                    $tagihan->pelanggan,
                    $tagihan,
                    $jenis,
                    $nomor,
                    $pesan,
                    $berhasil,
                    $response
                );

                if ($berhasil) {
                    $jumlah++;
                }
            } catch (Throwable $e) {
                Log::error('WhatsApp Reminder Error', [
                    'jenis' => $jenis,
                    'tagihan_id' => $tagihan->id ?? null,
                    'pelanggan_id' => $tagihan->pelanggan_id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $jumlah;
    }

    public function countConfiguredCandidates(int $days): int
    {
        $tanggalBatas = Carbon::today()->subDays(max(0, $days));

        return Tagihan::query()
            ->whereIn('status', [
                Tagihan::STATUS_BELUM_BAYAR,
                Tagihan::STATUS_JATUH_TEMPO,
                Tagihan::STATUS_SEBAGIAN,
            ])
            ->whereDate('tanggal_jatuh_tempo', '=', $tanggalBatas)
            ->whereHas('pelanggan', function ($query) {
                $query->whereNotNull('no_hp')
                    ->where('no_hp', '!=', '')
                    ->where('no_hp', '!=', '-')
                    ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(no_hp, ' ', ''), '-', ''), '+', ''), '(', '') REGEXP '[0-9]{8,}'");
            })
            ->count();
    }

    protected function validNomor(?string $nomor): bool
    {
        if ($nomor === null) {
            return false;
        }

        $nomor = trim($nomor);
        if ($nomor === '' || $nomor === '-') {
            return false;
        }

        return preg_match('/[0-9]{8,}/', preg_replace('/[^0-9]/', '', $nomor)) === 1;
    }

    protected function renderTemplate(string $templateKey, Tagihan $tagihan): string
    {
        return trim($this->whatsAppService->renderConfiguredTagihanTemplate($tagihan, $templateKey));
    }

    protected function rupiah($nilai): string
    {
        return number_format((float) $nilai, 0, ',', '.');
    }

    protected function periodeIndonesia(Tagihan $tagihan): string
    {
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return ($namaBulan[(int) $tagihan->bulan] ?? (string) $tagihan->bulan)
            . ' ' . $tagihan->tahun;
    }
}
