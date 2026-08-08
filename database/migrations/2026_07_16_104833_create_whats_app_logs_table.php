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
        Schema::create('whats_app_logs', function (Blueprint $table) {

            $table->id();

            // Relasi
            $table->foreignId('pelanggan_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('tagihan_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Jenis pesan
            $table->enum('jenis', [
                'tagihan',
                'h3',
                'h7',
                'isolir',
                'pembayaran',
            ]);

            // Provider
            $table->string('provider')->default('fonnte');

            // Nomor tujuan
            $table->string('nomor',30);

            // Isi pesan
            $table->longText('pesan');

            // Status kirim
            $table->enum('status',[
                'pending',
                'success',
                'failed'
            ])->default('pending');

            // Response API
            $table->longText('response')
                ->nullable();

            // Waktu kirim
            $table->timestamp('sent_at')
                ->nullable();

            $table->timestamps();

            // Index
            $table->index([
                'pelanggan_id',
                'jenis'
            ]);

            $table->index([
                'tagihan_id',
                'jenis'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whats_app_logs');
    }
};
