<?php

namespace App\Services;

use App\Models\OltOnuState;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Log;
use Throwable;

class OltMonitoringService
{
    public function __construct(private OltService $oltService)
    {
    }

    /**
     * Simpan snapshot optik terakhir khusus pelanggan paket C20 pada router KUWU.
     * Tidak mengirim alarm ONU terpisah dan tidak mengubah status PPP.
     */
    public function run(): array
    {
        $summary = ['checked' => 0, 'online' => 0, 'offline' => 0, 'unknown' => 0, 'errors' => 0];

        Pelanggan::query()
            ->with(['router', 'paket'])
            ->where('status', Pelanggan::AKTIF)
            ->whereNotNull('username_pppoe')
            ->where('username_pppoe', '<>', '')
            ->whereHas('router', function ($query): void {
                $query->whereRaw('UPPER(TRIM(nama_router)) = ?', ['KUWU']);
            })
            ->whereHas('paket', function ($query): void {
                $query->whereRaw('UPPER(TRIM(profile_mikrotik)) = ?', ['C20']);
            })
            ->orderBy('id')
            ->chunkById(50, function ($customers) use (&$summary): void {
                foreach ($customers as $customer) {
                    $router = $customer->router;

                    if (!$router || !$this->oltService->enabled($router)) {
                        continue;
                    }

                    try {
                        $onu = $this->oltService->findByUsername(
                            $router,
                            (string) $customer->username_pppoe,
                            $customer->mac_address
                        );

                        if (!is_array($onu)) {
                            $summary['unknown']++;
                            continue;
                        }

                        $existing = OltOnuState::query()
                            ->where('pelanggan_id', $customer->id)
                            ->first();

                        $onuName = $this->displayValue($onu['onu'] ?? null);
                        $distance = $this->displayValue($onu['distance'] ?? null);
                        $rxPower = $this->displayValue($onu['rx_power'] ?? null);
                        $txPower = $this->displayValue($onu['tx_power'] ?? null);

                        // Jangan menimpa snapshot valid dengan respons kosong/tidak lengkap.
                        if ($onuName === null && $distance === null && $rxPower === null && $txPower === null) {
                            $summary['unknown']++;
                            continue;
                        }

                        $normalizedStatus = $this->normalizeStatus((string) ($onu['status'] ?? ''));
                        $status = $normalizedStatus ?? $existing?->status ?? 'unknown';

                        OltOnuState::query()->updateOrCreate(
                            ['pelanggan_id' => $customer->id],
                            [
                                'router_id' => $router->id,
                                'onu' => $onuName ?? $existing?->onu,
                                'status' => $status,
                                'distance' => $distance ?? $existing?->distance,
                                'rx_power' => $rxPower ?? $existing?->rx_power,
                                'tx_power' => $txPower ?? $existing?->tx_power,
                                'last_checked_at' => now(),
                            ]
                        );

                        $summary['checked']++;
                        $summary[$status] = ($summary[$status] ?? 0) + 1;

                        Log::info('Snapshot optik C20 KUWU diperbarui.', [
                            'pelanggan_id' => $customer->id,
                            'username' => $customer->username_pppoe,
                            'onu' => $onuName ?? $existing?->onu,
                            'distance' => $distance ?? $existing?->distance,
                            'rx_power' => $rxPower ?? $existing?->rx_power,
                            'tx_power' => $txPower ?? $existing?->tx_power,
                            'status' => $status,
                        ]);
                    } catch (Throwable $e) {
                        $summary['errors']++;
                        Log::error('Gagal memperbarui snapshot optik C20 KUWU.', [
                            'pelanggan_id' => $customer->id,
                            'username' => $customer->username_pppoe,
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

    private function displayValue(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' || $value === '-' ? null : $value;
    }
}
