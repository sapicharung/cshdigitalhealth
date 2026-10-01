<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    if (!Schema::hasTable('knowledge')) {
        Schema::create('knowledge', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // ชื่อไฟล์/หัวข้อ
            $table->string('category'); // cpg, research, bcp, hosxp
            $table->string('file_path'); // ที่อยู่ไฟล์ใน Server
            $table->timestamps();
        });
    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge');
    }
};
