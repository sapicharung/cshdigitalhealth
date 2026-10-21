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
        if (!Schema::hasColumn('blood_alc_records', 'test_date')) {
            Schema::table('blood_alc_records', function (Blueprint $table) {
                $table->date('test_date')->nullable()->after('test_day')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('blood_alc_records', 'test_date')) {
            Schema::table('blood_alc_records', function (Blueprint $table) {
                $table->dropColumn('test_date');
            });
        }
    }
};

