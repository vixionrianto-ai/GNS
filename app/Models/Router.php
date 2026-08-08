<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Router extends Model
{
    protected $fillable = [

    'nama_router',
    'ip_router',
    'api_port',
    'username',
    'password',
    'lokasi',
    'versi_routeros',
    'identity',
    'ssl',
    'status',

    'is_online',
    'last_checked_at',

];

protected $casts = [
    'ssl' => 'boolean',
    'is_online' => 'boolean',
    'last_checked_at' => 'datetime',
];

    public function pelanggans()
    {
     return $this->hasMany(Pelanggan::class);
    }
}
    