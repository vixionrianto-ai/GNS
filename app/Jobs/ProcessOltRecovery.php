<?php

namespace App\\Jobs;

use App\\Models\\OltOnuState;
use App\\Models\\Pelanggan;
use Illuminate\\Bus\\Queueable;
use Illuminate\\Contracts\\Queue\\ShouldQueue;
use Illuminate\\Foundation\\Bus\\Dispatchable;
use Illuminate\\Queue\\InteractsWithQueue;
use Illuminate\\Queue\\SerializesModels;
use Illuminate\\Support\\Facades\\Cache;
use Illuminate\\Support\\Facades\\Http;
use Illuminate\\Support\\Facades\\Log;
use Throwable;

class ProcessOltRecovery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $timeout = 30;
    public array $backoff = [15, 30, 60];

    public function __construct(public int $pelangganId)
    {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $lock = Cache::lock('olt:kuwu:notify-lock:' . $this->pelangganId, 45);
        $lock->block(10);

        try {
            $notifiedKey = 'olt:kuwu:down-notified:' . $this->pelangganId;
            if (!Cache::has($notifiedKey)) {
                return;
            }

            $customer = Pelanggan::with('router')->find($this->pelangganId);
            if (!$customer || !$customer->router ||
                strtoupper(trim((string) $customer->router->nama_router)) !== 'KUWU') {
                Log::warning('Job recovery dibatalkan: pelanggan bukan router KUWU.', [
                    'pelanggan_id' => $this->pelangganId,
                ]);
                return;
            }

            $state = OltOnuState::where('pelanggan_id', $customer->id)->first();
            if (!$this->sendTelegram($customer, [
                'onu' => $state?->onu,
                'distance' => $state?->distance,
                'rx_power' => $state?->rx_power,
                'tx_power' => $state?->tx_power,
            ])) {
                throw new \RuntimeException('Telegram gagal mengirim notifikasi pemulihan PPP KUWU.');
            }

            Cache::forget($notifiedKey);
            Log::info('Notifikasi PPP UP KUWU berhasil dikirim.', [
                'pelanggan_id' => $customer->id,
                'username' => $customer->username_pppoe,
            ]);
        } catch (Throwable $e) {
            Log::warning('Job notifikasi PPP UP KUWU gagal.', [
                'pelanggan_id' => $this->pelangganId,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } finally {
            $lock->release();
        }
    }

    private function sendTelegram(Pelanggan $customer, array $snapshot): bool
    {
        $botToken = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (!(bool) config('services.telegram.enabled') || !filled($botToken) || !filled($chatId)) {
            Log::warning('Telegram pemulihan PPP KUWU belum dikonfigurasi lengkap.');
            return false;
        }

        $lines = [
            '🟢 <b>PELANGGAN PPP TERHUBUNG KEMBALI</b>',
            '<b>Pelanggan:</b> ' . $this->escape((string) $customer->nama),
            '<b>Kode:</b> ' . $this->escape((string) ($customer->kode_pelanggan ?? '-')),
            '<b>Router:</b> KUWU',
            '<b>Username:</b> ' . $this->escape((string) $customer->username_pppoe),
            '<b>Status:</b> PPP CONNECTED',
            '<b>ONU terakhir tercatat:</b> ' . $this->escape((string) ($snapshot['onu'] ?: '-')),
        ];

        if (!empty($snapshot['distance'])) $lines[] = '<b>Jarak terakhir:</b> ' . $this->escape((string) $snapshot['distance']) . ' m';
        if (!empty($snapshot['rx_power'])) $lines[] = '<b>RX Power terakhir:</b> ' . $this->escape((string) $snapshot['rx_power']) . ' dBm';
        if (!empty($snapshot['tx_power'])) $lines[] = '<b>TX Power terakhir:</b> ' . $this->escape((string) $snapshot['tx_power']) . ' dBm';

        if (is_numeric($customer->latitude) && is_numeric($customer->longitude)) {
            $url = 'https://www.google.com/maps?q=' . rawurlencode($customer->latitude . ',' . $customer->longitude);
            $lines[] = '<a href="' . $this->escape($url) . '">📍 Buka lokasi pelanggan</a>';
        }

        try {
            $response = Http::asForm()->timeout(10)->post(
                'https://api.telegram.org/bot' . $botToken . '/sendMessage',
                [
                    'chat_id' => $chatId,
                    'text' => implode("\n", $lines),
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]
            );

            if (!$response->successful() || !$response->json('ok')) {
                Log::warning('Telegram gagal mengirim pemulihan PPP KUWU.', [
                    'http_status' => $response->status(),
                    'response' => $response->json(),
                ]);
                return false;
            }
            return true;
        } catch (Throwable $e) {
            Log::error('Koneksi Telegram pemulihan PPP KUWU gagal.', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
