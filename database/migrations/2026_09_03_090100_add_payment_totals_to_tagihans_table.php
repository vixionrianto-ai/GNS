<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->decimal('dibayar', 15, 2)
                ->default(0)
                ->after('total');

            $table->decimal('sisa', 15, 2)
                ->default(0)
                ->after('dibayar');
        });
    }

    public function down(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropColumn(['dibayar', 'sisa']);
        });
    }
};
