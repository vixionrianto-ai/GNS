<?php

namespace App\Services;

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

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');

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
     */
    public function restore(UploadedFile $file): void
    {
        // Pastikan mysql.exe tersedia
        $mysql = env('MYSQL_PATH');

        if (empty($mysql) || !File::exists($mysql)) {
            throw new \RuntimeException(
                'MYSQL_PATH tidak ditemukan. Periksa file .env'
            );
        }

        // Backup database saat ini terlebih dahulu
        $this->create();

        // Simpan file upload sementara
        $tempFile = storage_path(
            'app/temp_restore_' . now()->format('YmdHis') . '.sql'
        );

        $file->move(
            dirname($tempFile),
            basename($tempFile)
        );

        // Konfigurasi database
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

        // Hapus file sementara
        if (File::exists($tempFile)) {
            File::delete($tempFile);
        }

        // Jika restore gagal
        if (! $process->isSuccessful()) {
            throw new \RuntimeException(
                $process->getErrorOutput() ?: $process->getOutput()
            );
        }
    }

}