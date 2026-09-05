<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Paket;

class Pelanggan extends Model
{
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
        'keterangan',
    ];

    protected $hidden = [
        'password_pppoe',
    ];

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
}
