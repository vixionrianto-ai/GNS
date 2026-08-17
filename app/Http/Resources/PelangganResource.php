<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\WhatsAppService;

class PelangganResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $whatsappUrl = null;

        if (!empty($this->no_hp)) {
            $whatsappUrl = app(WhatsAppService::class)->url(
                $this->no_hp,
                ''
            );
        }

        return [
            'id' => $this->id,
            'kode_pelanggan' => $this->kode_pelanggan,
            'nama' => $this->nama,
            'alamat' => $this->alamat,
            'no_hp' => $this->no_hp,
            'whatsapp_url' => $whatsappUrl,
            'status' => $this->status,
            'tagihan_belum_lunas' => $this->tagihan_belum_lunas,
            'username_pppoe' => $this->username_pppoe,
            'password_pppoe' => $this->password_pppoe,
            'ip_address' => $this->ip_address,
            'mac_address' => $this->mac_address,
            'router_id' => $this->router_id,
            'paket_id' => $this->paket_id,
            'tanggal_pasang' => $this->tanggal_pasang,
            'tanggal_aktif' => $this->tanggal_aktif,
            'keterangan' => $this->keterangan,
            'isolation_use_default' => $this->isolation_use_default,
            'isolation_period_limit' => $this->isolation_period_limit,
            'is_isolated' => (bool) $this->is_isolated,

            'paket' => $this->whenLoaded('paket', function () {
                return [
                    'id' => $this->paket->id,
                    'nama_paket' => $this->paket->nama_paket,
                    'kecepatan' => $this->paket->kecepatan,
                    'harga' => $this->paket->harga,
                ];
            }),

            'router' => $this->whenLoaded('router', function () {
                return [
                    'id' => $this->router->id,
                    'nama_router' => $this->router->nama_router,
                    'identity' => $this->router->identity,
                    'status' => $this->router->status,
                    'is_online' => $this->router->is_online,
                ];
            }),

            'saldo' => $this->whenLoaded('saldo', function () {
                return [
                    'saldo' => $this->saldo->saldo ?? 0,
                ];
            }),
        ];
    }
}
