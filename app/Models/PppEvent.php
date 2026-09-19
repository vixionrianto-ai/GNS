<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PppEvent extends Model
{
    protected $fillable = [
        'router_id',
        'pelanggan_id',
        'event_type',
        'username',
        'ip_address',
        'caller_id',
        'session_id',
        'event_at',
        'event_key',
        'payload',
    ];

    protected $casts = [
        'event_at' => 'datetime',
        'payload' => 'array',
    ];

    public function router()
    {
        return $this->belongsTo(Router::class);
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }
}
