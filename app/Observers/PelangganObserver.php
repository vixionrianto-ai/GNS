<?php

namespace App\Observers;

use App\Models\Pelanggan;
use App\Services\AuditTrailService;

class PelangganObserver
{
    protected AuditTrailService $auditTrail;

    public function __construct(
        AuditTrailService $auditTrail
    ) {
        $this->auditTrail = $auditTrail;
    }
    
    /**
     * Setelah data pelanggan dibuat.
     */
    public function created(Pelanggan $pelanggan): void
    {
        $this->auditTrail->pelanggan(
            'create',
            'Menambah pelanggan: ' . $pelanggan->nama,
            [
                'pelanggan_id' => $pelanggan->id,
                'nama'         => $pelanggan->nama,
            ]
        );
    }

    /**
     * Setelah data pelanggan diubah.
     */
    public function updated(Pelanggan $pelanggan): void
    {
        $this->auditTrail->pelanggan(
            'update',
            'Mengubah pelanggan: ' . $pelanggan->nama,
            [
                'pelanggan_id' => $pelanggan->id,
                'nama'         => $pelanggan->nama,
            ]
        );
    }

    /**
     * Setelah data pelanggan dihapus.
     */
    public function deleted(Pelanggan $pelanggan): void
    {
        $this->auditTrail->pelanggan(
            'delete',
            'Menghapus pelanggan: ' . $pelanggan->nama,
            [
                'pelanggan_id' => $pelanggan->id,
                'nama'         => $pelanggan->nama,
            ]
        );
    }
}