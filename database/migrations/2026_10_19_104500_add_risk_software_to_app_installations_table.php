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
            $table->boolean('has_risk_software')->default(false)->after('anydesk_id')->index();
            $table->text('risk_software_list')->nullable()->after('has_risk_software');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_installations', function (Blueprint $table) {
            $table->dropIndex(['has_risk_software']);
            $table->dropColumn(['has_risk_software', 'risk_software_list']);
        });
    }
};

