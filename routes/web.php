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

// 3. ติดตามการติดตั้งแอพ และดาวน์โหลดตัวสร้างทางลัด (Client tracking & Shortcut Installer)
Route::post('/track-install', [AppInstallationController::class, 'track'])->name('track.install');
Route::post('/api/track-install', [AppInstallationController::class, 'track'])->name('api.track.install');
Route::post('/track-uninstall', [AppInstallationController::class, 'trackUninstall'])->name('track.uninstall');
Route::post('/api/track-uninstall', [AppInstallationController::class, 'trackUninstall'])->name('api.track.uninstall');
Route::post('/api/install-desktop-shortcut', [AppInstallationController::class, 'installDesktopShortcut'])->name('api.install.desktop');
Route::get('/api/client/version', [AppInstallationController::class, 'clientVersion'])->name('api.client.version');
Route::get('/api/client/update-script', [AppInstallationController::class, 'clientUpdateScript'])->name('api.client.update-script');
Route::get('/download-shortcut-installer', [AppInstallationController::class, 'downloadShortcutInstaller'])->name('download.shortcut.installer');
Route::get('/download-autosync-installer', [AppInstallationController::class, 'downloadAutoSyncInstaller'])->name('download.autosync.installer');
Route::get('/download-uninstaller', [AppInstallationController::class, 'downloadUninstaller'])->name('download.uninstaller');
Route::get('/download-url-shortcut', [AppInstallationController::class, 'downloadUrlShortcut'])->name('download.url.shortcut');

// 4. เข้าสู่ระบบผู้ดูแลระบบ (Admin Authentication)
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('login.post');

// 5. ระบบตรวจสอบการติดตั้งสำหรับผู้ดูแล (Admin Tracking Dashboard - Protected)
Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/installations', [AppInstallationController::class, 'index'])->name('admin.installations');
    Route::put('/installations/{id}', [AppInstallationController::class, 'update'])->name('admin.installations.update');
    Route::post('/installations/{id}/reset', [AppInstallationController::class, 'reset'])->name('admin.installations.reset');
    Route::post('/installations/reset-all', [AppInstallationController::class, 'resetAll'])->name('admin.installations.reset-all');
    Route::post('/installations/clear-offline', [AppInstallationController::class, 'clearOffline'])->name('admin.installations.clear-offline');
    Route::post('/installations/{id}/trigger-update', [AppInstallationController::class, 'triggerUpdate'])->name('admin.installations.trigger-update');
    Route::post('/installations/{id}/cancel-update', [AppInstallationController::class, 'cancelUpdate'])->name('admin.installations.cancel-update');
    Route::post('/installations/{id}/complete-update', [AppInstallationController::class, 'completeUpdate'])->name('admin.installations.complete-update');
    Route::post('/installations/clear-pending', [AppInstallationController::class, 'clearAllPending'])->name('admin.installations.clear-pending');
    Route::post('/installations/trigger-update-all', [AppInstallationController::class, 'triggerUpdateAll'])->name('admin.installations.trigger-update-all');
    Route::post('/installations/{id}/refresh', [AppInstallationController::class, 'refreshDevice'])->name('admin.installations.refresh');
    Route::post('/installations/{id}/resync-specs', [AppInstallationController::class, 'resyncSpecs'])->name('admin.installations.resync-specs');
    Route::post('/installations/resync-all-specs', [AppInstallationController::class, 'resyncAllSpecs'])->name('admin.installations.resync-all-specs');
    Route::post('/installations/check-status', [AppInstallationController::class, 'checkLiveStatus'])->name('admin.installations.check-status');
    Route::delete('/installations/{id}', [AppInstallationController::class, 'destroy'])->name('admin.installations.destroy');
    Route::get('/installations/export', [AppInstallationController::class, 'exportCsv'])->name('admin.installations.export');
    Route::get('/installations/{id}/vnc', [AppInstallationController::class, 'downloadVnc'])->name('admin.installations.vnc');
    Route::get('/installations/{id}/rdp', [AppInstallationController::class, 'downloadRdp'])->name('admin.installations.rdp');
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