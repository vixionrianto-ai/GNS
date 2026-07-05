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
        Schema::create('routers', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | IDENTITAS ROUTER
            |--------------------------------------------------------------------------
            */

            $table->string('nama_router');

            $table->string('lokasi')->nullable();

            /*
            |--------------------------------------------------------------------------
            | KONEKSI API
            |--------------------------------------------------------------------------
            */

            $table->string('ip_router');

            $table->integer('api_port')
                  ->default(8728);

            $table->string('username');

            $table->string('password');

            $table->boolean('ssl')
                  ->default(false);

            /*
            |--------------------------------------------------------------------------
            | INFORMASI
            |--------------------------------------------------------------------------
            */

            $table->string('versi_routeros')
                  ->nullable();

            $table->string('identity')
                  ->nullable();

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            $table->string('status',20)
                  ->default('Aktif');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routers');
    }
};