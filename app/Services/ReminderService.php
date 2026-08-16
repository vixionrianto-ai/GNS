<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Tagihan;
use Carbon\Carbon;

class ReminderService
{
    public function __construct(
        protected WhatsAppService $whatsAppService
    ) {
    }

    public function reminderConfigured(): array
    {
        $firstDays = max(0, (int) Setting::value('whatsapp.reminder_h3', 3));
        $secondDays = max(0, (int) Setting::value('whatsapp.reminder_h7', 7));

        return [
            'reminder_1' => $this->sendConfiguredReminder('reminder_1', $firstDays, 'whatsapp.template_h3'),
            'reminder_2' => $this->sendConfiguredReminder('reminder_2', $secondDays, 'whatsapp.template_h7'),
        ];
    }

    public function reminderH3(): int
    {
        $days = max(0, (int) Setting::value('whatsapp.reminder_h3', 3));
        return $this->sendConfiguredReminder('reminder_1', $days, 'whatsapp.template_h3');
    }

    public function reminderH7(): int
    {
        $days = max(0, (int) Setting::value('whatsapp.reminder_h7', 7));
        return $this->sendConfiguredReminder('reminder_2', $days, 'whatsapp.template_h7');
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
            ->whereDate('tanggal_jatuh_tempo', '<=', $tanggalBatas)
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

                $pesan = $this->renderTemplate($templateKey, $tagihan);
                if ($pesan === '') {
                    continue;
                }

                $nomor = $tagihan->pelanggan?->no_hp;
                if (!$this->validNomor($nomor)) {
                    continue;
                }

                $berhasil = $this->whatsAppService->kirim($nomor, $pesan);

                $this->whatsAppService->simpanLog(
                    $tagihan->pelanggan,
                    $tagihan,
                    $jenis,
                    $nomor,
                    $pesan,
                    $berhasil,
                    null
                );

                if ($berhasil) {
                    $jumlah++;
                }
            } catch (\Throwable $e) {
                report($e);
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
            ->whereDate('tanggal_jatuh_tempo', '<=', $tanggalBatas)
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
        $pelanggan = $tagihan->pelanggan;
        if (!$pelanggan) {
            return '';
        }

        $template = Setting::value($templateKey, '');
        if (!$template) {
            return '';
        }

        $sisa = (float) $tagihan->getSisaTagihan();

        $data = [
            'nama' => $pelanggan->nama,
            'invoice' => $tagihan->invoice_no,
            'periode' => $this->periodeIndonesia($tagihan),
            'bulan' => $tagihan->bulan,
            'tahun' => $tagihan->tahun,
            'nominal' => 'Rp ' . $this->rupiah($tagihan->nominal),
            'denda' => 'Rp ' . $this->rupiah($tagihan->denda),
            'total' => 'Rp ' . $this->rupiah($tagihan->getTotalTagihan()),
            'jatuh_tempo' => optional($tagihan->tanggal_jatuh_tempo)->format('d-m-Y'),
            'total_sisa' => 'Rp ' . $this->rupiah($sisa),
            'total_harus_dibayar' => 'Rp ' . $this->rupiah($sisa),
            'isp' => config('app.name'),
        ];

        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', (string) $value, $template);
        }

        return trim($template);
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
