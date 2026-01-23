<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
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
        // Check if student is logged in
        if (!Session::has('student_id')) {
            return redirect()->route('student.login')
                ->with('error', 'Silakan login terlebih dahulu');
        }
        
        $student = Student::find(Session::get('student_id'));
        
        if (!$student || $student->status !== 'active') {
            Session::forget(['student_id', 'student_name', 'student_nisn', 'student_class']);
            return redirect()->route('student.login')
                ->with('error', 'Data siswa tidak valid atau tidak aktif');
        }
        
        $date = Carbon::today('Asia/Makassar');
        $attendance = Attendance::where('student_id', $student->id)
            ->where('date', $date)
            ->first();
            
        // Get settings
        $settings = \App\Models\Setting::getAttendanceSettings();
        
        // Check if today is holiday
        $isHoliday = \App\Models\Holiday::isTodayHoliday();
        
        return view('student.attendance.index', compact(
            'student', 
            'attendance', 
            'date', 
            'settings',
            'isHoliday'
        ));
    }
    
    public function clockIn(Request $request)
    {
        // Check if student is logged in
        if (!Session::has('student_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi telah berakhir. Silakan login kembali.',
                'redirect' => route('student.login')
            ]);
        }
        
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'face_validation' => 'sometimes|boolean' // Face validation flag from client
        ]);
        
        // TODO: Add server-side face detection validation here
        // For now, we trust client-side validation but log it for audit
        if ($request->has('face_validation') && $request->face_validation) {
            Log::info('Clock in with face validation', [
                'student_id' => Session::get('student_id'),
                'face_validated' => true,
                'timestamp' => now()
            ]);
        }
        
        $student = Student::find(Session::get('student_id'));
        
        if (!$student || $student->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak valid atau tidak aktif'
            ]);
        }
        
        $date = Carbon::today('Asia/Makassar');
        $now = Carbon::now('Asia/Makassar');
        
        // Check if already clocked in
        $attendance = Attendance::where('student_id', $student->id)
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
        
        // Store photo using semester-based service
        $photoPath = AttendancePhotoService::storePhoto(
            $request->file('photo'),
            'clock_in',
            $student->id,
            $date
        );
        
        // Determine status based on time
        $settings = \App\Models\Setting::getAttendanceSettings();
        $schoolStartTime = Carbon::createFromFormat('H:i', $settings['school_start_time'], 'Asia/Makassar')
            ->setDate($date->year, $date->month, $date->day);
        $lateThreshold = $schoolStartTime->copy()->addMinutes($settings['late_tolerance_minutes']);
        
        $status = $now->gt($lateThreshold) ? 'late' : 'present';
        
        // Create or update attendance
        $attendanceData = [
            'student_id' => $student->id,
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
            $attendance = Attendance::create($attendanceData);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Clock in berhasil! Status: ' . ($status === 'present' ? 'Hadir' : 'Terlambat'),
            'data' => [
                'clock_in' => $now->format('H:i'),
                'status' => $status,
                'location' => $locationResult['location']->name
            ]
        ]);
    }
    
    public function clockOut(Request $request)
    {
        // Check if student is logged in
        if (!Session::has('student_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi telah berakhir. Silakan login kembali.',
                'redirect' => route('student.login')
            ]);
        }
        
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'face_validation' => 'sometimes|boolean' // Face validation flag from client
        ]);
        
        // Log face validation for clock out
        if ($request->has('face_validation') && $request->face_validation) {
            Log::info('Clock out with face validation', [
                'student_id' => Session::get('student_id'),
                'face_validated' => true,
                'timestamp' => now()
            ]);
        }
        
        $student = Student::find(Session::get('student_id'));
        
        $date = Carbon::today('Asia/Makassar');
        $now = Carbon::now('Asia/Makassar');
        
        $attendance = Attendance::where('student_id', $student->id)
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
        
        // Validate location (for clock out, we're more lenient)
        $locationResult = SchoolLocation::isWithinSchoolArea(
            $request->latitude, 
            $request->longitude
        );
        
        // For clock out, we don't strictly require being within school area
        // but we still log the location
        
        // Store photo using semester-based service
        $photoPath = AttendancePhotoService::storePhoto(
            $request->file('photo'),
            'clock_out',
            $student->id,
            $date
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