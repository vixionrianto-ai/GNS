<?php

namespace App\Console\Commands;

use App\Models\Router;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use RouterOS\Query;
use Throwable;

#[Signature('ppp:listen {--router= : ID router yang dipantau, default router aktif pertama}')]

#[Description('Mendengarkan event realtime PPP Active dari MikroTik melalui RouterOS API')]

class PppListenCommand extends Command
{
    public function handle(): int
    {
        $router = $this->resolveRouter();

        if (!$router) {
            $this->error('Router tidak ditemukan atau tidak aktif.');
            return self::FAILURE;
        }

        $this->info("PPP listener aktif: {$router->nama_router} ({$router->ip_router})");
        $this->line('Mode TEST: belum menyimpan event ke database dan belum mengirim Telegram.');
        $this->line('Silakan hapus satu PPP Active Connection di WinBox untuk menguji event DISCONNECT.');
        $this->line('Tekan Ctrl+C untuk berhenti.');
        $this->newLine();

        while (true) {
            try {
                $client = $this->createClient($router);

                $query = new Query('/ppp/active/listen');
                $query->equal('.proplist', '.id,name,address,caller-id,uptime,service,session-id');

                $client->query($query);

                $this->info('Terhubung ke RouterOS API, listener menunggu event...');

                while (true) {
                    // RouterOS /ppp/active/listen tidak mengirim !done selama
                    // listener aktif. count=1 membuat library mengembalikan
                    // satu blok !re sekaligus agar event dapat diproses.
                    $raw = $client->readRAW(['count' => 1]);

                    if (empty($raw)) {
                        continue;
                    }

                    $event = $client->parseResponse($raw);
                    $event = $event[0] ?? $event['after'] ?? [];

                    if (!is_array($event)) {
                        continue;
                    }

                    $name = trim((string) ($event['name'] ?? ''));
                    $dead = (($event['.dead'] ?? '') === 'yes');

                    if ($dead) {
                        $this->warn(sprintf(
                            '[%s] DISCONNECT | user=%s | id=%s',
                            now()->format('Y-m-d H:i:s'),
                            $name !== '' ? $name : '-',
                            $event['.id'] ?? '-'
                        ));
                    } else {
                        $this->line(sprintf(
                            '[%s] CONNECT/UPDATE | user=%s | ip=%s | caller=%s | uptime=%s | service=%s',
                            now()->format('Y-m-d H:i:s'),
                            $name !== '' ? $name : '-',
                            $event['address'] ?? '-',
                            $event['caller-id'] ?? '-',
                            $event['uptime'] ?? '-',
                            $event['service'] ?? '-'
                        ));
                    }
                }
            } catch (Throwable $e) {
                $this->warn('Listener terputus: ' . $e->getMessage());
                $this->line('Mencoba reconnect dalam 3 detik...');
                sleep(3);
            }
        }
    }

    private function resolveRouter(): ?Router
    {
        $id = $this->option('router');

        if ($id !== null && $id !== '') {
            return Router::query()
                ->whereKey((int) $id)
                ->where('status', 'Aktif')
                ->first();
        }

        return Router::query()
            ->where('status', 'Aktif')
            ->orderBy('id')
            ->first();
    }

    private function createClient(Router $router): \RouterOS\Client
    {
        return new \RouterOS\Client(new \RouterOS\Config([
            'host' => trim((string) $router->ip_router),
            'user' => (string) $router->username,
            'pass' => (string) $router->password,
            'port' => (int) $router->api_port,
            'ssl' => (bool) $router->ssl,
            'timeout' => 10,

            // Listener memang harus menunggu lama ketika tidak ada event.
            // Library RouterOS API-php default-nya 30 detik, yang membuat
            // listener terlihat "putus" padahal router masih sehat.
            // 24 jam hanya menjadi batas baca; koneksi TCP yang benar-benar
            // putus tetap akan masuk ke blok reconnect.
            'socket_timeout' => 86400,

            'attempts' => 2,
            'delay' => 1,
        ]));
    }
}
