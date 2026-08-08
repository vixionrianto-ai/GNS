<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'description',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function value(string $key, $default = null)
    {
        return static::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->value('value') ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::query()
            ->where('key', $key)
            ->update([
                'value' => $value,
            ]);
    }
    /**
     * Hari jatuh tempo.
     */
    public static function dueDays(): int
    {
        return (int) static::value(
            'billing.due_days',
            10
        );
    }

    /**
     * Denda per hari.
     */
    public static function finePerDay(): int
    {
        return (int) static::value(
            'billing.fine_per_day',
            1000
        );
    }

    /**
     * Batas Auto Isolir Default (Periode).
     */
    public static function defaultIsolationPeriod(): int
    {
        return (int) static::value(
            'billing.isolate_after',
            2
        );
    }
 
    /**
     * Auto Apply Saldo.
     */
    public static function autoApplySaldo(): bool
    {
        return filter_var(
            static::value(
                'billing.auto_apply_saldo',
                true
            ),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    /**
     * Prefix invoice.
     */
    public static function invoicePrefix(): string
    {
        return (string) static::value(
            'invoice.prefix',
            'INV'
        );
    }

}