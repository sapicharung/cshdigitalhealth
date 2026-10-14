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
            $table->string('cpu')->nullable()->after('browser');
            $table->string('ram')->nullable()->after('cpu');
            $table->string('disk')->nullable()->after('ram');
            $table->boolean('remote_ready')->default(true)->after('disk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_installations', function (Blueprint $table) {
            $table->dropColumn(['cpu', 'ram', 'disk', 'remote_ready']);
        });
    }
};
