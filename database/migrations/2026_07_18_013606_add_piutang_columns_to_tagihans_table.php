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
        Schema::table('tagihans', function (Blueprint $table) {

            // Nilai paket bulan berjalan
            $table->decimal('subtotal', 15, 2)
                ->default(0)
                ->after('nominal');

            // Sisa tagihan dari bulan sebelumnya
            $table->decimal('tunggakan', 15, 2)
                ->default(0)
                ->after('subtotal');

            // Total yang sudah dibayar
            $table->decimal('dibayar', 15, 2)
                ->default(0)
                ->after('total');

            // Sisa yang masih harus dibayar
            $table->decimal('sisa', 15, 2)
                ->default(0)
                ->after('dibayar');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {

            $table->dropColumn([
                'subtotal',
                'tunggakan',
                'dibayar',
                'sisa',
            ]);

        });
    }
};
