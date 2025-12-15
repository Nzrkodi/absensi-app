<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\UserController;

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

Route::get('/', function () {
    return view('welcome');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
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
    
    Route::resource('students', StudentController::class);
    Route::resource('users', UserController::class);
});

// Temporary logout route
Route::post('/logout', function () {
    auth()->logout();
    return redirect('/');
})->name('logout');
