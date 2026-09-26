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

#[Signature('ppp:listen {--router= : ID router yang dipantau} {--worker : Jalankan satu listener router sebagai worker internal} {--interval=5 : Interval polling PPP Active dalam detik}')]
#[Description('Memantau perubahan PPP Active dari seluruh MikroTik aktif menggunakan snapshot polling')]
class PppListenCommand extends Command
{
    /** @var array<int, Process> */
    private array $workers = [];

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

        $this->info('PPP monitoring GNS aktif.');
        $this->line('Semua router MikroTik dengan status Aktif akan dipantau otomatis.');
        $this->line('Monitoring menggunakan snapshot /ppp/active/print, tanpa /ppp/active/listen.');
        $this->line('Setiap polling membuat koneksi API baru agar tidak mempertahankan stream API terbuka.');
        $this->line('Event PPP disimpan ke database dan disconnect baru dikirim ke Telegram.');
        $this->line('Tekan Ctrl+C untuk berhenti.');
        $this->newLine();

        try {
            while (true) {
                $activeRouters = Router::query()->where('status', 'Aktif')->orderBy('id')->get();
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
                        $this->warn("Menghentikan monitoring router ID {$id} karena status router tidak Aktif.");
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
        $interval = max(1, (int) ($this->option('interval') ?: 5));

        $worker = new Process([
            PHP_BINARY,
            base_path('artisan'),
            'ppp:listen',
            '--router=' . $id,
            '--worker',
            '--interval=' . $interval,
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
        $this->info("Monitoring dimulai: {$router->nama_router} (ID {$id})");
    }

    private function drainWorkerOutput(): void
    {
        foreach ($this->workers as $id => $worker) {
            $output = trim($worker->getIncrementalOutput());
            if ($output !== '') {
                $router = Router::find($id);
                $prefix = '[' . ($router?->nama_router ?? "Router {$id}") . '] ';
                foreach (preg_split('/\r\n|\r|\n/', $output) as $line) {
                    if ($line !== '') {
                        $this->output->writeln($prefix . $line);
                    }
                }
            }

            $error = trim($worker->getIncrementalErrorOutput());
            if ($error !== '') {
                $router = Router::find($id);
                $prefix = '[' . ($router?->nama_router ?? "Router {$id}") . '] ';
                foreach (preg_split('/\r\n|\r|\n/', $error) as $line) {
                    if ($line !== '') {
                        $this->output->writeln($prefix . $line);
                    }
                }
            }
        }
    }

    private function listenRouter(Router $router, PppEventService $eventService): int
    {
        $interval = max(1, (int) ($this->option('interval') ?: 5));
        $previousItems = null;

        $this->info("Monitoring aktif: {$router->nama_router} ({$router->ip_router})");
        $this->line("Metode: /ppp/active/print setiap {$interval} detik.");
        $this->line('Koneksi API dibuat ulang pada setiap polling.');

        while (true) {
            try {
                $client = $this->createClient($router);
                $snapshot = $this->readActiveSnapshot($client);
                unset($client);

                if ($previousItems === null) {
                    $previousItems = $snapshot;
                    $this->info(sprintf('Baseline PPP Active: %d user.', count($snapshot)));
                } else {
                    $this->processSnapshotChanges($router, $eventService, $previousItems, $snapshot);
                    $previousItems = $snapshot;
                }

                sleep($interval);
            } catch (Throwable $e) {
                $this->warn('Polling terputus: ' . $e->getMessage());
                $this->line('Mencoba polling ulang dalam 3 detik...');
                $previousItems = null;
                sleep(3);
            }
        }
    }

    /** @return array<string, array<string, mixed>> */
    private function readActiveSnapshot(\RouterOS\Client $client): array
    {
        $query = new Query('/ppp/active/print');
        $query->equal('.proplist', '.id,name,address,caller-id,uptime,service,session-id');
        $rows = $client->query($query)->read();
        $snapshot = [];

        foreach ($rows as $item) {
            if (!is_array($item)) {
                continue;
            }
            $itemId = trim((string) ($item['.id'] ?? ''));
            $itemName = trim((string) ($item['name'] ?? ''));
            if ($itemId !== '' && $itemName !== '') {
                $snapshot[$itemId] = $item;
            }
        }

        return $snapshot;
    }

    /** @param array<string, array<string, mixed>> $previousItems
     * @param array<string, array<string, mixed>> $currentItems */
    private function processSnapshotChanges(
        Router $router,
        PppEventService $eventService,
        array $previousItems,
        array $currentItems
    ): void {
        foreach ($currentItems as $itemId => $item) {
            if (!isset($previousItems[$itemId])) {
                $name = trim((string) ($item['name'] ?? ''));
                $record = $eventService->handle($router, $item);
                $this->line(sprintf(
                    'CONNECT/UPDATE | user=%s | ip=%s | caller=%s | uptime=%s | event_id=%s',
                    $name !== '' ? $name : '-',
                    $item['address'] ?? '-',
                    $item['caller-id'] ?? '-',
                    $item['uptime'] ?? '-',
                    $record?->id ?? '-'
                ));
            }
        }

        foreach ($previousItems as $itemId => $item) {
            if (isset($currentItems[$itemId])) {
                continue;
            }
            $event = $item;
            $event['.dead'] = 'yes';
            $name = trim((string) ($event['name'] ?? ''));
            $record = $eventService->handle($router, $event);
            $this->warn(sprintf(
                'DISCONNECT | user=%s | event_id=%s',
                $name !== '' ? $name : '-',
                $record?->id ?? '-'
            ));
        }
    }

    private function resolveRouter(): ?Router
    {
        $id = $this->option('router');
        if ($id !== null && $id !== '') {
            return Router::query()->whereKey((int) $id)->where('status', 'Aktif')->first();
        }
        return Router::query()->where('status', 'Aktif')->orderBy('id')->first();
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
            'socket_timeout' => 10,
            'attempts' => 2,
            'delay' => 1,
        ]));
    }
}
