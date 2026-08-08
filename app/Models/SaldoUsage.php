<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaldoUsage extends Model
{
    protected $fillable = [
        'saldo_pelanggan_id',
        'tagihan_id',
        'jumlah',
        'tipe',
        'keterangan',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
    ];

    public function saldoPelanggan(): BelongsTo
    {
        return $this->belongsTo(SaldoPelanggan::class);
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }
}