<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'router_id' => $this->router_id,

            'router' => $this->router?->nama_router,

            'nama_paket' => $this->nama_paket,

            'kecepatan' => $this->kecepatan,

            'profile_mikrotik' => $this->profile_mikrotik,

            'harga' => $this->harga,

            'status' => $this->status,

            'keterangan' => $this->keterangan,

        ];
    }
}