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
        // Debug: Log incoming request
        \Log::info('Settings Update Request', [
            'school_start_time' => $request->school_start_time,
            'late_tolerance_minutes' => $request->late_tolerance_minutes,
            'auto_absent_time' => $request->auto_absent_time,
            'school_name' => $request->school_name,
            'allow_early_clockin' => $request->has('allow_early_clockin')
        ]);

        $request->validate([
            'school_start_time' => 'required|date_format:H:i',
            'late_tolerance_minutes' => 'required|integer|min:0|max:120',
            'auto_absent_time' => 'required|date_format:H:i',
            'school_name' => 'required|string|max:255',
        ]);

        try {
            // Update attendance settings
            $result1 = Setting::set('school_start_time', $request->school_start_time, 'time', 'Waktu mulai sekolah');
            $result2 = Setting::set('late_tolerance_minutes', $request->late_tolerance_minutes, 'integer', 'Toleransi keterlambatan dalam menit');
            $result3 = Setting::set('auto_absent_time', $request->auto_absent_time, 'time', 'Waktu otomatis menandai siswa absent');
            
            // Update general settings
            $result4 = Setting::set('school_name', $request->school_name, 'string', 'Nama sekolah');
            $result5 = Setting::set('allow_early_clockin', $request->has('allow_early_clockin') ? 'true' : 'false', 'boolean', 'Izinkan clock in sebelum jam mulai sekolah');

            // Debug: Log results
            \Log::info('Settings Update Results', [
                'school_start_time_saved' => $result1 ? 'success' : 'failed',
                'late_tolerance_saved' => $result2 ? 'success' : 'failed',
                'auto_absent_saved' => $result3 ? 'success' : 'failed',
                'school_name_saved' => $result4 ? 'success' : 'failed',
                'allow_early_saved' => $result5 ? 'success' : 'failed'
            ]);

            // Verify settings were saved by checking database directly
            $savedSettings = [
                'school_start_time' => Setting::where('key', 'school_start_time')->first()->value ?? 'not found',
                'late_tolerance_minutes' => Setting::where('key', 'late_tolerance_minutes')->first()->value ?? 'not found',
                'auto_absent_time' => Setting::where('key', 'auto_absent_time')->first()->value ?? 'not found'
            ];
            \Log::info('Settings After Save (Direct DB Check)', $savedSettings);

            return redirect()->route('admin.settings.index')
                ->with('success', 'Pengaturan berhasil disimpan.');

        } catch (\Exception $e) {
            \Log::error('Settings Update Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
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
            Setting::set('school_name', 'SMA IT Persis Palu', 'string', 'Nama sekolah');
            Setting::set('allow_early_clockin', 'true', 'boolean', 'Izinkan clock in sebelum jam mulai sekolah');

            return redirect()->route('admin.settings.index')
                ->with('success', 'Pengaturan berhasil direset ke default');

        } catch (\Exception $e) {
            return redirect()->route('admin.settings.index')
                ->with('error', 'Gagal mereset pengaturan: ' . $e->getMessage());
        }
    }

    public function test()
    {
        // Test method untuk debug
        $currentValue = Setting::where('key', 'school_start_time')->first();
        
        // Try to update directly
        $result = Setting::updateOrCreate(
            ['key' => 'school_start_time'],
            ['value' => '08:30', 'type' => 'time', 'description' => 'Test update']
        );
        
        $newValue = Setting::where('key', 'school_start_time')->first();
        
        return response()->json([
            'before' => $currentValue ? $currentValue->value : 'not found',
            'after' => $newValue ? $newValue->value : 'not found',
            'result_id' => $result->id,
            'was_recently_created' => $result->wasRecentlyCreated
        ]);
    }
}