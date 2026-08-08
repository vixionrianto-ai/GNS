<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {

            $table->boolean('isolation_use_default')
                ->default(true)
                ->after('is_isolated');

            $table->unsignedTinyInteger('isolation_period_limit')
                ->nullable()
                ->after('isolation_use_default');

        });
    }

    public function down(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {

            $table->dropColumn([
                'isolation_use_default',
                'isolation_period_limit',
            ]);

        });
    }
};