<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\PppEvent;
use App\Models\Router;
use Illuminate\Support\Facades\DB;

class PppEventService
{
    public function __construct(
        protected TelegramService $telegram,
        protected OltService $olt,
        protected MikroTikService $mikrotik
    ) {
    }

    /**
     * Simpan event realtime PPP, sinkronkan status pelanggan,
     * lalu kirim Telegram hanya untuk disconnect baru.
     */
    public function handle(Router $router, array $event): ?PppEvent
    {
        $username = trim((string) ($event['name'] ?? ''));
        if ($username === '') {
            return null;
        }

        $dead = (($event['.dead'] ?? '') === 'yes');
        $eventType = $dead ? 'disconnect' : 'connect';

        $sessionId = trim((string) ($event['session-id'] ?? ''));
        $routerItemId = trim((string) ($event['.id'] ?? ''));

        $fingerprint = implode('|', [
            $router->id,
            $eventType,
            $sessionId,
            $routerItemId,
            $username,
            (string) ($event['caller-id'] ?? ''),
            (string) ($event['address'] ?? ''),
        ]);
        $eventKey = hash('sha256', $fingerprint);

        $pelanggan = Pelanggan::query()
            ->where('router_id', $router->id)
            ->where('username_pppoe', $username)
            ->first();

        // ONLINE hanya dikirim ketika pelanggan memang sebelumnya tercatat offline.
        // Ini mencegah CONNECT/UPDATE biasa menjadi spam Telegram.
        $wasOffline = $pelanggan?->ppp_status === 'offline';

        $record = DB::transaction(function () use (
            $router,
            $pelanggan,
            $eventType,
            $username,
            $event,
            $sessionId,
            $eventKey
        ) {
            $record = PppEvent::firstOrCreate(
                ['event_key' => $eventKey],
                [
                    'router_id' => $router->id,
                    'pelanggan_id' => $pelanggan?->id,
                    'event_type' => $eventType,
                    'username' => $username,
                    'ip_address' => $event['address'] ?? null,
                    'caller_id' => $event['caller-id'] ?? null,
                    'session_id' => $sessionId !== '' ? $sessionId : null,
                    'event_at' => now(),
                    'payload' => $event,
                ]
            );

            if ($pelanggan) {
                if ($eventType === 'disconnect') {
                    $pelanggan->forceFill([
                        'ppp_status' => 'offline',
                        'ppp_ip_address' => null,
                        'ppp_caller_id' => null,
                        'ppp_uptime' => null,
                        'ppp_last_change_at' => now(),
                        'last_ppp_checked_at' => now(),
                    ])->save();
                } else {
                    $pelanggan->forceFill([
                        'ppp_status' => 'online',
                        'ppp_ip_address' => $event['address'] ?? null,
                        'ppp_caller_id' => $event['caller-id'] ?? null,
                        'ppp_uptime' => $event['uptime'] ?? null,
                        'ppp_last_change_at' => now(),
                        'last_ppp_checked_at' => now(),
                    ])->save();
                }
            }

            return $record;
        });

        // OFFLINE: kirim hanya sekali untuk event disconnect yang benar-benar baru.
        if ($eventType === 'disconnect' && $record->wasRecentlyCreated) {
            $oltData = $this->olt->findByUsername($username, $event['caller-id'] ?? null);

            $this->telegram->send(
                $this->formatDisconnectMessage($router, $event, $pelanggan, $oltData)
            );
        }

        // ONLINE: kirim hanya saat pelanggan yang sebelumnya offline kembali online.
        // Event CONNECT/UPDATE yang terjadi saat pelanggan sudah online tidak dikirim.
        if ($eventType === 'connect' && $record->wasRecentlyCreated && $wasOffline) {
            $oltData = $this->olt->findByUsername($username, $event['caller-id'] ?? null);

            $this->telegram->send(
                $this->formatConnectMessage($router, $event, $pelanggan, $oltData)
            );
        }

        return $record;
    }

    protected function formatConnectMessage(
        Router $router,
        array $event,
        ?Pelanggan $pelanggan = null,
        ?array $oltData = null
    ): string
    {
        return
            'ONLINE ' . $router->nama_router . "\n" .
            "====================\n" .
            'Time: ' . now()->format('Y-m-d H:i:s') . "\n" .
            "====================\n" .
            'User: ' . ($event['name'] ?? '-') . "\n" .
            'IP Client: ' . ($event['address'] ?? '-') . "\n" .
            'Caller ID: ' . ($event['caller-id'] ?? '-') . "\n" .
            'Profile: ' . ($pelanggan?->paket?->profile_mikrotik ?? $event['profile'] ?? '-') . "\n\n" .
            'ONU: ' . ($oltData['onu'] ?? '-') . "\n" .
            'RX Power: ' . ($oltData['rx_power'] ?? '-') . ' dBm' . "\n" .
            'TX Power: ' . ($oltData['tx_power'] ?? '-') . ' dBm' . "\n" .
            'Distance: ' . ($oltData['distance'] ?? '-') . ' m' . "\n" .
            'Last Degerasi Reason: ' . ($oltData['last_deregister_reason'] ?? '-') . "\n\n" .
            "====================\n" .
            $this->formatCurrentPppSummary($router, 'connect', trim((string) ($event['name'] ?? '')));
    }

