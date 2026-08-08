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
        Schema::create('saldo_usages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('saldo_pelanggan_id')
                ->constrained('saldo_pelanggans')
                ->cascadeOnDelete();

            $table->foreignId('tagihan_id')
                ->constrained('tagihans')
                ->cascadeOnDelete();

            $table->decimal('jumlah', 15, 2);

            $table->string('tipe', 20)->default('auto');
            // auto | manual | rollback

            $table->string('keterangan')->nullable();

            $table->timestamps();

            $table->index('saldo_pelanggan_id');
            $table->index('tagihan_id');
            $table->index('tipe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saldo_usages');
    }
};
