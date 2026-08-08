<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AlokasiPembayaran;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pembayaran extends Model
{
    /*
    |--------------------------------------------------------------------------
    | STATUS PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    public const STATUS_BERHASIL = 'Berhasil';

    public const STATUS_PENDING = 'Pending';

    public const STATUS_DIBATALKAN = 'Dibatalkan';

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'invoice_no',
        'invoice_date',
        'invoice_pdf',
        'public_token',
        'tagihan_id',
        'user_id',
        'tanggal_bayar',
        'metode',
        'nominal',
        'biaya_admin',
        'total_bayar',
        'dibayar',
        'kembalian',
        'status',
        'keterangan',

    ];

    /*
    |--------------------------------------------------------------------------
    | CAST
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'tanggal_bayar' => 'datetime',

        'invoice_date' => 'datetime',

        'nominal' => 'decimal:2',

        'biaya_admin' => 'decimal:2',

        'total_bayar' => 'decimal:2',

        'dibayar' => 'decimal:2',

        'kembalian' => 'decimal:2',

    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

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
        /**
     * Total tagihan yang harus dibayar.
     */
    public function getTotalAttribute(): float
    {
        return (float) $this->total_bayar;
    }

    /**
     * Apakah pembayaran lunas.
     */
    public function isLunas(): bool
    {
        return $this->isBerhasil();
    }

    /**
     * Nominal kembalian.
     */
    public function hasKembalian(): bool
    {
        return $this->kembalian > 0;
    }

    /**
     * Status badge Bootstrap.
     */
    public function badgeColor(): string
    {
        return match ($this->status) {

            self::STATUS_BERHASIL => 'success',

            self::STATUS_PENDING => 'warning',

            self::STATUS_DIBATALKAN => 'danger',

            default => 'secondary',

        };
    }
    /**
     * Detail alokasi pembayaran
     */
    public function alokasi(): HasMany
    {
        return $this->hasMany(AlokasiPembayaran::class);
    }
}