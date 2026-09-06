<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add payment totals to invoices when they are not already present.
     *
     * This migration is intentionally safe for environments whose database
     * already contains these columns from an earlier schema revision.
     */
    public function up(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            if (! Schema::hasColumn('tagihans', 'dibayar')) {
                $table->decimal('dibayar', 15, 2)
                    ->default(0)
                    ->after('total');
            }

            if (! Schema::hasColumn('tagihans', 'sisa')) {
                $table->decimal('sisa', 15, 2)
                    ->default(0)
                    ->after('dibayar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('tagihans', 'dibayar')) {
                $columns[] = 'dibayar';
            }

            if (Schema::hasColumn('tagihans', 'sisa')) {
                $columns[] = 'sisa';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
