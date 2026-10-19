<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('risk_software_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('ชื่อแสดงของโปรแกรมเสี่ยง เช่น TeamViewer, BlueStacks X');
            $table->string('category')->default('remote')->comment('หมวดหมู่: remote, emulator, other');
            $table->text('process_names')->nullable()->comment('ชื่อโปรเซสคั่นด้วยจุลภาค เช่น TeamViewer*, tv_w32');
            $table->text('service_names')->nullable()->comment('ชื่อเซอร์วิสคั่นด้วยจุลภาค เช่น TeamViewer, TeamViewerService');
            $table->text('registry_patterns')->nullable()->comment('คีย์เวิร์ดใน Registry คั่นด้วยจุลภาค');
            $table->text('file_paths')->nullable()->comment('โฟลเดอร์หรือพาธไฟล์คั่นด้วยบรรทัดใหม่หรือจุลภาค');
            $table->string('severity')->default('high')->comment('ระดับความเสี่ยง: low, medium, high, critical');
            $table->boolean('is_active')->default(true)->comment('เปิดใช้งานการตรวจจับหรือไม่');
            $table->text('description')->nullable()->comment('คำอธิบาย / เหตุผลความเสี่ยง');
            $table->timestamps();
        });

        // Seed default rules
        $defaults = [
            [
                'name' => 'TeamViewer',
                'category' => 'remote',
                'process_names' => 'TeamViewer, TeamViewer_Service, tv_w32, tv_x64',
                'service_names' => 'TeamViewer',
                'registry_patterns' => 'TeamViewer',
                'file_paths' => "C:\\Program Files\\TeamViewer\nC:\\Program Files (x86)\\TeamViewer",
                'severity' => 'high',
                'description' => 'โปรแกรมรีโมทคอมพิวเตอร์ภายนอก ไม่ผ่านระบบความปลอดภัยของ รพ.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Chrome Remote Desktop',
                'category' => 'remote',
                'process_names' => 'remoting_host, remoting_desktop',
                'service_names' => 'chromoting',
                'registry_patterns' => 'Chrome Remote Desktop',
                'file_paths' => "C:\\Program Files\\Google\\Chrome Remote Desktop\nC:\\Program Files (x86)\\Google\\Chrome Remote Desktop",
                'severity' => 'high',
                'description' => 'ส่วนขยายรีโมทเดสก์ท็อปผ่าน Google Chrome ข้ามเครือข่าย',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'UltraViewer',
                'category' => 'remote',
                'process_names' => 'UltraViewer, UltraViewer_Desktop, UltraViewer_Service',
                'service_names' => 'UltraViewerService',
                'registry_patterns' => 'UltraViewer',
                'file_paths' => "C:\\Program Files\\UltraViewer\nC:\\Program Files (x86)\\UltraViewer",
                'severity' => 'high',
                'description' => 'โปรแกรมควบคุมคอมพิวเตอร์ระยะไกลผ่านอินเทอร์เน็ต',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'RustDesk',
                'category' => 'remote',
                'process_names' => 'rustdesk',
                'service_names' => 'rustdesk',
                'registry_patterns' => 'RustDesk',
                'file_paths' => "C:\\Program Files\\RustDesk\nC:\\Program Files (x86)\\RustDesk\nC:\\ProgramData\\RustDesk",
                'severity' => 'high',
                'description' => 'โปรแกรมรีโมทระยะไกลโอเพ่นซอร์ส',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Windows Remote Desktop (RDP Client)',
                'category' => 'remote',
                'process_names' => 'mstsc, msrdc',
                'service_names' => '',
                'registry_patterns' => 'Microsoft Remote Desktop',
                'file_paths' => '',
                'severity' => 'medium',
                'description' => 'การเชื่อมต่อรีโมทเดสก์ท็อป Windows ภายนอก',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BlueStacks',
                'category' => 'emulator',
                'process_names' => 'HD-Player, BlueStacks',
                'service_names' => 'BstHdAndroidSvc, BstHdLogRotatorSvc',
                'registry_patterns' => 'BlueStacks',
                'file_paths' => "C:\\Program Files\\BlueStacks\nC:\\Program Files (x86)\\BlueStacks\nC:\\ProgramData\\BlueStacks",
                'severity' => 'high',
                'description' => 'โปรแกรมจำลองแอนดรอยด์ BlueStacks App Player (เวอร์ชัน 4 หรือ 5)',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BlueStacks X',
                'category' => 'emulator',
                'process_names' => 'BlueStacksX, BlueStacksWeb, BSX',
                'service_names' => 'BlueStacksXService',
                'registry_patterns' => 'BlueStacks X, BlueStacks 10',
                'file_paths' => "C:\\Program Files\\BlueStacks_nxt\nC:\\Program Files (x86)\\BlueStacks_nxt",
                'severity' => 'high',
                'description' => 'โปรแกรมจำลองแอนดรอยด์บนคลาวด์ BlueStacks X (เวอร์ชัน 10)',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'LDPlayer',
                'category' => 'emulator',
                'process_names' => 'dnplayer, LdBoxHeadless, dnmultiplayer',
                'service_names' => '',
                'registry_patterns' => 'LDPlayer',
                'file_paths' => "C:\\LDPlayer\nC:\\LDPlayer9\nC:\\Program Files\\LDPlayer",
                'severity' => 'high',
                'description' => 'โปรแกรมจำลองระบบ Android บนคอมพิวเตอร์ LDPlayer',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'NoxPlayer',
                'category' => 'emulator',
                'process_names' => 'Nox, NoxVMSrv, nox_adb',
                'service_names' => '',
                'registry_patterns' => 'Nox',
                'file_paths' => "C:\\Program Files\\Nox\nC:\\Program Files (x86)\\Nox\nC:\\Program Files\\Bignox",
                'severity' => 'high',
                'description' => 'โปรแกรมจำลองระบบ Android บนคอมพิวเตอร์ NoxPlayer',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'MEmu Play',
                'category' => 'emulator',
                'process_names' => 'MEmu, MEmuConsole, MEmuHeadless',
                'service_names' => '',
                'registry_patterns' => 'MEmu',
                'file_paths' => "C:\\Program Files\\Microvirt\\MEmu\nC:\\Program Files (x86)\\Microvirt\\MEmu",
                'severity' => 'high',
                'description' => 'โปรแกรมจำลอง Android บน PC แบรนด์ MEmu Play',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'MuMu Player',
                'category' => 'emulator',
                'process_names' => 'MuMuPlayer, NemuPlayer',
                'service_names' => '',
                'registry_patterns' => 'MuMu',
                'file_paths' => "C:\\Program Files\\Netease\\MuMu\nC:\\Program Files (x86)\\Netease\\MuMu",
                'severity' => 'high',
                'description' => 'โปรแกรมจำลอง Android บน PC แบรนด์ MuMu Player (NetEase)',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'GameLoop',
                'category' => 'emulator',
                'process_names' => 'AppMarket, AndroidProcess',
                'service_names' => '',
                'registry_patterns' => 'GameLoop, TxGameAssistant',
                'file_paths' => "C:\\Program Files\\TxGameAssistant\nD:\\TxGameAssistant",
                'severity' => 'high',
                'description' => 'โปรแกรมจำลอง Tencent GameLoop',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Navicat',
                'category' => 'other',
                'process_names' => 'navicat, navicat.exe, navicat15, navicat16, navicat_premium',
                'service_names' => '',
                'registry_patterns' => 'Navicat, PremiumSoft',
                'file_paths' => "C:\\Program Files\\PremiumSoft\\Navicat*\nC:\\Program Files (x86)\\PremiumSoft\\Navicat*\nC:\\Program Files\\Navicat*\nC:\\Program Files (x86)\\Navicat*",
                'severity' => 'high',
                'description' => 'โปรแกรมจัดการฐานข้อมูล Navicat (Premium / MySQL / PostgreSQL / MariaDB)',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('risk_software_rules')->insert($defaults);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_software_rules');
    }
};
