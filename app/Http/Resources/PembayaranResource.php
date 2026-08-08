<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PembayaranResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            // Ditambahkan fallback ke relasi tagihan jika invoice_no langsung kosong
            'invoice_no' => $this->invoice_no ?? $this->tagihan?->invoice_no,

            'tanggal_bayar' => optional($this->tanggal_bayar)
                ->format('Y-m-d H:i:s'),

            'metode' => $this->metode,

            'nominal' => (float) $this->nominal,

            'biaya_admin' => (float) $this->biaya_admin,

            'total_bayar' => (float) $this->total_bayar,

            'dibayar' => (float) $this->dibayar,

            'kembalian' => (float) $this->kembalian,

            'status' => $this->status,

            'is_lunas' => $this->isLunas(),

            'badge_color' => $this->badgeColor(),

            // --- TAMBAHAN AGAR NAMA PELANGGAN TIDAK NULL DI HP ANDROID ---
            'pelanggan_nama' => $this->tagihan?->pelanggan?->nama 
                ?? $this->tagihan?->pelanggan?->name 
                ?? $this->user?->name 
                ?? 'Pelanggan Umum',

            'tagihan' => $this->whenLoaded('tagihan', function () {
                return [
                    'id' => $this->tagihan->id,
                    'invoice_no' => $this->tagihan->invoice_no,
                    'periode' => $this->tagihan->periode,
                    'status' => $this->tagihan->status,
                    'pelanggan_nama' => $this->tagihan->pelanggan?->nama ?? $this->tagihan->pelanggan?->name,
                ];
            }),

            'petugas' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'nama' => $this->user->name,
                ];
            }),

        ];
    }
}