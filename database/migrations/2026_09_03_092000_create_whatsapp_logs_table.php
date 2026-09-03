<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained('pelanggans')->cascadeOnDelete();
            $table->foreignId('tagihan_id')->nullable()->constrained('tagihans')->nullOnDelete();
            $table->string('jenis', 30);
            $table->string('provider', 50)->nullable();
            $table->string('nomor', 30);
            $table->text('pesan');
            $table->string('status', 20);
            $table->longText('response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['tagihan_id', 'jenis', 'status']);
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_logs');
    }
};