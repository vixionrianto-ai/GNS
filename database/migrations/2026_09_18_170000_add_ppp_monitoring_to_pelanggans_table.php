<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->string('ppp_status', 20)->default('unknown')->after('status');
            $table->string('ppp_ip_address')->nullable()->after('ppp_status');
            $table->string('ppp_caller_id')->nullable()->after('ppp_ip_address');
            $table->string('ppp_uptime')->nullable()->after('ppp_caller_id');
            $table->timestamp('last_ppp_checked_at')->nullable()->after('ppp_uptime');
            $table->timestamp('ppp_last_change_at')->nullable()->after('last_ppp_checked_at');
            $table->index(['router_id','ppp_status']);
        });
    }

    public function down(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->dropIndex(['router_id','ppp_status']);
            $table->dropColumn(['ppp_status','ppp_ip_address','ppp_caller_id','ppp_uptime','last_ppp_checked_at','ppp_last_change_at']);
        });
    }
};
