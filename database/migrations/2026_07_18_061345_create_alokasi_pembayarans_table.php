<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alokasi_pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembayaran_id')
                ->constrained('pembayarans')
                ->cascadeOnDelete();
            $table->foreignId('tagihan_id')
                ->nullable()
                ->constrained('tagihans')
                ->nullOnDelete();
            $table->decimal('nominal', 15, 2);
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->index(['pembayaran_id', 'tagihan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alokasi_pembayarans');
    }
};
