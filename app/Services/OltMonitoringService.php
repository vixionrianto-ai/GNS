<?php

namespace App\Services;

use App\Models\OltOnuState;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OltMonitoringService
{
    public function __construct(private OltService $oltService)
    {
    }

    public function run(): array
    {
        $summary = ['checked' => 0, 'online' => 0, 'offline' => 0, 'unknown' => 0, 'errors' => 0];

        Pelanggan::query()
            ->with('router')
            ->where('status', Pelanggan::AKTIF)
            ->whereNotNull('username_pppoe')
            ->where('username_pppoe', '<>', '')
            ->whereHas('router')
            ->orderBy('id')
            ->chunkById(50, function ($customers) use (&$summary): void {
                foreach ($customers as $customer) {
                    $router = $customer->router;

                    if (!$router || !$this->oltService->enabled($router)) {
                        continue;
                    }

                    try {
                        // Match the ONU by PPP username first, then MAC address when available.
                        $onu = $this->oltService->findByUsername(
                            $router,
                            (string) $customer->username_pppoe,
                            $customer->mac_address
                        );

                        // A missing ONU or a failed OLT request is not proof that the customer is offline.
                        if (!is_array($onu) || !isset($onu['status'])) {
                            $summary['unknown']++;
                            continue;
                        }

                        $status = $this->normalizeStatus((string) $onu['status']);
                        if ($status === null) {
                            $summary['unknown']++;
                            continue;
                        }

                        $summary['checked']++;
                        $summary[$status]++;

                        $state = OltOnuState::query()->where('pelanggan_id', $customer->id)->first();
                        $oldStatus = $state?->status;

                        $snapshot = [
                            'router_id' => $router->id,
                            'onu' => $onu['onu'] ?? null,
                            'status' => $status,
                            'distance' => $this->displayValue($onu['distance'] ?? null),
                            'rx_power' => $this->displayValue($onu['rx_power'] ?? null),
                            'tx_power' => $this->displayValue($onu['tx_power'] ?? null),
                            'last_checked_at' => now(),
                        ];

                        $mustNotify = ($oldStatus !== null && $oldStatus !== $status)
                            || ($oldStatus === null && $status === 'offline');

                        if ($mustNotify) {
                            $sent = $this->sendNotification($customer, $router->nama_router, $snapshot, $oldStatus);
                            if (!$sent) {
                                // Keep the previous state so the transition can be retried next cycle.
                                $summary['errors']++;
                                continue;
                            }
                        }

                        OltOnuState::query()->updateOrCreate(
                            ['pelanggan_id' => $customer->id],
                            $snapshot
                        );
                    } catch (Throwable $e) {
                        $summary['errors']++;
                        Log::error('OLT monitoring gagal untuk pelanggan.', [
                            'pelanggan_id' => $customer->id,
                            'router' => $router->nama_router ?? null,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        return $summary;
    }

    private function normalizeStatus(string $raw): ?string
    {
        $value = mb_strtolower(trim($raw));

        if ($value === '') {
            return null;
        }

        if (preg_match('/los|offline|down|dying.?gasp|loss|disable|inactive|unregistered|failed|power.?off/', $value)) {
            return 'offline';
        }

        if (preg_match('/online|working|normal|up|enable|active|registered|operational/', $value)) {
            return 'online';
        }

        return null;
    }

    private function sendNotification(Pelanggan $customer, string $routerName, array $snapshot, ?string $oldStatus): bool
    {
        if (!(bool) config('services.telegram.enabled')
            || !filled(config('services.telegram.bot_token'))
            || !filled(config('services.telegram.chat_id'))) {
            Log::warning('Notifikasi OLT tidak dikirim karena konfigurasi Telegram belum lengkap.');
            return false;
        }

        $isOffline = $snapshot['status'] === 'offline';
        $title = $isOffline ? '🔴 ONU OFFLINE / LOS' : '🟢 ONU ONLINE KEMBALI';
        $lines = [
            $title,
            '<b>Pelanggan:</b> ' . $this->escape((string) $customer->nama),
            '<b>Kode:</b> ' . $this->escape((string) ($customer->kode_pelanggan ?? '-')),
            '<b>Router:</b> ' . $this->escape($routerName),
            '<b>Username:</b> ' . $this->escape((string) $customer->username_pppoe),
            '<b>ONU:</b> ' . $this->escape((string) ($snapshot['onu'] ?? '-')),
            '<b>Status:</b> ' . strtoupper($this->escape($snapshot['status'])),
        ];

        if ($snapshot['distance'] !== null) {
            $lines[] = '<b>Jarak:</b> ' . $this->escape($snapshot['distance']);
        }
        if ($snapshot['rx_power'] !== null) {
            $lines[] = '<b>RX Power:</b> ' . $this->escape($snapshot['rx_power']);
        }
        if ($snapshot['tx_power'] !== null) {
            $lines[] = '<b>TX Power:</b> ' . $this->escape($snapshot['tx_power']);
        }

        if (is_numeric($customer->latitude) && is_numeric($customer->longitude)) {
            $lat = (float) $customer->latitude;
            $lon = (float) $customer->longitude;
            $url = 'https://www.google.com/maps?q=' . rawurlencode($lat . ',' . $lon);
            $lines[] = '<a href="' . $this->escape($url) . '">📍 Buka lokasi pelanggan</a>';
        } else {
            $lines[] = '<i>Lokasi pelanggan belum diisi.</i>';
        }

        try {
            $token = (string) config('services.telegram.bot_token');
            $response = Http::asForm()
                ->timeout(10)
                ->post('https://api.telegram.org/bot' . $token . '/sendMessage', [
                    'chat_id' => config('services.telegram.chat_id'),
                    'text' => implode("\n", $lines),
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]);

            if (!$response->successful() || !$response->json('ok')) {
                Log::warning('Telegram menolak notifikasi OLT.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (Throwable $e) {
            Log::error('Gagal mengirim notifikasi OLT ke Telegram.', ['error' => $e->getMessage()]);
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
