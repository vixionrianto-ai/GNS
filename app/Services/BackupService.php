<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Paket;
use App\Models\Router;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\UploadedFile;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupService
{
    /**
     * Folder backup.
     */
    protected string $backupPath;

    protected MikroTikService $mikrotik;

    public function __construct(MikroTikService $mikrotik)
    {
        $this->backupPath = storage_path('app/backups');
        $this->mikrotik = $mikrotik;

        if (! File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    /**
     * Data halaman backup.
     */
    public function index(): array
    {
        $files = collect(File::files($this->backupPath))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(function ($file) {
                return [
                    'name' => $file->getFilename(),
                    'size' => round($file->getSize() / 1024 / 1024, 2),
                    'date' => date('d-m-Y H:i:s', $file->getMTime()),
                ];
            });

        return [
            'files' => $files,
        ];
    }

    /**
     * Membuat backup database.
     */
    public function create(): void
    {
        $filename = 'gns_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $filepath = $this->backupPath . DIRECTORY_SEPARATOR . $filename;

        $mysqldump = env('MYSQLDUMP_PATH');

        if (!$mysqldump || !File::exists($mysqldump)) {
            throw new \RuntimeException('MYSQLDUMP_PATH tidak ditemukan.');
        }

        $host = Config::get('database.connections.mysql.host');
        $port = Config::get('database.connections.mysql.port');
        $database = Config::get('database.connections.mysql.database');
        $username = Config::get('database.connections.mysql.username');
        $password = Config::get('database.connections.mysql.password');

        $arguments = [
            $mysqldump,
            "--host={$host}",
            "--port={$port}",
            "--user={$username}",
            "--skip-comments",
            "--result-file={$filepath}",
            $database,
        ];

        if (!empty($password)) {
            array_splice(
                $arguments,
                3,
                0,
                "--password={$password}"
            );
        }

        $process = new Process($arguments);
        $process->setTimeout(600);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * Download file backup.
     */
    public function download(string $file)
    {
        $path = $this->backupPath . DIRECTORY_SEPARATOR . $file;
        abort_unless(File::exists($path), 404);
        return Response::download($path);
    }

    /**
     * Hapus file backup.
     */
    public function destroy(string $file): void
    {
        $path = $this->backupPath . DIRECTORY_SEPARATOR . $file;
        if (File::exists($path)) {
            File::delete($path);
        }
    }

    /**
     * Restore database dari file SQL.
     * Setelah database berhasil dipulihkan, PPP Secret yang hilang
     * dari MikroTik dibuat kembali dari data pelanggan hasil restore.
     */
    public function restore(UploadedFile $file): void
    {
        $mysql = env('MYSQL_PATH');

        if (empty($mysql) || !File::exists($mysql)) {
            throw new \RuntimeException(
                'MYSQL_PATH tidak ditemukan. Periksa file .env'
            );
        }

        // Backup database saat ini terlebih dahulu.
        $this->create();

        $tempFile = storage_path(
            'app/temp_restore_' . now()->format('YmdHis') . '.sql'
        );

        $file->move(
            dirname($tempFile),
            basename($tempFile)
        );

        $host     = Config::get('database.connections.mysql.host');
        $port     = Config::get('database.connections.mysql.port');
        $database = Config::get('database.connections.mysql.database');
        $username = Config::get('database.connections.mysql.username');
        $password = Config::get('database.connections.mysql.password');

        $arguments = [
            $mysql,
            "--host={$host}",
            "--port={$port}",
            "--user={$username}",
        ];

        if (!empty($password)) {
            $arguments[] = "--password={$password}";
        }

        $arguments[] = $database;

        $process = new Process($arguments);
        $process->setInput(file_get_contents($tempFile));
        $process->setTimeout(600);
        $process->run();

        if (File::exists($tempFile)) {
            File::delete($tempFile);
        }

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(
                $process->getErrorOutput() ?: $process->getOutput()
            );
        }

        // Database sudah berhasil direstore. Sekarang pulihkan PPP Secret
        // yang hilang di setiap MikroTik berdasarkan data pelanggan hasil restore.
        $this->restoreMikrotikSecrets();
    }

    /**
     * Pulihkan PPP Secret pelanggan yang ada di database tetapi tidak ada
     * di MikroTik. Username menjadi kunci pencarian; mikrotik_secret_id
     * lama tidak dipercaya karena .id MikroTik dapat berubah setelah secret
     * dibuat ulang.
     */
    protected function restoreMikrotikSecrets(): void
    {
        $routers = Router::where('status', 'Aktif')->get();

        foreach ($routers as $router) {
            try {
                // Pastikan router dapat dihubungi sebelum memproses pelanggan.
                if (!$this->mikrotik->testConnection($router)) {
                    continue;
                }

                $secrets = $this->mikrotik->getSecrets($router);
                $existing = [];

                foreach ($secrets as $secret) {
                    if (!empty($secret['name'])) {
                        $existing[$secret['name']] = $secret;
                    }
                }

                $pelanggans = Pelanggan::where('router_id', $router->id)
                    ->whereNotNull('username_pppoe')
                    ->where('username_pppoe', '!=', '')
                    ->get();

                foreach ($pelanggans as $pelanggan) {
                    $username = trim((string) $pelanggan->username_pppoe);

                    if ($username === '') {
                        continue;
                    }

                    // Secret masih ada: cukup perbarui ID database jika berubah.
                    if (isset($existing[$username])) {
                        $secret = $existing[$username];
                        $newId = $secret['.id'] ?? null;

                        if ($newId && $pelanggan->mikrotik_secret_id !== $newId) {
                            $pelanggan->mikrotik_secret_id = $newId;
                            $pelanggan->save();
                        }

                        continue;
                    }

                    $password = (string) ($pelanggan->password_pppoe ?? '');
                    if ($password === '') {
                        continue;
                    }

                    $paket = $pelanggan->paket_id
                        ? Paket::find($pelanggan->paket_id)
                        : null;

                    $profile = trim((string) ($paket?->profile_mikrotik ?? ''));
                    if ($profile === '') {
                        continue;
                    }

                    try {
                        $secretId = $this->mikrotik->createSecret(
                            $router,
                            $username,
                            $password,
                            $profile,
                            'pppoe'
                        );

                        $pelanggan->mikrotik_secret_id = $secretId;
                        $pelanggan->save();

                        if (($pelanggan->status ?? '') === 'Aktif') {
                            $this->mikrotik->enableSecretById($router, $secretId);
                        } else {
                            $this->mikrotik->disableSecretById($router, $secretId);
                        }
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
