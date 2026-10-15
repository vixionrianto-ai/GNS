<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OltOnuState;
use App\Models\Pelanggan;
use App\Models\Router;
use App\Services\OltService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class OltEventController extends Controller
{
    public function __invoke(Request $request, OltService $oltService)
    {
        $expectedToken = (string) config('services.olt_event.token', '');
        $providedToken = (string) $request->header('X-OLT-Event-Token', '');

        if ($expectedToken === '' || $providedToken === '' || !hash_equals($expectedToken, $providedToken)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $validated = $request->validate([
            'router' => ['required', 'string', 'max:80'],
            'username' => ['required', 'string', 'max:191'],
            'event' => ['required', 'in:down,up'],
        ]);

        $router = Router::query()
            ->whereRaw('UPPER(nama_router) = ?', [mb_strtoupper(trim($validated['router']))])
            ->first();

        if (!$router) {
            return response()->json(['ok' => false, 'message' => 'Router tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $customer = Pelanggan::query()
            ->with('router')
            ->where('router_id', $router->id)
            ->where('username_pppoe', $validated['username'])
            ->where('status', Pelanggan::AKTIF)
            ->first();

        if (!$customer || !$customer->router) {
            return response()->json(['ok' => false, 'message' => 'Pelanggan aktif tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $router = $customer->router;

        try {
            $onu = $oltService->findByUsername(
                $router,
                (string) $customer->username_pppoe,
                $customer->mac_address
            );
        } catch (Throwable $e) {
            Log::error('Pemicu event OLT gagal membaca ONU.', [
                'router' => $router->nama_router,
                'pelanggan_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['ok' => false, 'message' => 'Pembacaan OLT gagal'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        if (!is_array($onu) || !isset($onu['status'])) {
            return response()->json(['ok' => false, 'message' => 'Status ONU tidak berhasil dibaca; notifikasi dilewati'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $status = $this->normalizeStatus((string) $onu['status']);
        if ($status === null) {
            return response()->json(['ok' => false, 'message' => 'Status ONU tidak dikenal; notifikasi dilewati'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $state = OltOnuState::query()->where('pelanggan_id', $customer->id)->first();
        $oldStatus = $state?->status;

        // First observation establishes a baseline; it must not trigger an alert.
        // Notify only on a verified transition from a known opposite state.
        $shouldNotify = ($oldStatus === 'online' && $status === 'offline')
            || ($oldStatus === 'offline' && $status === 'online' && $validated['event'] === 'up');

        $snapshot = [
            'router_id' => $router->id,
            'onu' => $onu['onu'] ?? null,
            'status' => $status,
            'distance' => $this->displayValue($onu['distance'] ?? null),
            'rx_power' => $this->displayValue($onu['rx_power'] ?? null),
            'tx_power' => $this->displayValue($onu['tx_power'] ?? null),
            'last_checked_at' => now(),
        ];

        if ($shouldNotify && !$this->sendTelegram($customer, (string) $router->nama_router, $snapshot)) {
            return response()->json(['ok' => false, 'message' => 'Status terbaca tetapi Telegram gagal dikirim'], Response::HTTP_BAD_GATEWAY);
        }

        OltOnuState::query()->updateOrCreate(['pelanggan_id' => $customer->id], $snapshot);

        return response()->json([
            'ok' => true,
            'customer' => $customer->nama,
            'event' => $validated['event'],
            'onu_status' => $status,
            'notified' => $shouldNotify,
        ]);
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

    private function sendTelegram(Pelanggan $customer, string $routerName, array $snapshot): bool
    {
        if (!(bool) config('services.telegram.enabled')
            || !filled(config('services.telegram.bot_token'))
            || !filled(config('services.telegram.chat_id'))) {
            Log::warning('Notifikasi event OLT tidak dikirim karena konfigurasi Telegram belum lengkap.');
            return false;
        }

        $offline = $snapshot['status'] === 'offline';
        $lines = [
            $offline ? '🔴 <b>PELANGGAN OLT PUTUS</b>' : '🟢 <b>ONU ONLINE KEMBALI</b>',
            '<b>Pelanggan:</b> ' . $this->escape((string) $customer->nama),
            '<b>Kode:</b> ' . $this->escape((string) ($customer->kode_pelanggan ?? '-')),
            '<b>Router:</b> ' . $this->escape($routerName),
            '<b>Username:</b> ' . $this->escape((string) $customer->username_pppoe),
            '<b>ONU:</b> ' . $this->escape((string) ($snapshot['onu'] ?? '-')),
            '<b>Status ONU:</b> ' . strtoupper($this->escape((string) $snapshot['status'])),
        ];

        if ($snapshot['distance'] !== null) {
            $lines[] = '<b>Jarak:</b> ' . $this->escape((string) $snapshot['distance']) . ' m';
        }
        if ($snapshot['rx_power'] !== null) {
            $lines[] = '<b>RX Power:</b> ' . $this->escape((string) $snapshot['rx_power']) . ' dBm';
        }
        if ($snapshot['tx_power'] !== null) {
            $lines[] = '<b>TX Power:</b> ' . $this->escape((string) $snapshot['tx_power']) . ' dBm';
        }

        if (is_numeric($customer->latitude) && is_numeric($customer->longitude)) {
            $url = 'https://www.google.com/maps?q=' . rawurlencode((string) $customer->latitude . ',' . (string) $customer->longitude);
            $lines[] = '<a href="' . $this->escape($url) . '">📍 Buka lokasi pelanggan</a>';
        }

        try {
            $response = Http::asForm()->timeout(10)->post(
                'https://api.telegram.org/bot' . config('services.telegram.bot_token') . '/sendMessage',
                [
                    'chat_id' => config('services.telegram.chat_id'),
                    'text' => implode("\n", $lines),
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]
            );

            if (!$response->successful() || !$response->json('ok')) {
                Log::warning('Telegram gagal mengirim event OLT.', [
                    'http_status' => $response->status(),
                    'response' => $response->json(),
                ]);
                return false;
            }

            return true;
        } catch (Throwable $e) {
            Log::error('Koneksi Telegram gagal pada event OLT.', ['error' => $e->getMessage()]);
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
