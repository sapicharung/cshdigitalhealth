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
            $table->boolean('hosxp_installed')->default(false)->after('office_version');
            $table->string('hosxp_version', 100)->nullable()->after('hosxp_installed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_installations', function (Blueprint $table) {
            $table->dropColumn(['hosxp_installed', 'hosxp_version']);
        });
    }
};
