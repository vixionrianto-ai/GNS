<?php

namespace App\Console\Commands;

use App\Models\OltOnuState;
use App\Models\Pelanggan;
use App\Models\Router;
use App\Services\OltService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class OltKuwuBaselineCommand extends Command
{
    protected $signature = 'olt:kuwu-baseline';
    protected $description = 'Perbarui baseline status ONU KUWU tanpa mengirim notifikasi';

    public function handle(OltService $oltService): int
    {
        $router = Router::query()
            ->whereRaw('UPPER(nama_router) = ?', ['KUWU'])
            ->first();

        if (!$router || !$oltService->enabled($router)) {
            $this->error('Router KUWU tidak ditemukan atau konfigurasi OLT belum aktif.');
            return self::FAILURE;
        }

        try {
            // Satu login dan satu pembacaan PON 1-4 untuk seluruh pelanggan KUWU.
            $onus = $oltService->getAllKuwuOnus($router);
            if ($onus === []) {
                $this->error('Daftar ONU KUWU kosong/gagal dibaca; baseline tidak diubah.');
                return self::FAILURE;
            }

            $customers = Pelanggan::query()
                ->where('router_id', $router->id)
                ->where('status', Pelanggan::AKTIF)
                ->whereNotNull('username_pppoe')
                ->where('username_pppoe', '<>', '')
                ->get();

            $indexed = [];
            foreach ($onus as $onu) {
                $description = mb_strtolower(trim((string) ($onu['description'] ?? '')));
                $mac = $this->normalizeMac((string) ($onu['mac'] ?? ''));
                if ($description !== '') {
                    $indexed['desc:' . $description] = $onu;
                }
                if ($mac !== '') {
                    $indexed['mac:' . $mac] = $onu;
                }
            }

            $updated = 0;
            $unknown = 0;
            foreach ($customers as $customer) {
                $username = mb_strtolower(trim((string) $customer->username_pppoe));
                $mac = $this->normalizeMac((string) $customer->mac_address);
                $onu = ($username !== '' ? ($indexed['desc:' . $username] ?? null) : null)
                    ?? ($mac !== '' ? ($indexed['mac:' . $mac] ?? null) : null);

                if (!$onu || empty($onu['onu'])) {
                    $unknown++;
                    continue;
                }

                $status = $this->normalizeStatus((string) ($onu['status'] ?? ''));
                if ($status === null) {
                    $unknown++;
                    continue;
                }

                OltOnuState::query()->updateOrCreate(
                    ['pelanggan_id' => $customer->id],
                    [
                        'router_id' => $router->id,
                        'onu' => $onu['onu'],
                        'status' => $status,
                        'distance' => $this->displayValue($onu['distance'] ?? null),
                        'rx_power' => $this->displayValue($onu['rx_power'] ?? null),
                        'tx_power' => $this->displayValue($onu['tx_power'] ?? null),
                        'last_checked_at' => now(),
                    ]
                );
                $updated++;
            }

            $summary = "KUWU baseline diperbarui: {$updated} pelanggan; tidak cocok/tidak dikenal: {$unknown}; ONU terbaca: " . count($onus);
            $this->info($summary);
            Log::info($summary);
            return self::SUCCESS;
        } catch (Throwable $e) {
            Log::error('Pembaruan baseline KUWU gagal.', ['error' => $e->getMessage()]);
            $this->error('Baseline KUWU gagal: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function normalizeStatus(string $raw): ?string
    {
        $value = mb_strtolower(trim($raw));
        if ($value === '') return null;
        if (preg_match('/los|offline|down|dying.?gasp|loss|disable|inactive|unregistered|failed|power.?off/', $value)) return 'offline';
        if (preg_match('/online|working|normal|up|enable|active|registered|operational/', $value)) return 'online';
        return null;
    }

    private function normalizeMac(string $mac): string
    {
        return strtoupper(preg_replace('/[^A-Fa-f0-9]/', '', $mac) ?? '');
    }

    private function displayValue(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' || $value === '-' ? null : $value;
    }
}
