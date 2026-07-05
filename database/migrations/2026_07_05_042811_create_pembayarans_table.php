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
        Schema::create('pembayarans', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | RELASI
            |--------------------------------------------------------------------------
            */

            $table->foreignId('tagihan_id')
                ->constrained('tagihans')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | PEMBAYARAN
            |--------------------------------------------------------------------------
            */

            $table->date('tanggal_bayar');

            $table->string('metode', 30);

            // Nilai tagihan
            $table->decimal('nominal', 15, 2);

            // Admin (QRIS, Transfer, dll)
            $table->decimal('biaya_admin', 15, 2)
                ->default(0);

            // Total yang harus dibayar
            $table->decimal('total_bayar', 15, 2);

            // Uang yang diterima
            $table->decimal('dibayar', 15, 2);

            // Kembalian
            $table->decimal('kembalian', 15, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            $table->string('status', 20)
                ->default('Berhasil');

            /*
            |--------------------------------------------------------------------------
            | KETERANGAN
            |--------------------------------------------------------------------------
            */

            $table->text('keterangan')
                ->nullable();

            $table->timestamps();

            $table->index('tanggal_bayar');
            $table->index('status');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};