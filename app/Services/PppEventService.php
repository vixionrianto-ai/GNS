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

        // Satu session PPP = satu event connect/disconnect.
        // Event /ppp/active/listen juga dapat mengirim update dari session
        // yang sama, jadi jangan membuat event/Telegram baru hanya karena
        // address atau caller-id berubah.
        $fingerprint = implode('|', [
            $router->id,
            $eventType,
            $sessionId !== '' ? $sessionId : $routerItemId,
            $username,
        ]);
        $eventKey = hash('sha256', $fingerprint);

        $pelanggan = Pelanggan::query()
            ->where('router_id', $router->id)
            ->where('username_pppoe', $username)
            ->first();

        // Simpan status sebelum event. Ini dipakai untuk mendeteksi
        // perubahan nyata OFFLINE -> ONLINE atau ONLINE -> OFFLINE.
        $previousPppStatus = $pelanggan?->ppp_status;

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

        // Telegram hanya dikirim saat status pelanggan benar-benar berubah.
        // Tidak bergantung pada wasRecentlyCreated karena event RouterOS
        // dapat memiliki event_key yang sudah pernah tersimpan.
        $statusChanged = $eventType === 'disconnect'
            ? $previousPppStatus !== 'offline'
            : $previousPppStatus !== 'online';

        if ($statusChanged) {
            if ($eventType === 'disconnect') {
                $oltData = $this->olt->findByUsername($username, $event['caller-id'] ?? null);

                $this->telegram->send(
                    $this->formatDisconnectMessage($router, $event, $pelanggan, $oltData)
                );
            } else {
                $this->telegram->send(
                    $this->formatConnectMessage($router, $event, $pelanggan)
                );
            }
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

        $gangguan = PppEvent::query()
            ->where('router_id', $router->id)
            ->where('event_type', 'disconnect')
            ->where('event_at', '>=', $today)
            ->count();

        $disconnectedUsers = $this->getCurrentOfflineUsers($router);

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
            'Offline Saat Ini (' . $this->countCurrentOfflineUsers($router) . "):\n" .
            $disconnectedUsers;
    }

    protected function formatConnectMessage(
        Router $router,
        array $event,
        ?Pelanggan $pelanggan = null
    ): string
    {
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

        $gangguan = PppEvent::query()
            ->where('router_id', $router->id)
            ->where('event_type', 'disconnect')
            ->where('event_at', '>=', now()->startOfDay())
            ->count();

        $offlineUsers = $this->getCurrentOfflineUsers($router);
        $offlineCount = $this->countCurrentOfflineUsers($router);

        return
            'ONLINE ' . $router->nama_router . "\n" .
            "====================\n" .
            'Time: ' . now()->format('Y-m-d H:i:s') . "\n" .
            "====================\n" .
            'User: ' . ($event['name'] ?? '-') . "\n" .
            'IP Client: ' . ($event['address'] ?? '-') . "\n" .
            'Caller ID: ' . ($event['caller-id'] ?? '-') . "\n" .
            'Profile: ' . ($pelanggan?->paket?->profile_mikrotik ?? $event['profile'] ?? '-') . "\n\n" .
            'Jumlah Gangguan : ' . $gangguan . 'x Terputus hari ini' . "\n" .
            "====================\n" .
            'Total Secrets: ' . $totalSecrets . "\n" .
            'Total Active: ' . $totalActive . "\n" .
            'Offline Saat Ini (' . $offlineCount . "):\n" .
            $offlineUsers;
    }

    protected function getCurrentOfflineUsers(Router $router): string
    {
        // Offline saat ini dihitung langsung dari MikroTik:
        // PPP Secret yang tidak mempunyai session PPP Active.
        // Jadi tidak tergantung apakah user pernah disconnect hari ini.
        try {
            $secrets = $this->mikrotik->getSecretStatusMap($router);
            $activeNames = [];

            foreach ($this->mikrotik->getActiveSessions($router) as $active) {
                $name = trim((string) ($active['name'] ?? ''));
                if ($name !== '') {
                    $activeNames[strtolower($name)] = true;
                }
            }

            $offline = [];

            foreach ($secrets as $name => $secret) {
                $username = trim((string) $name);
                if ($username === '') {
                    continue;
                }

                // Secret disabled bukan pelanggan aktif yang sedang offline.
                if (($secret['disabled'] ?? '') === 'true' || ($secret['disabled'] ?? '') === 'yes') {
                    continue;
                }

                if (!isset($activeNames[strtolower($username)])) {
                    $offline[] = $username;
                }
            }

            sort($offline, SORT_NATURAL | SORT_FLAG_CASE);

            return empty($offline)
                ? '-'
                : collect($offline)->map(fn ($name) => '- ' . $name)->implode("\\n");
        } catch (\\Throwable $e) {
            report($e);
            return '-';
        }
    }

    protected function countCurrentOfflineUsers(Router $router): int
    {
        $list = $this->getCurrentOfflineUsers($router);

        return $list === '-' ? 0 : substr_count($list, "\n") + 1;
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
