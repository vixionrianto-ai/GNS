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
        Schema::create('audit_trails', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | User
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Aktivitas
            |--------------------------------------------------------------------------
            */

            $table->string('module', 50);

            $table->string('action', 50);

            $table->text('description');

            /*
            |--------------------------------------------------------------------------
            | Request
            |--------------------------------------------------------------------------
            */

            $table->ipAddress('ip_address')
                ->nullable();

            $table->text('user_agent')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Additional Data
            |--------------------------------------------------------------------------
            */

            $table->json('properties')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Timestamp
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Index
            |--------------------------------------------------------------------------
            */

            $table->index('module');

            $table->index('action');

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};