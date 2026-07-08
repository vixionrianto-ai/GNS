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
        Schema::table('pembayarans', function (Blueprint $table) {

            // Nomor Invoice
            $table->string('invoice_no')
                ->nullable()
                ->after('id');

            // Tanggal Invoice
            $table->date('invoice_date')
                ->nullable()
                ->after('invoice_no');

            // Lokasi file PDF Invoice
            $table->string('invoice_pdf')
                ->nullable()
                ->after('invoice_date');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {

            $table->dropColumn([
                'invoice_no',
                'invoice_date',
                'invoice_pdf',
            ]);

        });
    }
};