<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropForeign(['pelanggan_id']);

            $table->foreign('pelanggan_id')
                ->references('id')
                ->on('pelanggans')
                ->restrictOnDelete();
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropForeign(['tagihan_id']);

            $table->foreign('tagihan_id')
                ->references('id')
                ->on('tagihans')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropForeign(['tagihan_id']);

            $table->foreign('tagihan_id')
                ->references('id')
                ->on('tagihans')
                ->cascadeOnDelete();
        });

        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropForeign(['pelanggan_id']);

            $table->foreign('pelanggan_id')
                ->references('id')
                ->on('pelanggans')
                ->cascadeOnDelete();
        });
    }
};
