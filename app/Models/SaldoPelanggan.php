<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaldoPelanggan extends Model
{
    protected $table = 'saldo_pelanggans';

    protected $fillable = [

        'pelanggan_id',

        'saldo',

        'keterangan',

    ];

    protected $casts = [

        'saldo' => 'decimal:2',

    ];

    /**
     * Relasi ke pelanggan.
     */
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(
            Pelanggan::class
        );
    }

    /**
     * Tambah saldo.
     */
    public function tambah(
        float $nominal,
        string $keterangan = 'Topup'
    ): void {

        $this->increment(
            'saldo',
            $nominal
        );

        $this->update([
            'keterangan' => $keterangan,
        ]);

    }

    /**
     * Kurangi saldo.
     */
    public function kurangi(
        float $nominal,
        string $keterangan = 'Pemakaian Saldo'
    ): void {

        $this->decrement(
            'saldo',
            $nominal
        );

        $this->update([
            'keterangan' => $keterangan,
        ]);

    }

    /**
     * Ambil / buat saldo pelanggan.
     */
    public static function milik(
        int $pelangganId
    ): self {

        return static::firstOrCreate(

            [
                'pelanggan_id' => $pelangganId,
            ],

            [
                'saldo' => 0,
                'keterangan' => 'Saldo Awal',
            ]

        );

    }
    public function usages(): HasMany
    {
        return $this->hasMany(SaldoUsage::class);
    }
}