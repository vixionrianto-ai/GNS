<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('olt_onu_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->unique()->constrained('pelanggans')->cascadeOnDelete();
            $table->foreignId('router_id')->nullable()->constrained('routers')->nullOnDelete();
            $table->string('onu')->nullable();
            $table->string('status', 20);
            $table->string('distance')->nullable();
            $table->string('rx_power')->nullable();
            $table->string('tx_power')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olt_onu_states');
    }
};
