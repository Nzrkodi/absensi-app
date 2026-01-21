<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Debug route (temporary)
Route::get('/debug-auth', function() {
    $user = auth()->user();
    return response()->json([
        'authenticated' => auth()->check(),
        'user' => $user ? [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ] : null,
        'session_id' => session()->getId(),
        'csrf_token' => csrf_token()
    ]);
});

// Admin Routes (Protected by auth middleware)
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    
    // Routes accessible by both admin and teacher
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Attendance routes (accessible by both admin and teacher)
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/{student}/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/{student}/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
    Route::post('/attendance/{student}/note', [AttendanceController::class, 'updateNote'])->name('attendance.note');
    Route::post('/attendance/{student}/bulk-permission', [AttendanceController::class, 'bulkPermission'])->name('attendance.bulk-permission');
    Route::get('/attendance/{student}/data/{date?}', [AttendanceController::class, 'getAttendanceData'])->name('attendance.data');
    Route::get('/attendance/{attendance}/detail', [AttendanceController::class, 'getAttendanceDetail'])->name('attendance.detail');
    
    // Settings routes (accessible by both admin and teacher)
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/reset', [\App\Http\Controllers\Admin\SettingController::class, 'reset'])->name('settings.reset');
    
    // Profile routes (accessible by both admin and teacher)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.remove-avatar');
    
    // Admin-only routes
    Route::middleware('role:admin')->group(function () {
        // Settings management (admin only - reset and test only)
        Route::get('/settings/test', [\App\Http\Controllers\Admin\SettingController::class, 'test'])->name('settings.test');
        
        // Holiday routes (admin only)
        Route::get('/holidays', [\App\Http\Controllers\Admin\HolidayController::class, 'index'])->name('holidays.index');
        Route::post('/holidays', [\App\Http\Controllers\Admin\HolidayController::class, 'store'])->name('holidays.store');
        Route::put('/holidays/{holiday}', [\App\Http\Controllers\Admin\HolidayController::class, 'update'])->name('holidays.update');
        Route::delete('/holidays/{holiday}', [\App\Http\Controllers\Admin\HolidayController::class, 'destroy'])->name('holidays.destroy');
        Route::patch('/holidays/{holiday}/toggle', [\App\Http\Controllers\Admin\HolidayController::class, 'toggle'])->name('holidays.toggle');
        Route::post('/holidays/weekends', [\App\Http\Controllers\Admin\HolidayController::class, 'createWeekends'])->name('holidays.weekends');
        Route::get('/holidays/check-today', [\App\Http\Controllers\Admin\HolidayController::class, 'checkToday'])->name('holidays.check-today');
        Route::post('/holidays/auto-sync', [\App\Http\Controllers\Admin\HolidayController::class, 'autoSync'])->name('holidays.auto-sync');
        Route::post('/holidays/clear-cache', [\App\Http\Controllers\Admin\HolidayController::class, 'clearCache'])->name('holidays.clear-cache');
        
        // Report routes (admin only)
        Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/preview', [\App\Http\Controllers\Admin\ReportController::class, 'preview'])->name('reports.preview');
        Route::get('/reports/student/{student}', [\App\Http\Controllers\Admin\ReportController::class, 'student'])->name('reports.student');
        Route::get('/reports/export', [\App\Http\Controllers\Admin\ReportController::class, 'export'])->name('reports.export');
        
        // Student management (admin only)
        Route::delete('students/delete-all', [StudentController::class, 'deleteAll'])->name('students.delete-all');
        Route::post('students/bulk-delete', [StudentController::class, 'bulkDelete'])->name('students.bulk-delete');
        Route::post('students/bulk-update-status', [StudentController::class, 'bulkUpdateStatus'])->name('students.bulk-update-status');
        Route::resource('students', StudentController::class);
        Route::post('students/import', [StudentController::class, 'import'])->name('students.import');
        Route::get('students/template/download', [StudentController::class, 'downloadTemplate'])->name('students.template');
        
        // User management (admin only)
        Route::resource('users', UserController::class)->middleware('protect.admin');
        
        // Violation Types management (admin only)
        Route::resource('violation-types', \App\Http\Controllers\Admin\ViolationTypeController::class);
        Route::patch('violation-types/{violationType}/toggle-status', [\App\Http\Controllers\Admin\ViolationTypeController::class, 'toggleStatus'])->name('violation-types.toggle-status');
        
        // Student Violations management (admin only)
        Route::resource('student-violations', \App\Http\Controllers\Admin\StudentViolationController::class);
        Route::get('students/{student}/violations', [\App\Http\Controllers\Admin\StudentViolationController::class, 'getStudentViolations'])->name('students.violations');
        Route::get('students/{student}/info', [\App\Http\Controllers\Admin\StudentViolationController::class, 'getStudentInfo'])->name('students.info');
        
        // School Locations management (admin only)
        Route::resource('school-locations', \App\Http\Controllers\Admin\SchoolLocationController::class);
        
        // Storage management (admin only)
        Route::get('storage/attendance-photos', [\App\Http\Controllers\Admin\StorageController::class, 'attendancePhotos'])->name('storage.attendance-photos');
        Route::post('storage/cleanup', [\App\Http\Controllers\Admin\StorageController::class, 'cleanupPhotos'])->name('storage.cleanup');
        Route::get('storage/stats', [\App\Http\Controllers\Admin\StorageController::class, 'getStorageStats'])->name('storage.stats');
    });
});

// Student Routes (Mobile Attendance)
Route::prefix('student')->name('student.')->group(function () {
    // Login routes
    Route::get('/login', [\App\Http\Controllers\Student\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Student\LoginController::class, 'login'])->name('login.submit');
    Route::get('/logout', [\App\Http\Controllers\Student\LoginController::class, 'logout'])->name('logout');
    
    // Attendance routes (require student session)
    Route::get('/attendance', [\App\Http\Controllers\Student\AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock-in', [\App\Http\Controllers\Student\AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/clock-out', [\App\Http\Controllers\Student\AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
});

// API Routes for mobile/AJAX
Route::prefix('api')->group(function () {
    Route::get('/school-locations/active', [\App\Http\Controllers\Admin\SchoolLocationController::class, 'getActiveLocations']);
    Route::post('/school-locations/validate', [\App\Http\Controllers\Admin\SchoolLocationController::class, 'validateLocation']);
});


