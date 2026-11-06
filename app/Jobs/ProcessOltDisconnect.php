<?php

namespace App\Jobs;

use App\Models\OltOnuState;
use App\Models\Pelanggan;
use App\Services\OltService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ProcessOltDisconnect implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;
    public array $backoff = [15, 30];

    public function __construct(public int $pelangganId)
    {
        $this->onQueue('default');
    }

    public function handle(OltService $oltService): void
    {
        $customer = Pelanggan::with('router')->find($this->pelangganId);

        if (!$customer || !$customer->router) {
            Log::warning('Job OLT dibatalkan: pelanggan atau router tidak ditemukan.', [
                'pelanggan_id' => $this->pelangganId,
            ]);
            return;
        }

        if (strtoupper(trim((string) $customer->router->nama_router)) !== 'KUWU') {
            Log::warning('Job OLT dibatalkan: router bukan KUWU.', [
                'pelanggan_id' => $customer->id,
                'router' => $customer->router->nama_router,
            ]);
            return;
        }

        try {
            $onu = $oltService->findByUsername(
                $customer->router,
                (string) $customer->username_pppoe,
                $customer->mac_address
            );

            // Kegagalan membaca ONU bukan bukti offline. Lempar error agar queue retry.
            if (!is_array($onu) || !isset($onu['status']) || empty($onu['onu'])) {
                throw new RuntimeException('Data ONU KUWU belum berhasil dibaca atau ONU tidak ditemukan.');
            }

            $value = mb_strtolower(trim((string) $onu['status']));

            if (preg_match('/los|offline|down|dying.?gasp|loss|disable|inactive|unregistered|failed|power.?off/', $value)) {
                $status = 'offline';
            } elseif (preg_match('/online|working|normal|up|enable|active|registered|operational/', $value)) {
                $status = 'online';
            } else {
                throw new RuntimeException('Status ONU KUWU tidak dikenal: ' . $value);
            }

            $state = OltOnuState::where('pelanggan_id', $customer->id)->first();

            $snapshot = [
                'router_id' => $customer->router->id,
                'onu' => $onu['onu'],
                'status' => $status,
                'distance' => $this->displayValue($onu['distance'] ?? null),
                'rx_power' => $this->displayValue($onu['rx_power'] ?? null),
                'tx_power' => $this->displayValue($onu['tx_power'] ?? null),
                'last_checked_at' => now(),
            ];

            // Jika ONU sudah online kembali dalam masa tunggu, jangan kirim alarm putus.
            if ($status !== 'offline') {
                OltOnuState::updateOrCreate(
                    ['pelanggan_id' => $customer->id],
                    $snapshot
                );
                return;
            }

            // Event down adalah pemicu pembacaan OLT. Tidak perlu baseline polling.
            // Kirim alarm hanya saat status belum tercatat offline agar event berulang tidak spam.
            $notified = false;

            if ($state?->status !== 'offline') {
                if (!$this->sendTelegram($customer, $snapshot)) {
                    throw new RuntimeException('Telegram gagal mengirim notifikasi OLT KUWU.');
                }

                $notified = true;
            }

            OltOnuState::updateOrCreate(
                ['pelanggan_id' => $customer->id],
                $snapshot
            );

            Log::info('Event OLT KUWU selesai diproses.', [
                'pelanggan_id' => $customer->id,
                'username' => $customer->username_pppoe,
                'onu' => $snapshot['onu'],
                'notified' => $notified,
            ]);
        } catch (Throwable $e) {
            Log::warning('Job OLT KUWU gagal; antrean akan mencoba ulang bila kesempatan masih tersedia.', [
                'pelanggan_id' => $this->pelangganId,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function sendTelegram(Pelanggan $customer, array $snapshot): bool
    {
        $botToken = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (!(bool) config('services.telegram.enabled') || !filled($botToken) || !filled($chatId)) {
            Log::warning('Telegram OLT KUWU belum dikonfigurasi lengkap.');
            return false;
        }

        $lines = [
            '🔴 <b>PELANGGAN OLT PUTUS</b>',
            '<b>Pelanggan:</b> ' . $this->escape((string) $customer->nama),
            '<b>Kode:</b> ' . $this->escape((string) ($customer->kode_pelanggan ?? '-')),
            '<b>Router:</b> KUWU',
            '<b>Username:</b> ' . $this->escape((string) $customer->username_pppoe),
            '<b>ONU:</b> ' . $this->escape((string) ($snapshot['onu'] ?? '-')),
            '<b>Status ONU:</b> OFFLINE',
            '<b>MAC:</b> ' . $this->escape((string) ($customer->mac_address ?? '-')),
        ];

        if ($snapshot['distance'] !== null) {
            $lines[] = '<b>Jarak:</b> ' . $this->escape($snapshot['distance']) . ' m';
        }
        if ($snapshot['rx_power'] !== null) {
            $lines[] = '<b>RX Power:</b> ' . $this->escape($snapshot['rx_power']) . ' dBm';
        }
        if ($snapshot['tx_power'] !== null) {
            $lines[] = '<b>TX Power:</b> ' . $this->escape($snapshot['tx_power']) . ' dBm';
        }

        if (is_numeric($customer->latitude) && is_numeric($customer->longitude)) {
            $url = 'https://www.google.com/maps?q=' .
                rawurlencode($customer->latitude . ',' . $customer->longitude);
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
                Log::warning('Telegram gagal mengirim OLT KUWU.', [
                    'http_status' => $response->status(),
                    'response' => $response->json(),
                ]);
                return false;
            }

            return true;
        } catch (Throwable $e) {
            Log::error('Koneksi Telegram OLT KUWU gagal.', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function displayValue(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' || $value === '-' ? null : $value;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
