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
    ];

    protected $hidden = [
        'username',
        'password',
    ];

    public function pelanggans()
    {
        return $this->hasMany(Pelanggan::class);
    }
}
