<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Protect payment allocations from destructive cascade deletes.
     *
     * The parent relations (customer/router/package/billing/payment) are
     * already protected by the two migrations immediately before this one.
     */
    public function up(): void
    {
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
     * Restore the original allocation cascade behavior when rolling back.
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
    }
};
