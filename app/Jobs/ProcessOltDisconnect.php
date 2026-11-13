<?php

namespace App\Jobs;

use App\Models\OltOnuState;
use App\Models\Pelanggan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ProcessOltDisconnect implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $timeout = 30;
    public array $backoff = [15, 30, 60];

    public function __construct(public int $pelangganId, public string $eventId)
    {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $lock = Cache::lock('olt:kuwu:notify-lock:' . $this->pelangganId, 45);
        $lock->block(10);

        try {
            $pendingKey = 'olt:kuwu:pending-down:' . $this->pelangganId;
            $notifiedKey = 'olt:kuwu:down-notified:' . $this->pelangganId;

            // Ada event UP atau DOWN yang lebih baru: job lama tidak boleh mengirim alarm.
            if (Cache::get($pendingKey) !== $this->eventId) {
                Log::info('Job DOWN KUWU dibatalkan karena event sudah berubah.', [
                    'pelanggan_id' => $this->pelangganId,
                    'event_id' => $this->eventId,
                ]);
                return;
            }

            if (Cache::has($notifiedKey)) {
                return;
            }

            $customer = Pelanggan::with('router')->find($this->pelangganId);
            if (!$customer || !$customer->router ||
                strtoupper(trim((string) $customer->router->nama_router)) !== 'KUWU') {
                Log::warning('Job DOWN dibatalkan: pelanggan bukan router KUWU.', [
                    'pelanggan_id' => $this->pelangganId,
                ]);
                return;
            }

            // Snapshot ONU hanya untuk identitas/informasi terakhir. Tidak melakukan lookup OLT.
            $state = OltOnuState::where('pelanggan_id', $customer->id)->first();
            $snapshot = [
                'onu' => $state?->onu,
                'distance' => $state?->distance,
                'rx_power' => $state?->rx_power,
                'tx_power' => $state?->tx_power,
                'last_checked_at' => $state?->last_checked_at,
            ];

            if (!$this->sendTelegram($customer, $snapshot)) {
                throw new RuntimeException('Telegram gagal mengirim notifikasi PPP KUWU terputus.');
            }

            Cache::put($notifiedKey, [
                'event_id' => $this->eventId,
                'notified_at' => now()->toIso8601String(),
            ], now()->addDays(30));

            Log::info('Notifikasi PPP DOWN KUWU berhasil dikirim.', [
                'pelanggan_id' => $customer->id,
                'username' => $customer->username_pppoe,
                'event_id' => $this->eventId,
            ]);
        } catch (Throwable $e) {
            Log::warning('Job notifikasi PPP DOWN KUWU gagal.', [
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
            Log::warning('Telegram notifikasi PPP KUWU belum dikonfigurasi lengkap.');
            return false;
        }

        $lines = [
            '🔴 <b>PELANGGAN PPP TERPUTUS</b>',
            '<b>Pelanggan:</b> ' . $this->escape((string) $customer->nama),
            '<b>Kode:</b> ' . $this->escape((string) ($customer->kode_pelanggan ?? '-')),
            '<b>Router:</b> KUWU',
            '<b>Username:</b> ' . $this->escape((string) $customer->username_pppoe),
            '<b>Status:</b> PPP DISCONNECTED',
            '<b>ONU terakhir tercatat:</b> ' . $this->escape((string) ($snapshot['onu'] ?: '-')),
            '<i>Status OFFLINE optik belum diverifikasi ke OLT.</i>',
        ];

        if (!empty($snapshot['last_checked_at'])) {
            $checkedAt = $snapshot['last_checked_at'] instanceof \DateTimeInterface
                ? $snapshot['last_checked_at']->format('d-m-Y H:i:s')
                : (string) $snapshot['last_checked_at'];
            $lines[] = '<b>Data optik terakhir dibaca:</b> ' . $this->escape($checkedAt);
        }

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
                Log::warning('Telegram gagal mengirim notifikasi PPP KUWU.', [
                    'http_status' => $response->status(),
                    'response' => $response->json(),
                ]);
                return false;
            }
            return true;
        } catch (Throwable $e) {
            Log::error('Koneksi Telegram notifikasi PPP KUWU gagal.', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
