<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'kode' => $this->kode_pelanggan,

            'nama' => $this->nama,

            'telepon' => $this->no_hp,

            'username' => $this->username_pppoe,

            'paket' => [
                'nama' => optional($this->paket)->nama_paket,
                'kecepatan' => optional($this->paket)->kecepatan,
            ],

            'status' => $this->status,

            'is_isolated' => $this->is_isolated,

            'tagihan_belum_lunas' => $this->tagihan_belum_lunas,

            'saldo' => (float) optional($this->saldo)->saldo,

        ];
    }
}