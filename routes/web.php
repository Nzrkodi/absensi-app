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

// Admin Routes (Protected by auth middleware)
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Attendance routes
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/{student}/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/{student}/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
    Route::post('/attendance/{student}/note', [AttendanceController::class, 'updateNote'])->name('attendance.note');
    Route::get('/attendance/{student}/data/{date?}', [AttendanceController::class, 'getAttendanceData'])->name('attendance.data');
    
    // Settings routes
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/reset', [\App\Http\Controllers\Admin\SettingController::class, 'reset'])->name('settings.reset');
    Route::get('/settings/test', [\App\Http\Controllers\Admin\SettingController::class, 'test'])->name('settings.test');
    
    // Holiday routes
    Route::get('/holidays', [\App\Http\Controllers\Admin\HolidayController::class, 'index'])->name('holidays.index');
    Route::post('/holidays', [\App\Http\Controllers\Admin\HolidayController::class, 'store'])->name('holidays.store');
    Route::put('/holidays/{holiday}', [\App\Http\Controllers\Admin\HolidayController::class, 'update'])->name('holidays.update');
    Route::delete('/holidays/{holiday}', [\App\Http\Controllers\Admin\HolidayController::class, 'destroy'])->name('holidays.destroy');
    Route::patch('/holidays/{holiday}/toggle', [\App\Http\Controllers\Admin\HolidayController::class, 'toggle'])->name('holidays.toggle');
    Route::post('/holidays/weekends', [\App\Http\Controllers\Admin\HolidayController::class, 'createWeekends'])->name('holidays.weekends');
    Route::get('/holidays/check-today', [\App\Http\Controllers\Admin\HolidayController::class, 'checkToday'])->name('holidays.check-today');
    Route::post('/holidays/auto-sync', [\App\Http\Controllers\Admin\HolidayController::class, 'autoSync'])->name('holidays.auto-sync');
    Route::post('/holidays/clear-cache', [\App\Http\Controllers\Admin\HolidayController::class, 'clearCache'])->name('holidays.clear-cache');
    
    // Report routes
    Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/preview', [\App\Http\Controllers\Admin\ReportController::class, 'preview'])->name('reports.preview');
    Route::get('/reports/student/{student}', [\App\Http\Controllers\Admin\ReportController::class, 'student'])->name('reports.student');
    Route::get('/reports/export', [\App\Http\Controllers\Admin\ReportController::class, 'export'])->name('reports.export');
    
    Route::resource('students', StudentController::class);
    Route::resource('users', UserController::class);
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});


