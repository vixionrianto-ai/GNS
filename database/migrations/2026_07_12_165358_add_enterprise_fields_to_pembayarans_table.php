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

            /*
            |--------------------------------------------------------------------------
            | Invoice Enterprise
            |--------------------------------------------------------------------------
            */

            if (!Schema::hasColumn('pembayarans', 'invoice_no')) {

                $table->string('invoice_no')
                    ->unique()
                    ->after('id');

            }

            if (!Schema::hasColumn('pembayarans', 'invoice_date')) {

                $table->dateTime('invoice_date')
                    ->nullable()
                    ->after('invoice_no');

            }

            if (!Schema::hasColumn('pembayarans', 'invoice_pdf')) {

                $table->string('invoice_pdf')
                    ->nullable()
                    ->after('invoice_date');

            }

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