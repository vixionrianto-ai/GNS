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
        Schema::create('tagihans', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | RELASI
            |--------------------------------------------------------------------------
            */

            $table->foreignId('pelanggan_id')
                ->constrained('pelanggans')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | INVOICE
            |--------------------------------------------------------------------------
            */

            $table->string('invoice_no')
                ->unique();

            /*
            |--------------------------------------------------------------------------
            | PERIODE
            |--------------------------------------------------------------------------
            */

            $table->char('periode',7);

            $table->unsignedTinyInteger('bulan');

            $table->year('tahun');

            /*
            |--------------------------------------------------------------------------
            | TANGGAL
            |--------------------------------------------------------------------------
            */

            $table->date('tanggal_tagihan');

            $table->date('tanggal_jatuh_tempo');

            $table->dateTime('tanggal_bayar')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | NOMINAL
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('nominal');

            $table->unsignedBigInteger('denda')
                ->default(0);

            $table->unsignedBigInteger('total');

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            $table->string('status',20)
                ->default('Belum Bayar');

            /*
            |--------------------------------------------------------------------------
            | KETERANGAN
            |--------------------------------------------------------------------------
            */

            $table->text('keterangan')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEX
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'pelanggan_id',
                'periode'
            ]);

            $table->index('status');

            $table->index('tanggal_jatuh_tempo');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihans');
    }
};