<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OltOnuState extends Model
{
    protected $fillable = [
        'pelanggan_id',
        'router_id',
        'onu',
        'status',
        'distance',
        'rx_power',
        'tx_power',
        'last_checked_at',
    ];

    protected $casts = [
        'last_checked_at' => 'datetime',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function router()
    {
        return $this->belongsTo(Router::class);
    }
}
