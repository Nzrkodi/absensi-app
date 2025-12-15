<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::getAttendanceSettings();
        $allSettings = Setting::orderBy('key')->get()->groupBy(function($item) {
            if (in_array($item->key, ['school_start_time', 'late_tolerance_minutes', 'auto_absent_time'])) {
                return 'attendance';
            } elseif (in_array($item->key, ['school_name'])) {
                return 'general';
            } else {
                return 'other';
            }
        });

        return view('admin.settings.index', compact('settings', 'allSettings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'school_start_time' => 'required|date_format:H:i',
            'late_tolerance_minutes' => 'required|integer|min:0|max:120',
            'auto_absent_time' => 'required|date_format:H:i',
            'school_name' => 'required|string|max:255',
            'allow_early_clockin' => 'boolean'
        ]);

        try {
            // Update attendance settings
            Setting::set('school_start_time', $request->school_start_time, 'time', 'Waktu mulai sekolah');
            Setting::set('late_tolerance_minutes', $request->late_tolerance_minutes, 'integer', 'Toleransi keterlambatan dalam menit');
            Setting::set('auto_absent_time', $request->auto_absent_time, 'time', 'Waktu otomatis menandai siswa absent');
            
            // Update general settings
            Setting::set('school_name', $request->school_name, 'string', 'Nama sekolah');
            Setting::set('allow_early_clockin', $request->has('allow_early_clockin') ? 'true' : 'false', 'boolean', 'Izinkan clock in sebelum jam mulai sekolah');

            return redirect()->route('admin.settings.index')
                ->with('success', 'Pengaturan berhasil disimpan');

        } catch (\Exception $e) {
            return redirect()->route('admin.settings.index')
                ->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }

    public function reset()
    {
        try {
            // Reset to default values
            Setting::set('school_start_time', '07:00', 'time', 'Waktu mulai sekolah');
            Setting::set('late_tolerance_minutes', '15', 'integer', 'Toleransi keterlambatan dalam menit');
            Setting::set('auto_absent_time', '15:00', 'time', 'Waktu otomatis menandai siswa absent');
            Setting::set('school_name', 'SMA Negeri 1', 'string', 'Nama sekolah');
            Setting::set('allow_early_clockin', 'true', 'boolean', 'Izinkan clock in sebelum jam mulai sekolah');

            return redirect()->route('admin.settings.index')
                ->with('success', 'Pengaturan berhasil direset ke default');

        } catch (\Exception $e) {
            return redirect()->route('admin.settings.index')
                ->with('error', 'Gagal mereset pengaturan: ' . $e->getMessage());
        }
    }
}