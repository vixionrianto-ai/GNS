<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditTrail extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'user_id',

        'module',

        'action',

        'description',

        'ip_address',

        'user_agent',

        'properties',

    ];

    /*
    |--------------------------------------------------------------------------
    | Cast
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'properties' => 'array',

        'created_at' => 'datetime',

        'updated_at' => 'datetime',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relation
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    public function isCreate(): bool
    {
        return strtolower($this->action) === 'create';
    }

    public function isUpdate(): bool
    {
        return strtolower($this->action) === 'update';
    }

    public function isDelete(): bool
    {
        return strtolower($this->action) === 'delete';
    }

    public function isLogin(): bool
    {
        return strtolower($this->action) === 'login';
    }

    public function badgeColor(): string
    {
        return match (strtolower($this->action)) {

            'create' => 'success',

            'update' => 'warning',

            'delete' => 'danger',

            'login' => 'info',

            'logout' => 'secondary',

            default => 'primary',

        };
    }

    public function actionLabel(): string
    {
        return ucfirst($this->action);
    }

    public function createdAtFormat(): string
    {
        return optional($this->created_at)
            ->format('d M Y H:i:s');
    }
}