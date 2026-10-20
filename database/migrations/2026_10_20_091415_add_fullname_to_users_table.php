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
        Schema::table('users', function (Blueprint $table) {
            $table->string('fullname')->nullable()->after('name');
            $table->string('email')->nullable()->change();
        });

        // กำหนดชื่อ-สกุลเริ่มต้นให้บัญชีที่มีอยู่เดิม
        \Illuminate\Support\Facades\DB::table('users')
            ->where('name', 'admin')
            ->whereNull('fullname')
            ->update(['fullname' => 'ผู้ดูแลระบบ']);

        \Illuminate\Support\Facades\DB::table('users')
            ->where('name', 'it_staff')
            ->whereNull('fullname')
            ->update(['fullname' => 'เจ้าหน้าที่ไอที']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('fullname');
        });
    }
};
