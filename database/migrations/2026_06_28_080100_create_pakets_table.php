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
        Schema::create('pakets', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | RELASI
            |--------------------------------------------------------------------------
            */

            $table->foreignId('router_id')
                ->nullable()
                ->constrained('routers')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | DATA PAKET
            |--------------------------------------------------------------------------
            */

            $table->string('nama_paket');

            $table->string('kecepatan');

            $table->unsignedInteger('harga');

            /*
            |--------------------------------------------------------------------------
            | MIKROTIK
            |--------------------------------------------------------------------------
            */

            $table->string('profile_mikrotik');

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            $table->string('status',20)
                ->default('Aktif');

            /*
            |--------------------------------------------------------------------------
            | KETERANGAN
            |--------------------------------------------------------------------------
            */

            $table->text('keterangan')
                ->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pakets');
    }
};