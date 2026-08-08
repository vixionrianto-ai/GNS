<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Tagihan;

class WhatsAppTemplateService
{
    public function render(string $settingKey, Tagihan $tagihan): string
    {
        $template = Setting::value($settingKey, '');

        $pelanggan = $tagihan->pelanggan;

        $replace = [

            '{nama}'          => $pelanggan->nama,

            '{invoice}'       => $tagihan->nomor_invoice,

            '{bulan}'         => $tagihan->tanggal_tagihan
                                    ->translatedFormat('F Y'),

            '{tanggal}'       => $tagihan->tanggal_tagihan
                                    ->format('d-m-Y'),

            '{jatuh_tempo}'   => $tagihan->tanggal_jatuh_tempo
                                    ->format('d-m-Y'),

            '{nominal}'       => number_format(
                                    $tagihan->nominal,
                                    0,
                                    ',',
                                    '.'
                                ),

            '{denda}'         => number_format(
                                    $tagihan->denda,
                                    0,
                                    ',',
                                    '.'
                                ),

            '{total}'         => number_format(
                                    $tagihan->total,
                                    0,
                                    ',',
                                    '.'
                                ),

            '{isp}'           => config('app.name'),

        ];

        return str_replace(
            array_keys($replace),
            array_values($replace),
            $template
        );
    }
}