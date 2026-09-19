<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Services\PppEventService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use RouterOS\Query;
use Symfony\Component\Process\Process;
use Throwable;

#[Signature('ppp:listen {--router= : ID router yang dipantau} {--worker : Jalankan satu listener router sebagai worker internal}')]

#[Description('Mendengarkan event realtime PPP Active dari seluruh MikroTik aktif')]

class PppListenCommand extends Command
{
    /** @var array<int, Process> */
    private array $workers = [];

    /** @var array<string, array<string, mixed>> */
    private array $knownPppItems = [];

    public function handle(PppEventService $eventService): int
    {
        if ($this->option('worker')) {
            $router = $this->resolveRouter();

            if (!$router) {
                $this->error('Router tidak ditemukan atau tidak aktif.');
                return self::FAILURE;
            }

            return $this->listenRouter($router, $eventService);
        }

        $this->info('PPP realtime listener GNS aktif.');
        $this->line('Semua router MikroTik dengan status Aktif akan dipantau otomatis.');
        $this->line('Event PPP disimpan ke database dan disconnect baru dikirim ke Telegram.');
        $this->line('Tekan Ctrl+C untuk berhenti.');
        $this->newLine();

        try {
            while (true) {
                $activeRouters = Router::query()
                    ->where('status', 'Aktif')
                    ->orderBy('id')
                    ->get();

                $activeIds = $activeRouters->pluck('id')->map(fn ($id) => (int) $id)->all();

                foreach ($activeRouters as $router) {
                    $id = (int) $router->id;

                    if (isset($this->workers[$id]) && $this->workers[$id]->isRunning()) {
                        continue;
                    }

                    $this->startWorker($router);
                }

                foreach ($this->workers as $id => $worker) {
                    if (!in_array($id, $activeIds, true) && $worker->isRunning()) {
                        $this->warn("Menghentikan listener router ID {$id} karena status router tidak Aktif.");
                        $worker->stop(3);
                    }

                    if (!$worker->isRunning()) {
                        unset($this->workers[$id]);
                    }
                }

                if ($activeRouters->isEmpty()) {
                    $this->warn('Belum ada router aktif. Cek lagi dalam 10 detik...');
                }

                $this->drainWorkerOutput();
                sleep(1);
            }
        } finally {
            foreach ($this->workers as $worker) {
                if ($worker->isRunning()) {
                    $worker->stop(3);
                }
            }
        }
    }

    private function startWorker(Router $router): void
    {
        $id = (int) $router->id;

        $worker = new Process([
            PHP_BINARY,
            base_path('artisan'),
            'ppp:listen',
            '--router=' . $id,
            '--worker',
        ], base_path());

        $worker->setTimeout(null);
        $worker->start(function (string $type, string $buffer) use ($router): void {
            $prefix = '[' . $router->nama_router . '] ';
            foreach (preg_split('/\r\n|\r|\n/', trim($buffer)) as $line) {
                if ($line !== '') {
                    $this->output->writeln($prefix . $line);
                }
            }
        });

        $this->workers[$id] = $worker;
        $this->info("Listener dimulai: {$router->nama_router} (ID {$id})");
    }

    private function drainWorkerOutput(): void
    {
        foreach ($this->workers as $id => $worker) {
            $output = trim($worker->getIncrementalOutput());
            if ($output !== '') {
                $router = Router::find($id);
                $prefix = '[' . ($router?->nama_router ?? "Router {$id}") . '] ';
                foreach (preg_split('/\\r\\n|\\r|\\n/', $output) as $line) {
                    if ($line !== '') {
                        $this->output->writeln($prefix . $line);
                    }
                }
            }

            $error = trim($worker->getIncrementalErrorOutput());
            if ($error !== '') {
                $router = Router::find($id);
                $prefix = '[' . ($router?->nama_router ?? "Router {$id}") . '] ';
                foreach (preg_split('/\\r\\n|\\r|\\n/', $error) as $line) {
                    if ($line !== '') {
                        $this->output->writeln($prefix . $line);
                    }
                }
            }
        }
    }

