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
            $this->formatCurrentPppSummary($router);
    }

    protected function formatCurrentPppSummary(Router $router): string
    {
        $today = now()->startOfDay();

        try {
            $totalSecrets = $this->mikrotik->getSecretCount($router);
            $totalActive = $this->mikrotik->getActiveCount($router);
        } catch (\Throwable $e) {
            report($e);
            $totalSecrets = Pelanggan::query()
                ->where('router_id', $router->id)
                ->whereNotNull('username_pppoe')
                ->where('username_pppoe', '!=', '')
                ->count();

            $totalActive = Pelanggan::query()
                ->where('router_id', $router->id)
                ->where('ppp_status', 'online')
                ->count();
        }

        $activeNames = [];
        try {
            foreach ($this->mikrotik->getActiveSessions($router) as $active) {
                $name = trim((string) ($active['name'] ?? ''));
                if ($name !== '') {
                    $activeNames[$name] = true;
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        $disconnects = PppEvent::query()
            ->where('router_id', $router->id)
            ->where('event_type', 'disconnect')
            ->where('event_at', '>=', $today)
            ->orderBy('event_at')
            ->get(['username'])
            ->unique('username')
            ->values()
            ->reject(fn (PppEvent $item) => isset($activeNames[$item->username]))
            ->values();

        $disconnectedUsers = $disconnects->map(
            fn (PppEvent $item) => '- ' . $item->username
        )->implode("\n");

        if ($disconnectedUsers === '') {
            $disconnectedUsers = '-';
        }

        return
            'Jumlah Gangguan : ' . $disconnects->count() . 'x Terputus hari ini' . "\n" .
            'Total Secrets: ' . $totalSecrets . "\n" .
            'Total Active: ' . $totalActive . "\n" .
            'Offline Saat Ini (' . $disconnects->count() . "):\n" .
            $disconnectedUsers;
    }

    protected function formatDisconnectMessage(
        Router $router,
        array $event,
        ?Pelanggan $pelanggan = null,
        ?array $oltData = null
    ): string
    {
        $today = now()->startOfDay();

        // Ambil angka langsung dari MikroTik agar Telegram mencerminkan kondisi
        // PPP Secret dan PPP Active yang sebenarnya saat notifikasi dibuat.
        try {
            $totalSecrets = $this->mikrotik->getSecretCount($router);
            $totalActive = $this->mikrotik->getActiveCount($router);
        } catch (\Throwable $e) {
            report($e);
            $totalSecrets = Pelanggan::query()
                ->where('router_id', $router->id)
                ->whereNotNull('username_pppoe')
                ->where('username_pppoe', '!=', '')
                ->count();

            $totalActive = Pelanggan::query()
                ->where('router_id', $router->id)
                ->where('ppp_status', 'online')
                ->count();
        }

        // Daftar "disconnected" harus mencerminkan kondisi SEKARANG.
        // Jika user sempat disconnect lalu sudah online lagi, keluarkan dari daftar.
        $activeNames = [];
        try {
            foreach ($this->mikrotik->getActiveSessions($router) as $active) {
                $name = trim((string) ($active['name'] ?? ''));
                if ($name !== '') {
                    $activeNames[$name] = true;
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        $disconnects = PppEvent::query()
            ->where('router_id', $router->id)
            ->where('event_type', 'disconnect')
            ->where('event_at', '>=', $today)
            ->orderBy('event_at')
            ->get(['username'])
            ->unique('username')
            ->values()
            ->reject(fn (PppEvent $item) => isset($activeNames[$item->username]))
            ->values();

        $disconnectedUsers = $disconnects->map(
            fn (PppEvent $item) => '- ' . $item->username
        )->implode("\n");

        if ($disconnectedUsers === '') {
            $disconnectedUsers = '-';
        }

        $gangguan = $disconnects->count();

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
            'Jumlah Gangguan : ' . $gangguan . 'x Terputus hari ini' . "\n" .
            "====================\n" .
            'Total Secrets: ' . $totalSecrets . "\n" .
            'Total Active: ' . $totalActive . "\n" .
            'Offline Saat Ini (' . $gangguan . "):\n" .
            $disconnectedUsers;
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
