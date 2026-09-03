<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pembayaran extends Model
{
    public const STATUS_BERHASIL = 'Berhasil';
    public const STATUS_PENDING = 'Pending';
    public const STATUS_DIBATALKAN = 'Dibatalkan';

    protected $fillable = [
        'invoice_no', 'invoice_date', 'invoice_pdf', 'public_token',
        'tagihan_id', 'user_id', 'tanggal_bayar', 'metode', 'nominal',
        'biaya_admin', 'total_bayar', 'dibayar', 'kembalian', 'status', 'keterangan',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'invoice_date' => 'date',
        'nominal' => 'decimal:2',
        'biaya_admin' => 'decimal:2',
        'total_bayar' => 'decimal:2',
        'dibayar' => 'decimal:2',
        'kembalian' => 'decimal:2',
    ];

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function alokasi(): HasMany
    {
        return $this->hasMany(AlokasiPembayaran::class);
    }

    public function isBerhasil(): bool
    {
        return $this->status === self::STATUS_BERHASIL;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isDibatalkan(): bool
    {
        return $this->status === self::STATUS_DIBATALKAN;
    }
}