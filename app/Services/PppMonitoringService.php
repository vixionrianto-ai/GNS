<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Router;
use Illuminate\Support\Collection;
use Throwable;

class PppMonitoringService
{
    public function __construct(protected MikroTikService $mikrotik) {}

    public function syncRouter(Router $router): array
    {
        $pelanggans = Pelanggan::query()
            ->where('router_id', $router->id)
            ->whereNotNull('username_pppoe')
            ->where('username_pppoe', '!=', '')
            ->get();

        $checkedAt = now();

        try {
            $activeSessions = $this->mikrotik->getActiveSessions($router);
            $secretStatuses = $this->mikrotik->getSecretStatusMap($router);

            $activeByUsername = [];
            foreach ($activeSessions as $session) {
                $username = trim((string) ($session['name'] ?? ''));
                if ($username !== '') $activeByUsername[$username] = $session;
            }

            $online = $offline = $disabled = $unknown = 0;

            foreach ($pelanggans as $pelanggan) {
                $username = trim((string) $pelanggan->username_pppoe);

                if ($username === '') {
                    $this->updateStatus($pelanggan, 'unknown', $checkedAt);
                    $unknown++;
                    continue;
                }

                $secret = $secretStatuses[$username] ?? null;
                $isDisabled = $pelanggan->status !== Pelanggan::AKTIF
                    || (($secret['disabled'] ?? 'false') === 'true');

                if ($isDisabled) {
                    $status = 'disabled'; $disabled++;
                } elseif (isset($activeByUsername[$username])) {
                    $status = 'online'; $online++;
                } else {
                    $status = 'offline'; $offline++;
                }

                $session = $activeByUsername[$username] ?? null;
                $pelanggan->forceFill([
                    'ppp_status' => $status,
                    'ppp_ip_address' => $session['address'] ?? null,
                    'ppp_caller_id' => $session['caller-id'] ?? null,
                    'ppp_uptime' => $session['uptime'] ?? null,
                    'last_ppp_checked_at' => $checkedAt,
                ]);

                if ($pelanggan->isDirty('ppp_status')) {
                    $pelanggan->ppp_last_change_at = $checkedAt;
                }
                $pelanggan->save();
            }

            $router->forceFill(['is_online' => true, 'last_checked_at' => $checkedAt])->save();

            return compact('online', 'offline', 'disabled', 'unknown', 'checkedAt') + [
                'success' => true, 'router_status' => 'online',
            ];
        } catch (Throwable $e) {
            report($e);

            foreach ($pelanggans as $pelanggan) {
                $pelanggan->forceFill([
                    'ppp_status' => 'router_offline',
                    'ppp_ip_address' => null,
                    'ppp_caller_id' => null,
                    'ppp_uptime' => null,
                    'last_ppp_checked_at' => $checkedAt,
                ]);
                if ($pelanggan->isDirty('ppp_status')) $pelanggan->ppp_last_change_at = $checkedAt;
                $pelanggan->save();
            }

            $router->forceFill(['is_online' => false, 'last_checked_at' => $checkedAt])->save();

            return [
                'success' => false, 'router_status' => 'offline',
                'online' => 0, 'offline' => 0, 'disabled' => 0, 'unknown' => 0,
                'checked_at' => $checkedAt, 'error' => $e->getMessage(),
            ];
        }
    }

    public function syncAll(): Collection
    {
        return Router::query()->where('status', 'Aktif')->get()
            ->mapWithKeys(fn (Router $router) => [$router->id => $this->syncRouter($router)]);
    }

    protected function updateStatus(Pelanggan $pelanggan, string $status, $checkedAt): void
    {
        $pelanggan->forceFill([
            'ppp_status' => $status,
            'ppp_ip_address' => null,
            'ppp_caller_id' => null,
            'ppp_uptime' => null,
            'last_ppp_checked_at' => $checkedAt,
        ]);
        if ($pelanggan->isDirty('ppp_status')) $pelanggan->ppp_last_change_at = $checkedAt;
        $pelanggan->save();
    }
}
