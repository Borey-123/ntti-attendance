<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\RfidCardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\SecurityLogController;
use App\Http\Controllers\PortalController;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Language Switcher (public, no auth needed)
Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'km'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');

Route::get('/lang-portal/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'km'])) {
        session(['portal_locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch.portal');

Route::get('/lang-live/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'km'])) {
        session(['live_locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch.live');

// Live Monitor (public, no auth needed)
Route::get('/live', [AttendanceController::class, 'liveMonitor'])->name('live.monitor');
Route::get('/api-live/latest', [AttendanceController::class, 'latest'])->name('api.live.latest');

// Teacher Portal Enhancements
Route::get('/portal/export', [PortalController::class, 'export'])->name('portal.export');
Route::post('/portal/correction', [PortalController::class, 'storeCorrection'])->name('portal.correction.store');
Route::post('/settings/attendance-corrections/{correction}', [SettingController::class, 'handleCorrection'])->name('settings.attendance_corrections.handle');

Route::get('/portal', [PortalController::class, 'index'])->name('portal.index');
Route::get('/api-web/portal/search', [PortalController::class, 'search'])->name('api.portal.search');

// Architecture Diagram (public, no auth needed)
Route::get('/architecture', function () {
    return response()->file(public_path('system_diagram.html'));
})->name('architecture.diagram');



// Protected web routes
Route::middleware('auth')->group(function () {
    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [AttendanceController::class, 'index'])->name('dashboard');
    Route::get('/scan', [AttendanceController::class, 'scanPage'])->name('scan.index');
    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::get('/rfid', [RfidCardController::class, 'index'])->name('rfid.index');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');

    // AJAX / JSON endpoints for Blade pages
    Route::get('/api-web/teachers', [TeacherController::class, 'index'])->name('api.teachers.list');
    Route::post('/api-web/teachers', [TeacherController::class, 'store'])->name('api.teachers.store');
    Route::put('/api-web/teachers/{teacher}', [TeacherController::class, 'update'])->name('api.teachers.update');
    Route::delete('/api-web/teachers/{teacher}', [TeacherController::class, 'destroy'])->name('api.teachers.destroy');

    Route::get('/api-web/rfid-cards', [RfidCardController::class, 'index'])->name('api.rfid.list');
    Route::post('/api-web/rfid-cards', [RfidCardController::class, 'store'])->name('api.rfid.store');
    Route::put('/api-web/rfid-cards/{rfidCard}', [RfidCardController::class, 'update'])->name('api.rfid.update');
    Route::delete('/api-web/rfid-cards/{rfidCard}', [RfidCardController::class, 'destroy'])->name('api.rfid.destroy');
    Route::get('/api-web/rfid-check', [RfidCardController::class, 'checkUid'])->name('api.rfid.check');
    Route::get('/api-web/rfid-pending-scan', [RfidCardController::class, 'pendingScan'])->name('api.rfid.pending-scan');

    Route::get('/api-web/attendance', [AttendanceController::class, 'index'])->name('api.attendance.index');
    Route::post('/api-web/attendance/manual', [AttendanceController::class, 'manual'])->name('api.attendance.manual');
    Route::post('/api-web/attendance/admin-scan', [AttendanceController::class, 'adminScan'])->name('api.attendance.admin-scan');
    Route::get('/api-web/attendance/list', [AttendanceController::class, 'list'])->name('api.attendance.list');
    
    Route::get('/api-web/reports', [ReportController::class, 'getData'])->name('api.reports');
    Route::get('/api-web/reports/export-csv', [ReportController::class, 'exportCsv'])->name('api.reports.export');
    Route::get('/api-web/reports/teacher-summary', [ReportController::class, 'teacherSummary'])->name('api.reports.teacher-summary');
    Route::get('/api-web/reports/absent', [ReportController::class, 'absentReport'])->name('api.reports.absent');
    Route::get('/api-web/reports/late', [ReportController::class, 'lateReport'])->name('api.reports.late');
    Route::get('/api-web/reports/leave', [ReportController::class, 'leaveReport'])->name('api.reports.leave');
    Route::get('/api-web/reports/individual', [ReportController::class, 'individualReport'])->name('api.reports.individual');
    Route::get('/api-web/reports/department', [ReportController::class, 'departmentReport'])->name('api.reports.department');
    
    // Attendance Edit & Manual
    Route::post('/api-web/reports/attendance/manual', [ReportController::class, 'storeManualAttendance'])->name('api.reports.attendance.manual');
    Route::put('/api-web/reports/attendance/{id}', [ReportController::class, 'updateAttendance'])->name('api.reports.attendance.update');
    Route::get('/api-web/reports/attendance/{id}/history', [ReportController::class, 'getAttendanceHistory'])->name('api.reports.attendance.history');
    Route::get('/api-web/device-status', [AttendanceController::class, 'deviceStatus'])->name('api.device.status');
    Route::get('/api-web/teachers/departments', [TeacherController::class, 'departments'])->name('api.departments');
    Route::get('/api-web/teachers/{teacher}/insights', [TeacherController::class, 'insights'])->name('api.teachers.insights');

    Route::get('/api-web/departments', [DepartmentController::class, 'index'])->name('api.departments.list');
    Route::post('/api-web/departments', [DepartmentController::class, 'store'])->name('api.departments.store');
    Route::put('/api-web/departments/{department}', [DepartmentController::class, 'update'])->name('api.departments.update');
    Route::delete('/api-web/departments/{department}', [DepartmentController::class, 'destroy'])->name('api.departments.destroy');

    Route::post('/api-web/holidays', [\App\Http\Controllers\HolidayController::class, 'store'])->name('api.holidays.store');
    Route::post('/api-web/holidays/auto-fill-cambodia', [\App\Http\Controllers\HolidayController::class, 'autoFillCambodia'])->name('api.holidays.autofill');
    Route::delete('/api-web/holidays/{holiday}', [\App\Http\Controllers\HolidayController::class, 'destroy'])->name('api.holidays.destroy');

    // Security & Integrity
    Route::get('/security', [SecurityLogController::class, 'index'])->name('security.index');
    Route::post('/security/clear-cache', [SecurityLogController::class, 'clearCache'])->name('security.clear-cache');
    Route::get('/api-web/security/integrity', [SecurityLogController::class, 'integrityCheck'])->name('security.integrity');

    // Attendance Manual Adjustment
    Route::put('/api-web/attendance/adjustment', [AttendanceController::class, 'manualAdjustment'])->name('api.attendance.adjustment');

    // Settings Routes
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/settings/backup', [SettingController::class, 'downloadBackup'])->name('settings.backup');
    Route::post('/settings/system-cleanup', [SettingController::class, 'runSystemCleanup'])->name('settings.cleanup');
    Route::get('/settings/telegram-chats', [SettingController::class, 'fetchTelegramChats'])->name('settings.telegram.chats');
    Route::post('/settings/appearance', [SettingController::class, 'updateAppearance'])->name('settings.appearance.update');
    Route::post('/settings/admin', [SettingController::class, 'storeAdmin'])->name('settings.admin.store');
    Route::post('/settings/admin/{user}/reset-password', [SettingController::class, 'resetAdminPassword'])->name('settings.admin.reset-password');
});
