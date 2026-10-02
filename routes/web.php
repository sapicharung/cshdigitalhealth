<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KnowledgeController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AppInstallationController;
use App\Http\Controllers\BloodAlcController;

/*
|--------------------------------------------------------------------------
| Web Routes - Chiang Saen Hospital (CSH Digital Health)
|--------------------------------------------------------------------------
*/

// 1. หน้าแรกของเว็บไซต์
Route::get('/', function () {
    return view('welcome');
})->name('home');

// 2. กลุ่มเส้นทางคลังความรู้ (Knowledge Management)
Route::prefix('knowledge')->group(function () {
    // หน้าหลักคลังความรู้ (แสดงรายการล่าสุดหน้าละ 20 รายการ)
    Route::get('/', [KnowledgeController::class, 'index'])->name('knowledge');

    // หน้าฟอร์มอัปโหลดความรู้
    Route::get('/upload', [KnowledgeController::class, 'create'])->name('knowledge.upload');

    // บันทึกข้อมูลการอัปโหลด (POST)
    Route::post('/store', [KnowledgeController::class, 'store'])->name('knowledge.store');

    // หน้าคู่มือระบบโรงพยาบาล (HOSxP)
    Route::get('/hospital-system', [KnowledgeController::class, 'hospitalSystem'])->name('knowledge.hosxp');

    // หน้าแสดงผลแยกตามหมวดหมู่ (CPG, Research, BCP)
    Route::get('/category/{category}', [KnowledgeController::class, 'showCategory'])->name('knowledge.category');
    Route::get('/{category}', [KnowledgeController::class, 'showCategory'])->name('knowledge.category.short');
});

// 3. ติดตามการติดตั้งแอพ (Beacon / Client tracking - Public)
Route::post('/track-install', [AppInstallationController::class, 'track'])->name('track.install');
Route::post('/api/track-install', [AppInstallationController::class, 'track'])->name('api.track.install');

// 4. เข้าสู่ระบบผู้ดูแลระบบ (Admin Authentication)
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('login.post');

// 5. ระบบตรวจสอบการติดตั้งสำหรับผู้ดูแล (Admin Tracking Dashboard - Protected)
Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/installations', [AppInstallationController::class, 'index'])->name('admin.installations');
    Route::put('/installations/{id}', [AppInstallationController::class, 'update'])->name('admin.installations.update');
    Route::post('/installations/{id}/reset', [AppInstallationController::class, 'reset'])->name('admin.installations.reset');
    Route::post('/installations/reset-all', [AppInstallationController::class, 'resetAll'])->name('admin.installations.reset-all');
    Route::delete('/installations/{id}', [AppInstallationController::class, 'destroy'])->name('admin.installations.destroy');
    Route::get('/installations/export', [AppInstallationController::class, 'exportCsv'])->name('admin.installations.export');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});

// 6. ระบบทะเบียนส่งตรวจปริมาณแอลกอฮอล์ในเลือด (Blood Alcohol Testing Registry)
Route::prefix('blood-alc')->group(function () {
    Route::get('/', [BloodAlcController::class, 'index'])->name('blood-alc.index');
    Route::post('/', [BloodAlcController::class, 'store'])->name('blood-alc.store');
    Route::get('/export', [BloodAlcController::class, 'export'])->name('blood-alc.export');
    Route::get('/lookup-patient', [BloodAlcController::class, 'lookupPatient'])->name('blood-alc.lookup-patient');
    Route::get('/{id}', [BloodAlcController::class, 'show'])->name('blood-alc.show');
    Route::put('/{id}', [BloodAlcController::class, 'update'])->name('blood-alc.update');
    Route::delete('/{id}', [BloodAlcController::class, 'destroy'])->name('blood-alc.destroy');
});