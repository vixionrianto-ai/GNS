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
        protected OltService $olt
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

        // Telegram hanya sekali untuk event disconnect yang benar-benar baru.
        if ($eventType === 'disconnect' && $record->wasRecentlyCreated) {
            $oltData = $this->olt->findByUsername($username);

            $this->telegram->send(
                $this->formatDisconnectMessage($router, $event, $pelanggan, $oltData)
            );
        }

        return $record;
    }

    protected function formatDisconnectMessage(
        Router $router,
        array $event,
        ?Pelanggan $pelanggan = null,
        ?array $oltData = null
    ): string
    {
        $today = now()->startOfDay();

        $totalSecrets = Pelanggan::query()
            ->where('router_id', $router->id)
            ->whereNotNull('username_pppoe')
            ->where('username_pppoe', '!=', '')
            ->count();

        $totalActive = Pelanggan::query()
            ->where('router_id', $router->id)
            ->where('ppp_status', 'online')
            ->count();

        $disconnects = PppEvent::query()
            ->where('router_id', $router->id)
            ->where('event_type', 'disconnect')
            ->where('event_at', '>=', $today)
            ->orderBy('event_at')
            ->get(['username'])
            ->unique('username')
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
            'Last Degerasi Reason: ' . ($oltData['last_deregister_reason'] ?? '-') . "\n\n" .
            'Jumlah Gangguan : ' . $gangguan . 'x Terputus hari ini' . "\n" .
            "====================\n" .
            'Total Secrets: ' . $totalSecrets . "\n" .
            'Total Active: ' . $totalActive . "\n" .
            'Disconnected Users (' . $gangguan . "):\n" .
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
