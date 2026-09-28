<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviationController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\DiscrepancyController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ParentPortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReliefController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SummaryController;
use App\Http\Controllers\TeacherPortalController;
use App\Http\Controllers\TimetableController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', fn () => redirect()->route('login'));
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login');
    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'send'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
    Route::get('/otp', [LoginController::class, 'otpForm'])->name('otp.create');
    Route::post('/otp', [LoginController::class, 'otpStore'])->name('otp.store');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    Route::get('/parent', [ParentPortalController::class, 'dashboard'])->name('parent.dashboard');
    Route::get('/parent/summaries', [ParentPortalController::class, 'summaries'])->name('parent.summaries');
    Route::get('/parent/summaries/{summary}', [ParentPortalController::class, 'summary'])->name('parent.summary');
    Route::get('/parent/discrepancy', [ParentPortalController::class, 'discrepancyForm'])->name('parent.discrepancy.create');
    Route::post('/parent/discrepancy', [ParentPortalController::class, 'discrepancyStore'])->name('parent.discrepancy.store');
    Route::get('/parent/cases/{case}', [ParentPortalController::class, 'caseShow'])->name('parent.case');
    Route::get('/parent/notifications', [ParentPortalController::class, 'notifications'])->name('parent.notifications');

    Route::get('/teacher', [TeacherPortalController::class, 'dashboard'])->name('teacher.dashboard');
    Route::get('/teacher/schedules/{schedule}/deviate', [TeacherPortalController::class, 'deviate'])->name('teacher.deviate');

    foreach ([
        'provinces', 'districts', 'zones', 'schools', 'academic-years', 'terms', 'classes',
        'subjects', 'students', 'parents', 'teachers', 'users',
    ] as $directory) {
        Route::get($directory, [DirectoryController::class, 'index'])->defaults('directory', $directory)->name($directory.'.index');
        Route::get($directory.'/create', [DirectoryController::class, 'create'])->defaults('directory', $directory)->name($directory.'.create');
        Route::post($directory, [DirectoryController::class, 'store'])->defaults('directory', $directory)->name($directory.'.store');
        Route::get($directory.'/{record}/edit', [DirectoryController::class, 'edit'])->defaults('directory', $directory)->name($directory.'.edit');
        Route::put($directory.'/{record}', [DirectoryController::class, 'update'])->defaults('directory', $directory)->name($directory.'.update');
    }

    Route::get('/timetable', [TimetableController::class, 'index'])->name('timetable.index');
    Route::get('/timetable/create', [TimetableController::class, 'create'])->name('timetable.create');
    Route::post('/timetable', [TimetableController::class, 'store'])->name('timetable.store');
    Route::post('/timetable/{version}/entries', [TimetableController::class, 'storeEntry'])->name('timetable.entries.store');
    Route::post('/timetable/{version}/approve', [TimetableController::class, 'approve'])->name('timetable.approve');
    Route::post('/timetable/{version}/publish', [TimetableController::class, 'publish'])->name('timetable.publish');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('/calendar', [CalendarController::class, 'store'])->name('calendar.store');
    Route::get('/leave', [LeaveController::class, 'index'])->name('leave.index');
    Route::post('/leave', [LeaveController::class, 'store'])->name('leave.store');
    Route::post('/leave/{leave}/approve', [LeaveController::class, 'approve'])->name('leave.approve');
    Route::get('/relief', [ReliefController::class, 'index'])->name('relief.index');
    Route::put('/relief/{relief}', [ReliefController::class, 'update'])->name('relief.update');

    Route::get('/deviations', [DeviationController::class, 'index'])->name('deviations.index');
    Route::get('/deviations/create', [DeviationController::class, 'create'])->name('deviations.create');
    Route::post('/deviations', [DeviationController::class, 'store'])->name('deviations.store');
    Route::get('/deviations/{deviation}', [DeviationController::class, 'show'])->name('deviations.show');
    Route::post('/deviations/{deviation}/approve', [DeviationController::class, 'approve'])->name('deviations.approve');
    Route::post('/deviations/{deviation}/reject', [DeviationController::class, 'reject'])->name('deviations.reject');
    Route::post('/deviations/{deviation}/correct', [DeviationController::class, 'correct'])->name('deviations.correct');

    Route::get('/summaries', [SummaryController::class, 'index'])->name('summaries.index');
    Route::post('/summaries/generate', [SummaryController::class, 'generate'])->name('summaries.generate');
    Route::get('/summaries/{summary}', [SummaryController::class, 'show'])->name('summaries.show');

    Route::get('/discrepancies', [DiscrepancyController::class, 'index'])->name('discrepancies.index');
    Route::get('/discrepancies/{case}', [DiscrepancyController::class, 'show'])->name('discrepancies.show');
    Route::post('/discrepancies/{case}/resolve', [DiscrepancyController::class, 'resolve'])->name('discrepancies.resolve');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
});
