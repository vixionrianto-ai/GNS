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
    Schema::create('alokasi_pembayarans', function (Blueprint $table) {

        $table->id();

        /*
        |--------------------------------------------------------------------------
        | Relasi
        |--------------------------------------------------------------------------
        */

        $table->foreignId('pembayaran_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('tagihan_id')
            ->nullable()
            ->constrained()
            ->nullOnDelete();

        /*
        |--------------------------------------------------------------------------
        | Nominal yang dialokasikan
        |--------------------------------------------------------------------------
        */

        $table->decimal('nominal', 15, 2);

        /*
        |--------------------------------------------------------------------------
        | Keterangan
        |--------------------------------------------------------------------------
        */

        $table->string('keterangan')
            ->nullable();

        $table->timestamps();

        /*
        |--------------------------------------------------------------------------
        | Index
        |--------------------------------------------------------------------------
        */

        $table->index([
            'pembayaran_id',
            'tagihan_id'
        ]);
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::dropIfExists('alokasi_pembayarans');
}
};
