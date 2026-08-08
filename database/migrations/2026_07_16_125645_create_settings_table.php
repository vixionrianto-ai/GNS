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
        Schema::create('settings', function (Blueprint $table) {

            $table->id();

            // Kelompok pengaturan
            $table->string('group',50);

            // Nama key
            $table->string('key',100)->unique();

            // Nilai
            $table->text('value')->nullable();

            // Keterangan
            $table->string('description')->nullable();

            // Tipe data
            $table->enum('type',[
                'string',
                'integer',
                'boolean',
                'time',
                'json'
            ])->default('string');

            // Aktif / Tidak
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
