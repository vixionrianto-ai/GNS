<?php

namespace App\Services;

use App\Models\Paket;

class PaketService
{
    public function getList(int $perPage = 100)
    {
        return Paket::with('router')
        ->orderBy('nama_paket')
        ->paginate($perPage);
    }

    public function getDetail(int $id): Paket
    {
        return Paket::with('router')
            ->findOrFail($id);
    }

    public function create(array $data): Paket
    {
        return Paket::create($data);
    }

    public function update(
        int $id,
        array $data
    ): Paket {

        $paket = Paket::findOrFail($id);

        $paket->update($data);

        return $paket->fresh();
    }

    public function delete(
        int $id
    ): void {

        $paket = Paket::findOrFail($id);

        if ($paket->pelanggans()->exists()) {

            throw new \Exception(
                'Paket masih digunakan pelanggan.'
            );

        }

        $paket->delete();
    }
}