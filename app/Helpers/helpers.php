<?php

use App\Models\Setting;

if (! function_exists('setting')) {

    function setting(string $key, $default = null)
    {
        $setting = Setting::where('key', $key)
            ->where('is_active', true)
            ->first();

        return $setting?->value ?? $default;
    }

}