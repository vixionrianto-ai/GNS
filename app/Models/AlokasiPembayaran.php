<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlokasiPembayaran extends Model
{
    protected $fillable = [
        'pembayaran_id',
        'tagihan_id',
        'nominal',
        'keterangan',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    /**
     * Pembayaran induk
     */
    public function pembayaran(): BelongsTo
    {
        return $this->belongsTo(Pembayaran::class);
    }

    /**
     * Tagihan yang dibayar
     */
    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }
}