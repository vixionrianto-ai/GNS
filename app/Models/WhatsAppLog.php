<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppLog extends Model
{
    protected $fillable = [

        'pelanggan_id',

        'tagihan_id',

        'jenis',

        'provider',

        'nomor',

        'pesan',

        'status',

        'response',

        'sent_at',

    ];

    protected $casts = [

        'sent_at' => 'datetime',

    ];

    /**
     * Pelanggan
     */
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    /**
     * Tagihan
     */
    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }
}