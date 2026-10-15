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
            $table->string('os_version')->nullable()->after('os');
            $table->string('office_version')->nullable()->after('os_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_installations', function (Blueprint $table) {
            $table->dropColumn(['os_version', 'office_version']);
        });
    }
};

