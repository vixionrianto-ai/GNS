<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $renames = [
            'whatsapp.reminder_h3' => 'whatsapp.reminder_first_days',
            'whatsapp.reminder_h7' => 'whatsapp.reminder_second_days',
            'whatsapp.template_h3' => 'whatsapp.template_reminder_first',
            'whatsapp.template_h7' => 'whatsapp.template_reminder_second',
        ];

        foreach ($renames as $old => $new) {
            $oldSetting = DB::table('settings')->where('key', $old)->first();
            $newSetting = DB::table('settings')->where('key', $new)->first();

            if ($oldSetting && !$newSetting) {
                DB::table('settings')->where('id', $oldSetting->id)->update([
                    'key' => $new,
                    'description' => match ($new) {
                        'whatsapp.reminder_first_days' => 'Reminder pertama (hari setelah jatuh tempo)',
                        'whatsapp.reminder_second_days' => 'Reminder kedua (hari setelah jatuh tempo)',
                        'whatsapp.template_reminder_first' => 'Template Reminder Pertama',
                        'whatsapp.template_reminder_second' => 'Template Reminder Kedua',
                        default => $oldSetting->description,
                    },
                ]);
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $renames = [
            'whatsapp.reminder_first_days' => 'whatsapp.reminder_h3',
            'whatsapp.reminder_second_days' => 'whatsapp.reminder_h7',
            'whatsapp.template_reminder_first' => 'whatsapp.template_h3',
            'whatsapp.template_reminder_second' => 'whatsapp.template_h7',
        ];

        foreach ($renames as $old => $new) {
            DB::table('settings')->where('key', $old)->update(['key' => $new]);
        }
    }
};
