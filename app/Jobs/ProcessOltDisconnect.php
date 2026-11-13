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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\MikroTikService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ProcessOltDisconnect implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $timeout = 50;
    public array $backoff = [15, 30, 60];

    public function __construct(public int $pelangganId, public string $eventId)
    {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $lock = Cache::lock('olt:kuwu:notify-lock:' . $this->pelangganId, 90);
        $lock->block(10);

        try {
            $pendingKey = 'olt:kuwu:pending-down:' . $this->pelangganId;
            $notifiedKey = 'olt:kuwu:down-notified:' . $this->pelangganId;

            // Ada event UP atau DOWN yang lebih baru: job lama tidak boleh mengirim alarm.
            if (Cache::get($pendingKey) !== $this->eventId) {
                Log::info('Job DOWN KUWU dibatalkan karena event sudah berubah.', [
                    'pelanggan_id' => $this->pelangganId,
                    'event_id' => $this->eventId,
                ]);
                return;
            }

            if (Cache::has($notifiedKey)) {
                return;
            }

            $customer = Pelanggan::with(['router', 'paket'])->find($this->pelangganId);
            if (!$customer || !$customer->router ||
                strtoupper(trim((string) $customer->router->nama_router)) !== 'KUWU') {
                Log::warning('Job DOWN dibatalkan: pelanggan bukan router KUWU.', [
                    'pelanggan_id' => $this->pelangganId,
                ]);
                return;
            }

            // Pada DOWN yang sudah bertahan 30 detik, coba baca OLT tepat satu kali.
            // Ini berjalan di queue. Jika ONU sudah hilang, gunakan snapshot terakhir.
            $state = OltOnuState::where('pelanggan_id', $customer->id)->first();
            $snapshot = [
                'onu' => $state?->onu,
                'status' => null,
                'last_deregister_reason' => null,
                'olt_verified' => false,
                'distance' => $state?->distance,
                'rx_power' => $state?->rx_power,
                'tx_power' => $state?->tx_power,
                'last_checked_at' => $state?->last_checked_at,
            ];

            if (strtoupper(trim((string) ($customer->paket?->profile_mikrotik ?? ''))) === 'C20') {
                try {
                    $olt = app(OltService::class)->findByUsername(
                        $customer->router,
                        (string) $customer->username_pppoe,
                        $customer->mac_address
                    );

                    if (is_array($olt) && !empty($olt['onu'])) {
                        $snapshot = array_merge($snapshot, [
                            'onu' => $olt['onu'],
                            'status' => $olt['status'] ?? 'Unknown',
                            'last_deregister_reason' => $olt['last_deregister_reason'] ?? null,
                            'olt_verified' => true,
                            'distance' => $olt['distance'] ?? $snapshot['distance'],
                            'rx_power' => $olt['rx_power'] ?? $snapshot['rx_power'],
                            'tx_power' => $olt['tx_power'] ?? $snapshot['tx_power'],
                            'last_checked_at' => now(),
                        ]);

                        OltOnuState::updateOrCreate(
                            ['pelanggan_id' => $customer->id],
                            [
                                'router_id' => $customer->router_id,
                                'onu' => $snapshot['onu'],
                                'status' => $snapshot['status'],
                                'distance' => $snapshot['distance'],
                                'rx_power' => $snapshot['rx_power'],
                                'tx_power' => $snapshot['tx_power'],
                                'last_checked_at' => now(),
                            ]
                        );
                    } else {
                        Log::warning('ONU KUWU tidak ditemukan saat event PPP DOWN; memakai snapshot terakhir.', [
                            'pelanggan_id' => $customer->id,
                            'username' => $customer->username_pppoe,
                        ]);
                    }
                } catch (Throwable $oltError) {
                    Log::warning('Pembacaan OLT saat PPP DOWN gagal; memakai snapshot terakhir.', [
                        'pelanggan_id' => $customer->id,
                        'error' => $oltError->getMessage(),
                    ]);
                }
            }

            DB::table('ppp_disconnect_events')->insertOrIgnore([
                'event_id' => $this->eventId,
                'router_id' => $customer->router_id,
                'pelanggan_id' => $customer->id,
                'occurred_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $dailyCount = DB::table('ppp_disconnect_events')
                ->where('router_id', $customer->router_id)
                ->whereDate('occurred_at', today())
                ->count();

            $offlineCount = null;
            try {
                $eligible = Pelanggan::query()
                    ->where('router_id', $customer->router_id)
                    ->where('status', Pelanggan::AKTIF)
                    ->whereNotNull('username_pppoe')
                    ->where('username_pppoe', '<>', '')
                    ->whereHas('paket', fn ($q) => $q->whereRaw('UPPER(TRIM(profile_mikrotik)) = ?', ['C20']))
                    ->pluck('username_pppoe')
                    ->map(fn ($name) => trim((string) $name))
                    ->filter()
                    ->values();
                $activeNames = collect(app(MikroTikService::class)->getActiveSessions($customer->router))
                    ->pluck('name')->map(fn ($name) => trim((string) $name))->all();
                $offlineCount = $eligible->diff($activeNames)->count();
            } catch (Throwable $countError) {
                Log::warning('Gagal menghitung pelanggan offline KUWU C20.', [
                    'error' => $countError->getMessage(),
                ]);
            }

            if (!$this->sendTelegram($customer, $snapshot, $offlineCount, $dailyCount)) {
                throw new RuntimeException('Telegram gagal mengirim notifikasi PPP KUWU terputus.');
            }

            Cache::put($notifiedKey, [
                'event_id' => $this->eventId,
                'notified_at' => now()->toIso8601String(),
            ], now()->addDays(30));

            Log::info('Notifikasi PPP DOWN KUWU berhasil dikirim.', [
                'pelanggan_id' => $customer->id,
                'username' => $customer->username_pppoe,
                'event_id' => $this->eventId,
            ]);
        } catch (Throwable $e) {
            Log::warning('Job notifikasi PPP DOWN KUWU gagal.', [
                'pelanggan_id' => $this->pelangganId,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } finally {
            $lock->release();
        }
    }

    private function sendTelegram(Pelanggan $customer, array $snapshot, ?int $offlineCount, int $dailyCount): bool
    {
        $botToken = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (!(bool) config('services.telegram.enabled') || !filled($botToken) || !filled($chatId)) {
            Log::warning('Telegram notifikasi PPP KUWU belum dikonfigurasi lengkap.');
            return false;
        }

        $lines = [
            '🔴 <b>PELANGGAN PPP TERPUTUS</b>',
            '<b>Pelanggan:</b> ' . $this->escape((string) $customer->nama),
            '<b>Kode:</b> ' . $this->escape((string) ($customer->kode_pelanggan ?? '-')),
            '<b>Router:</b> KUWU',
            '<b>User:</b> ' . $this->escape((string) $customer->username_pppoe),
            '<b>Caller ID:</b> ' . $this->escape((string) ($customer->mac_address ?: '-')),
            '<b>Profile:</b> C20',
            '<b>Status:</b> PPP DISCONNECTED',
            '<b>ONU:</b> ' . $this->escape((string) ($snapshot['onu'] ?: '-')),
        ];

        if (!empty($snapshot['olt_verified'])) {
            $lines[] = '<b>Status ONU dari OLT:</b> ' . $this->escape((string) ($snapshot['status'] ?: 'Unknown'));
            if (!empty($snapshot['last_deregister_reason']) && $snapshot['last_deregister_reason'] !== '-') {
                $lines[] = '<b>Alasan deregister:</b> ' . $this->escape((string) $snapshot['last_deregister_reason']);
            }
        } else {
            $lines[] = '<b>Status ONU:</b> Tidak ditemukan / tidak dapat diverifikasi saat pemeriksaan OLT';
            $lines[] = '<i>LOS atau Wire Down belum dapat dipastikan.</i>';
        }

        if (!empty($snapshot['last_checked_at'])) {
            $checkedAt = $snapshot['last_checked_at'] instanceof \DateTimeInterface
                ? $snapshot['last_checked_at']->format('d-m-Y H:i:s')
                : (string) $snapshot['last_checked_at'];
            $lines[] = '<b>Data optik terakhir dibaca:</b> ' . $this->escape($checkedAt);
        }

        if (!empty($snapshot['distance'])) $lines[] = '<b>Jarak terakhir:</b> ' . $this->escape((string) $snapshot['distance']) . ' m';
        if (!empty($snapshot['rx_power'])) $lines[] = '<b>RX Power terakhir:</b> ' . $this->escape((string) $snapshot['rx_power']) . ' dBm';
        if (!empty($snapshot['tx_power'])) $lines[] = '<b>TX Power terakhir:</b> ' . $this->escape((string) $snapshot['tx_power']) . ' dBm';

        $lines[] = '<b>Jumlah Pelanggan Offline:</b> ' . ($offlineCount === null ? 'Tidak dapat dibaca' : $offlineCount);
        $lines[] = '<b>Jumlah Gangguan Hari Ini:</b> ' . $dailyCount . 'x';

        if (is_numeric($customer->latitude) && is_numeric($customer->longitude)) {
            $url = 'https://www.google.com/maps?q=' . rawurlencode($customer->latitude . ',' . $customer->longitude);
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
                Log::warning('Telegram gagal mengirim notifikasi PPP KUWU.', [
                    'http_status' => $response->status(),
                    'response' => $response->json(),
                ]);
                return false;
            }
            return true;
        } catch (Throwable $e) {
            Log::error('Koneksi Telegram notifikasi PPP KUWU gagal.', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
