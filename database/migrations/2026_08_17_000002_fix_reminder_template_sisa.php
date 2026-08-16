<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        foreach (['whatsapp.template_reminder_first', 'whatsapp.template_reminder_second'] as $key) {
            $setting = DB::table('settings')->where('key', $key)->first();

            if ($setting && str_contains((string) $setting->value, '{total}')) {
                DB::table('settings')
                    ->where('id', $setting->id)
                    ->update([
                        'value' => str_replace('{total}', '{total_sisa}', $setting->value),
                    ]);
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        foreach (['whatsapp.template_reminder_first', 'whatsapp.template_reminder_second'] as $key) {
            $setting = DB::table('settings')->where('key', $key)->first();

            if ($setting && str_contains((string) $setting->value, '{total_sisa}')) {
                DB::table('settings')
                    ->where('id', $setting->id)
                    ->update([
                        'value' => str_replace('{total_sisa}', '{total}', $setting->value),
                    ]);
            }
        }
    }
};
