<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Prevent destructive cascades across financial and customer data.
     */
    public function up(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->dropForeign(['router_id']);
            $table->dropForeign(['paket_id']);

            $table->foreign('router_id')
                ->references('id')
                ->on('routers')
                ->restrictOnDelete();

            $table->foreign('paket_id')
                ->references('id')
                ->on('pakets')
                ->restrictOnDelete();
        });

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

        Schema::table('alokasi_pembayarans', function (Blueprint $table) {
            $table->dropForeign(['pembayaran_id']);
            $table->dropForeign(['tagihan_id']);

            $table->foreign('pembayaran_id')
                ->references('id')
                ->on('pembayarans')
                ->restrictOnDelete();

            $table->foreign('tagihan_id')
                ->references('id')
                ->on('tagihans')
                ->restrictOnDelete();
        });
    }

    /**
     * Restore the original cascade behavior when rolling back.
     */
    public function down(): void
    {
        Schema::table('alokasi_pembayarans', function (Blueprint $table) {
            $table->dropForeign(['pembayaran_id']);
            $table->dropForeign(['tagihan_id']);

            $table->foreign('pembayaran_id')
                ->references('id')
                ->on('pembayarans')
                ->cascadeOnDelete();

            $table->foreign('tagihan_id')
                ->references('id')
                ->on('tagihans')
                ->cascadeOnDelete();
        });

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

        Schema::table('pelanggans', function (Blueprint $table) {
            $table->dropForeign(['router_id']);
            $table->dropForeign(['paket_id']);

            $table->foreign('router_id')
                ->references('id')
                ->on('routers')
                ->cascadeOnDelete();

            $table->foreign('paket_id')
                ->references('id')
                ->on('pakets')
                ->cascadeOnDelete();
        });
    }
};