    protected function formatCurrentPppSummary(
        Router $router,
        string $eventType = '',
        string $eventUsername = ''
    ): string
    {
        try {
            $activeSessions = $this->mikrotik->getActiveSessions($router);

            $activeNames = [];
            foreach ($activeSessions as $active) {
                $name = $this->normalizeUsername($active['name'] ?? '');
                if ($name !== '') {
                    $activeNames[$name] = true;
                }
            }

            // RouterOS /ppp/active/listen dapat mengirim .dead sedikit lebih
            // cepat daripada /ppp/active/print memperlihatkan perubahan.
            // Pakai event yang sedang diproses sebagai koreksi kondisi saat ini.
            $eventKey = $this->normalizeUsername($eventUsername);
            if ($eventKey !== '') {
                if ($eventType === 'disconnect') {
                    unset($activeNames[$eventKey]);
                } elseif ($eventType === 'connect') {
                    $activeNames[$eventKey] = true;
                }
            }

            $totalSecrets = $this->mikrotik->getSecretCount($router);
            $totalActive = count($activeNames);

            $secretMap = $this->mikrotik->getSecretStatusMap($router);

            $offlineNames = [];
            foreach ($secretMap as $name => $secret) {
                $displayName = trim((string) $name);
                $secretKey = $this->normalizeUsername($displayName);

                if ($secretKey !== '' && !isset($activeNames[$secretKey])) {
                    $offlineNames[] = $displayName;
                }
            }

            sort($offlineNames, SORT_NATURAL | SORT_FLAG_CASE);

            $disconnectedUsers = collect($offlineNames)
                ->map(fn (string $name) => '- ' . $name)
                ->implode("\n");

            if ($disconnectedUsers === '') {
                $disconnectedUsers = '-';
            }

            $today = now()->startOfDay();
            $gangguan = PppEvent::query()
                ->where('router_id', $router->id)
                ->where('event_type', 'disconnect')
                ->where('event_at', '>=', $today)
                ->distinct('username')
                ->count('username');

            return
                'Jumlah Gangguan : ' . $gangguan . 'x Terputus hari ini' . "\n" .
                'Total Secrets: ' . $totalSecrets . "\n" .
                'Total Active: ' . $totalActive . "\n" .
                'Offline Saat Ini (' . count($offlineNames) . "):\n" .
                $disconnectedUsers;
        } catch (\Throwable $e) {
            report($e);

            return
                'Jumlah Gangguan : -' . "\n" .
                'Total Secrets: -' . "\n" .
                'Total Active: -' . "\n" .
                'Offline Saat Ini (-):\n-';
        }
    }

    protected function formatDisconnectMessage(
        Router $router,
        array $event,
        ?Pelanggan $pelanggan = null,
        ?array $oltData = null
    ): string
    {
        return
            'OFFLINE ' . $router->nama_router . "\n" .
            "====================\n" .
            'Time: ' . now()->format('Y-m-d H:i:s') . "\n" .
            "====================\n" .
            'User: ' . ($event['name'] ?? '-') . "\n" .
            'IP Client: ' . ($event['address'] ?? '-') . "\n" .
            'Caller ID: ' . ($event['caller-id'] ?? '-') . "\n" .
            'Profile: ' . ($pelanggan?->paket?->profile_mikrotik ?? $event['profile'] ?? '-') . "\n\n" .
            'ONU: ' . ($oltData['onu'] ?? '-') . "\n" .
            'RX Power: ' . ($oltData['rx_power'] ?? '-') . ' dBm' . "\n" .
            'TX Power: ' . ($oltData['tx_power'] ?? '-') . ' dBm' . "\n" .
            'Distance: ' . ($oltData['distance'] ?? '-') . ' m' . "\n" .
            'Last Degerasi Reason: ' . ($oltData['last_deregister_reason'] ?? '-') . "\n\n" .
            "====================\n" .
            $this->formatCurrentPppSummary($router, 'disconnect', trim((string) ($event['name'] ?? '')));
    }

    protected function normalizeUsername(mixed $username): string
    {
        return mb_strtolower(trim((string) $username));
    }

    public function disconnectCount(Pelanggan $pelanggan, ?string $from = null, ?string $to = null): int
    {
        $query = PppEvent::query()
            ->where('pelanggan_id', $pelanggan->id)
            ->where('event_type', 'disconnect');

        if ($from) $query->where('event_at', '>=', $from);
        if ($to) $query->where('event_at', '<=', $to);

        return $query->count();
    }
}
