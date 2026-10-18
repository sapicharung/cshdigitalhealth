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
        Schema::table('app_installations', function (Blueprint $table) {
            $table->timestamp('last_boot_time')->nullable()->after('is_online');
            $table->string('uptime_text', 100)->nullable()->after('last_boot_time');
            $table->boolean('anydesk_installed')->default(false)->after('uptime_text');
            $table->string('anydesk_id', 50)->nullable()->after('anydesk_installed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_installations', function (Blueprint $table) {
            $table->dropColumn(['last_boot_time', 'uptime_text', 'anydesk_installed', 'anydesk_id']);
        });
    }
};
