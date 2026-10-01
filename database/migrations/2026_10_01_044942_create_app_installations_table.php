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
        Schema::create('app_installations', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique();
            $table->string('ip_address')->nullable()->index();
            $table->string('hostname')->nullable();
            $table->string('department')->nullable();
            $table->string('device_name')->nullable();
            $table->string('os')->nullable();
            $table->string('browser')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('install_type')->default('pwa');
            $table->unsignedInteger('launch_count')->default(1);
            $table->timestamp('first_installed_at')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_installations');
    }
};
