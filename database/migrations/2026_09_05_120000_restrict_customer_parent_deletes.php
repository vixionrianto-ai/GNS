<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
    }

    public function down(): void
    {
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
