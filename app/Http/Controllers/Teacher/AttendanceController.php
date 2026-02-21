<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherAttendance;
use App\Models\Teacher;
use App\Models\SchoolLocation;
use App\Services\AttendancePhotoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function index()
    {
        // Check if teacher is logged in
        if (!Session::has('teacher_id')) {
            return redirect()->route('teacher.login')
                ->with('error', 'Silakan login terlebih dahulu');
        }
        
        $teacher = Teacher::find(Session::get('teacher_id'));
        
        if (!$teacher || $teacher->status !== 'active') {
            Session::forget(['teacher_id', 'teacher_name', 'teacher_email', 'teacher_jabatan']);
            return redirect()->route('teacher.login')
                ->with('error', 'Data guru tidak valid atau tidak aktif');
        }
        
        $date = Carbon::today('Asia/Makassar');
        $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
            ->where('date', $date)
            ->first();
            
        // Get settings
        $settings = \App\Models\Setting::getAttendanceSettings();
        
        // Check if today is holiday
        $isHoliday = \App\Models\Holiday::isTodayHoliday();
        
        return view('teacher.attendance.index', compact(
            'teacher', 
            'attendance', 
            'date', 
            'settings',
            'isHoliday'
        ));
    }
    
    public function clockIn(Request $request)
    {
        // Check if teacher is logged in
        if (!Session::has('teacher_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi telah berakhir. Silakan login kembali.',
                'redirect' => route('teacher.login')
            ]);
        }
        
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'face_validation' => 'required|boolean|accepted'
        ]);
        
        // Check face validation
        if (!$request->has('face_validation') || !$request->face_validation) {
            return response()->json([
                'success' => false,
                'message' => 'Absensi memerlukan verifikasi wajah. Pastikan wajah terdeteksi dengan benar sebelum mengambil foto.',
                'error_code' => 'FACE_VALIDATION_REQUIRED'
            ]);
        }
        
        Log::info('Teacher clock in with face validation', [
            'teacher_id' => Session::get('teacher_id'),
            'face_validated' => true,
            'timestamp' => now()
        ]);
        
        $teacher = Teacher::find(Session::get('teacher_id'));
        
        if (!$teacher || $teacher->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Data guru tidak valid atau tidak aktif'
            ]);
        }
        
        $date = Carbon::today('Asia/Makassar');
        $now = Carbon::now('Asia/Makassar');
        
        // Check if already clocked in
        $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
            ->where('date', $date)
            ->first();
            
        if ($attendance && $attendance->clock_in) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan clock in hari ini'
            ]);
        }
        
        // Validate location
        $locationResult = SchoolLocation::isWithinSchoolArea(
            $request->latitude, 
            $request->longitude
        );
        
        if (!$locationResult['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi Anda terlalu jauh dari sekolah. Jarak: ' . 
                           ($locationResult['nearest_location']->distance ?? 'Unknown') . 'm'
            ]);
        }
        
        // Store photo
        $photoPath = AttendancePhotoService::storePhoto(
            $request->file('photo'),
            'clock_in',
            $teacher->id,
            $date,
            'teacher'
        );
        
        // Determine status - for teachers, always present (no late status)
        $status = 'present';
        
        // Create or update attendance
        $attendanceData = [
            'teacher_id' => $teacher->id,
            'date' => $date,
            'clock_in' => $now->format('H:i:s'),
            'status' => $status,
            'clock_in_photo' => $photoPath,
            'clock_in_latitude' => $request->latitude,
            'clock_in_longitude' => $request->longitude,
            'location_verified' => true,
            'distance_from_school' => $locationResult['distance']
        ];
        
        if ($attendance) {
            $attendance->update($attendanceData);
        } else {
            $attendance = TeacherAttendance::create($attendanceData);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Clock in berhasil!',
            'data' => [
                'clock_in' => $now->format('H:i'),
                'status' => $status,
                'location' => $locationResult['location']->name
            ]
        ]);
    }
    
    public function clockOut(Request $request)
    {
        // Check if teacher is logged in
        if (!Session::has('teacher_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi telah berakhir. Silakan login kembali.',
                'redirect' => route('teacher.login')
            ]);
        }
        
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'face_validation' => 'required|boolean|accepted'
        ]);
        
        // Check face validation
        if (!$request->has('face_validation') || !$request->face_validation) {
            return response()->json([
                'success' => false,
                'message' => 'Absensi pulang memerlukan verifikasi wajah. Pastikan wajah terdeteksi dengan benar sebelum mengambil foto.',
                'error_code' => 'FACE_VALIDATION_REQUIRED'
            ]);
        }
        
        Log::info('Teacher clock out with face validation', [
            'teacher_id' => Session::get('teacher_id'),
            'face_validated' => true,
            'timestamp' => now()
        ]);
        
        $teacher = Teacher::find(Session::get('teacher_id'));
        
        $date = Carbon::today('Asia/Makassar');
        $now = Carbon::now('Asia/Makassar');
        
        $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
            ->where('date', $date)
            ->first();
            
        if (!$attendance || !$attendance->clock_in) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan clock in'
            ]);
        }
        
        if ($attendance->clock_out) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan clock out hari ini'
            ]);
        }
        
        // Validate location
        $locationResult = SchoolLocation::isWithinSchoolArea(
            $request->latitude, 
            $request->longitude
        );
        
        // Store photo
        $photoPath = AttendancePhotoService::storePhoto(
            $request->file('photo'),
            'clock_out',
            $teacher->id,
            $date,
            'teacher'
        );
        
        // Update attendance
        $attendance->update([
            'clock_out' => $now->format('H:i:s'),
            'clock_out_photo' => $photoPath,
            'clock_out_latitude' => $request->latitude,
            'clock_out_longitude' => $request->longitude
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Clock out berhasil!',
            'data' => [
                'clock_out' => $now->format('H:i')
            ]
        ]);
    }
}
