<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    /*
    |--------------------------------------------------------------------------
    | STATUS TAGIHAN
    |--------------------------------------------------------------------------
    */

    public const STATUS_BELUM_BAYAR = 'Belum Bayar';

    public const STATUS_LUNAS = 'Lunas';

    public const STATUS_JATUH_TEMPO = 'Jatuh Tempo';

    public const STATUS_DIBATALKAN = 'Dibatalkan';

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */
    
    protected $fillable = [

        'pelanggan_id',

        'invoice_no',

        'periode',

        'bulan',

        'tahun',

        'tanggal_tagihan',

        'tanggal_jatuh_tempo',

        'nominal',

        'denda',

        'total',

        'status',

        'tanggal_bayar',

        'keterangan',

    ];

    protected $casts = [

        'tanggal_tagihan'      => 'date',

        'tanggal_jatuh_tempo'  => 'date',

        'tanggal_bayar'        => 'datetime',

    ];

    /**
     * Relasi ke Pelanggan
     */
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

}