<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ppp_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')->constrained('routers')->cascadeOnDelete();
            $table->foreignId('pelanggan_id')->nullable()->constrained('pelanggans')->nullOnDelete();
            $table->string('event_type', 20);
            $table->string('username')->index();
            $table->string('ip_address')->nullable();
            $table->string('caller_id')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamp('event_at');
            $table->string('event_key', 64)->unique();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['router_id', 'event_type', 'event_at']);
            $table->index(['pelanggan_id', 'event_type', 'event_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppp_events');
    }
};
