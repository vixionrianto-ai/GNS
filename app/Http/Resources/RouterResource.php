<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RouterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'nama_router' => $this->nama_router,

            'lokasi' => $this->lokasi,

            'ip_router' => $this->ip_router,

            'api_port' => $this->api_port,

            'username' => $this->username,

            'ssl' => (bool) $this->ssl,

            'status' => $this->status,

            'identity' => $this->identity,

            'versi_routeros' => $this->versi_routeros,

            'is_online' => $this->is_online,

            'last_checked_at' => $this->last_checked_at,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];
    }
}