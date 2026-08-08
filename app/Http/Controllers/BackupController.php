<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    /**
     * Service Backup.
     */
    protected BackupService $backupService;

    /**
     * Constructor.
     */
    public function __construct(
        BackupService $backupService
    ) {
        $this->backupService = $backupService;
    }

    /**
     * Halaman Backup.
     */
    public function index()
    {
        return view(
            'backup.index',
            $this->backupService->index()
        );
    }

    /**
     * Backup Database.
     */
    public function create()
    {
        $this->backupService->create();

        return redirect()
            ->route('backup.index')
            ->with(
                'success',
                'Backup database berhasil dibuat.'
            );
    }

    /**
     * Download Backup.
     */
    public function download(string $file)
    {
        return $this->backupService->download($file);
    }

    /**
     * Hapus Backup.
     */
    public function destroy(string $file)
    {
        $this->backupService->destroy($file);

        return redirect()
            ->route('backup.index')
            ->with(
                'success',
                'Backup berhasil dihapus.'
            );
    }

    /**
     * Restore database.
     */
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql',
        ]);

        $this->backupService->restore(
            $request->file('backup_file')
        );

        return redirect()
            ->route('backup.index')
            ->with(
                'success',
                'Restore database berhasil dilakukan.'
            );
    }

}