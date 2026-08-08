<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pelanggan;

class Paket extends Model
{
    public const STATUS_AKTIF = 'Aktif';
    public const STATUS_NONAKTIF = 'Nonaktif';
        protected $fillable = [
        'router_id',
        'nama_paket',
        'kecepatan',
        'profile_mikrotik',
        'harga',
        'status',
        'keterangan',
    ];

    // Satu paket memiliki banyak pelanggan
    public function pelanggans()
    {
        return $this->hasMany(Pelanggan::class);
    }
    public function router()
    {
        return $this->belongsTo(\App\Models\Router::class);
    }
}