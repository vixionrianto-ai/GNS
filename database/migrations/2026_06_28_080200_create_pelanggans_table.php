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
        Schema::create('pelanggans', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | KODE PELANGGAN
            |--------------------------------------------------------------------------
            */

            $table->string('kode_pelanggan')
                  ->unique();

            /*
            |--------------------------------------------------------------------------
            | DATA PELANGGAN
            |--------------------------------------------------------------------------
            */

            $table->string('nama');

            $table->text('alamat');

            $table->string('no_hp');

            /*
            |--------------------------------------------------------------------------
            | RELASI
            |--------------------------------------------------------------------------
            */

            $table->foreignId('router_id')
                ->constrained('routers')
                ->cascadeOnDelete();

            $table->foreignId('paket_id')
                ->constrained('pakets')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | PPPoE
            |--------------------------------------------------------------------------
            */

            $table->string('username_pppoe')
                ->unique();

            $table->string('password_pppoe');

            $table->string('mikrotik_secret_id')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | NETWORK
            |--------------------------------------------------------------------------
            */

            $table->string('ip_address')
                ->nullable();

            $table->string('mac_address')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | TANGGAL
            |--------------------------------------------------------------------------
            */

            $table->date('tanggal_pasang')
            ->nullable();

            $table->date('tanggal_aktif')
            ->nullable();

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
        Schema::dropIfExists('pelanggans');
    }
};