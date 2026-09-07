<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add public invoice tokens and their unique index when they are not
     * already present in the deployed database.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('pembayarans', 'public_token')) {
            Schema::table('pembayarans', function (Blueprint $table) {
                $table->string('public_token', 64)
                    ->nullable()
                    ->after('invoice_pdf');
            });
        }

        if (! Schema::hasIndex('pembayarans', 'pembayarans_public_token_unique')) {
            Schema::table('pembayarans', function (Blueprint $table) {
                $table->unique('public_token', 'pembayarans_public_token_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('pembayarans', 'pembayarans_public_token_unique')) {
            Schema::table('pembayarans', function (Blueprint $table) {
                $table->dropUnique('pembayarans_public_token_unique');
            });
        }

        if (Schema::hasColumn('pembayarans', 'public_token')) {
            Schema::table('pembayarans', function (Blueprint $table) {
                $table->dropColumn('public_token');
            });
        }
    }
};
