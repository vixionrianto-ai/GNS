<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppp_disconnect_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 64)->unique();
            $table->foreignId('router_id')->constrained('routers')->cascadeOnDelete();
            $table->foreignId('pelanggan_id')->constrained('pelanggans')->cascadeOnDelete();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
            $table->index(['router_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppp_disconnect_events');
    }
};
