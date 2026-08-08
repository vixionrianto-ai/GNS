<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagihanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'invoice_no' => $this->invoice_no,
            'pelanggan_id' => $this->pelanggan_id,

            'pelanggan_nama' => optional($this->pelanggan)->nama,

            'periode' => $this->periode,

            'bulan' => $this->bulan,

            'tahun' => $this->tahun,

            'tanggal_tagihan' => optional($this->tanggal_tagihan)
                ->format('Y-m-d'),

            'tanggal_jatuh_tempo' => optional($this->tanggal_jatuh_tempo)
                ->format('Y-m-d'),

            'tanggal_bayar' => optional($this->tanggal_bayar)
                ->format('Y-m-d H:i:s'),

            // Menggunakan helper dari model
            'total' => $this->getTotalTagihan(),

            'dibayar' => $this->getTotalDibayar(),

            'sisa' => $this->getSisaTagihan(),

            'status' => $this->status,

            'keterangan' => $this->keterangan,
        ];
    }
}