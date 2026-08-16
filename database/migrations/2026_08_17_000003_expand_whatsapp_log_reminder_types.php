<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE whats_app_logs MODIFY jenis ENUM('tagihan','reminder_first','reminder_second','h3','h7','isolir','pembayaran') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE whats_app_logs SET jenis = 'h3' WHERE jenis = 'reminder_first'");
        DB::statement("UPDATE whats_app_logs SET jenis = 'h7' WHERE jenis = 'reminder_second'");
        DB::statement("ALTER TABLE whats_app_logs MODIFY jenis ENUM('tagihan','h3','h7','isolir','pembayaran') NOT NULL");
    }
};
