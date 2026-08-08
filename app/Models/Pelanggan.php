<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Paket;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;



class Pelanggan extends Model
{
    public const AKTIF = 'Aktif';
    public const NONAKTIF = 'Nonaktif';

    // Alias agar kode baru tetap berjalan
    public const STATUS_AKTIF = self::AKTIF;
    public const STATUS_NONAKTIF = self::NONAKTIF;
    
    protected $fillable = [
        'kode_pelanggan',
        'nama',
        'alamat',
        'no_hp',
        'paket_id',
        'router_id',
        'mikrotik_secret_id',
        'username_pppoe',
        'password_pppoe',
        'ip_address',
        'mac_address',
        'tanggal_pasang',
        'tanggal_aktif',
        'status',

        'is_isolated',
        'isolated_at',

        'isolation_use_default',
        'isolation_period_limit',

        'keterangan',
        
    ];
    protected $casts = [
        'is_isolated' => 'boolean',
        'isolation_use_default' => 'boolean',
        'isolated_at' => 'datetime',
    ];

    public function router()
    {
    return $this->belongsTo(Router::class);
    }
    public function paket()
    {
        return $this->belongsTo(Paket::class);
    }
    /**
     * Relasi ke Tagihan
     */
    public function tagihans()
    {
        return $this->hasMany(Tagihan::class);
    }
    /**
     * Saldo pelanggan.
     */
    public function saldo()
    {
        return $this->hasOne(
            SaldoPelanggan::class
        );
    }
}