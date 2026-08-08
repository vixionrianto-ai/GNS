<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {

            $table->string('type', 30)
                ->default('string')
                ->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {

            $table->enum('type', [
                'string',
                'integer',
                'boolean',
                'time',
                'json',
            ])->default('string')->change();

        });
    }
};