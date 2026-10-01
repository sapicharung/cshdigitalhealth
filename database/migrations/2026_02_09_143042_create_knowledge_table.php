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
                $table->string('title'); // หัวข้อความรู้
                $table->string('category'); // หมวดหมู่ (cpg, research, bcp, hosxp)
                $table->text('description')->nullable(); // รายละเอียด
                $table->string('file_path'); // พาธที่เก็บไฟล์
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
