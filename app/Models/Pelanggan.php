<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pelanggan extends Model
{
    public const AKTIF = 'Aktif';
    public const NONAKTIF = 'Nonaktif';

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

    protected static function booted(): void
    {
        static::created(function (self $pelanggan): void {
            $send = static function () use ($pelanggan): void {
                try {
                    app(\App\Services\PelangganBaruWhatsAppService::class)
                        ->send($pelanggan->fresh(['paket', 'router']));
                } catch (\Throwable $e) {
                    \Log::error('Gagal mengirim WhatsApp pelanggan baru.', [
                        'pelanggan_id' => $pelanggan->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            };

            if (DB::transactionLevel() > 0) {
                DB::afterCommit($send);
            } else {
                $send();
            }
        });
    }

    public function router()
    {
        return $this->belongsTo(Router::class);
    }

    public function paket()
    {
        return $this->belongsTo(Paket::class);
    }

    public function tagihans()
    {
        return $this->hasMany(Tagihan::class);
    }

    public function saldo()
    {
        return $this->hasOne(SaldoPelanggan::class);
    }
}