    private function listenRouter(Router $router, PppEventService $eventService): int
    {
        $this->info("Listener aktif: {$router->nama_router} ({$router->ip_router})");

        while (true) {
            try {
                $client = $this->createClient($router);

                // Ambil snapshot PPP Active yang sudah terhubung.
                // Event .dead dari RouterOS hanya membawa .id, sehingga
                // snapshot diperlukan untuk mengetahui username saat disconnect.
                $snapshotQuery = new Query('/ppp/active/print');
                $snapshotQuery->equal('.proplist', '.id,name,address,caller-id,uptime,service,session-id');

                $snapshot = $client->query($snapshotQuery)->read();

                foreach ($snapshot as $item) {
                    if (!is_array($item)) {
                        continue;
                    }

                    $itemId = trim((string) ($item['.id'] ?? ''));
                    $itemName = trim((string) ($item['name'] ?? ''));

                    if ($itemId !== '' && $itemName !== '') {
                        $this->knownPppItems[$itemId] = $item;
                    }
                }

                $query = new Query('/ppp/active/listen');
                $query->equal('.proplist', '.id,.dead,name,address,caller-id,uptime,service,session-id');

                // Setelah snapshot tersimpan, baru pasang listener realtime.
                $client->query($query);

                $this->info('Terhubung ke RouterOS API, menunggu event...');

                while (true) {
                    $raw = $client->readRAW(['count' => 1]);

                    if (empty($raw)) {
                        continue;
                    }

                    // RouterOS dapat mengirim event item yang hilang sebagai
                    // =.dead=yes atau =.dead=true. Deteksi langsung dari RAW.
                    $dead = false;
                    foreach ($raw as $word) {
                        if ($word === '=.dead=yes' || $word === '=.dead=true') {
                            $dead = true;
                            break;
                        }
                    }

                    $parsed = $client->parseResponse($raw);
                    $event = $parsed[0] ?? $parsed['after'] ?? [];

                    if (!is_array($event)) {
                        $event = [];
                    }

                    $itemId = trim((string) ($event['.id'] ?? ''));

                    if ($dead) {
                        // Normalisasi agar PppEventService memakai satu format.
                        $event['.dead'] = 'yes';

                        // Event delete RouterOS biasanya hanya membawa .id + .dead.
                        // Gabungkan dengan data item yang disimpan dari snapshot / update.
                        if ($itemId !== '' && isset($this->knownPppItems[$itemId])) {
                            $event = array_merge($this->knownPppItems[$itemId], $event);
                        }
                    } elseif ($itemId !== '' && !empty($event['name'])) {
                        $this->knownPppItems[$itemId] = $event;
                    }

                    $name = trim((string) ($event['name'] ?? ''));

                    $record = $eventService->handle($router, $event);

                    if ($dead && $itemId !== '') {
                        unset($this->knownPppItems[$itemId]);
                    }

                    if ($dead) {
                        $this->warn(sprintf(
                            '[%s] DISCONNECT | user=%s | event_id=%s',
                            now()->format('Y-m-d H:i:s'),
                            $name !== '' ? $name : '-',
                            $record?->id ?? '-'
                        ));
                    } else {
                        $this->line(sprintf(
                            '[%s] CONNECT/UPDATE | user=%s | ip=%s | caller=%s | uptime=%s | event_id=%s',
                            now()->format('Y-m-d H:i:s'),
                            $name !== '' ? $name : '-',
                            $event['address'] ?? '-',
                            $event['caller-id'] ?? '-',
                            $event['uptime'] ?? '-',
                            $record?->id ?? '-'
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
            'socket_timeout' => 86400,
            'attempts' => 2,
            'delay' => 1,
        ]));
    }
}
