<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::today('Asia/Makassar')->format('Y-m-d'));
        
        $query = Student::query()
            ->select('students.*')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('classes', 'students.class_id', '=', 'classes.id')
            ->where('students.status', 'active')
            ->with(['user', 'class']);

        // Add attendance data for the selected date
        $query->with(['attendances' => function($q) use ($date) {
            $q->where('date', $date);
        }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('students.student_code', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_id')) {
            $query->where('students.class_id', $request->class_id);
        }

        $students = $query->orderBy('users.name', 'asc')->paginate(15);
        
        // Get classes for filter
        $classes = \App\Models\Classes::select('id', 'name')->get();

        return view('admin.attendance.index', compact('students', 'classes', 'date'));
    }

    public function clockIn(Request $request, Student $student)
    {
        $date = Carbon::today('Asia/Makassar');
        $now = Carbon::now('Asia/Makassar');
        
        // Check if attendance already exists for today
        $attendance = Attendance::where('student_id', $student->id)
            ->where('date', $date)
            ->first();

        if ($attendance && $attendance->clock_in) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa sudah melakukan clock in hari ini'
            ]);
        }

        // Get settings for school start time and late tolerance
        $settings = \App\Models\Setting::getAttendanceSettings();
        
        // Debug: Log current settings
        \Log::info('Clock In Settings', [
            'school_start_time' => $settings['school_start_time'],
            'late_tolerance_minutes' => $settings['late_tolerance_minutes'],
            'current_time' => $now->format('H:i:s'),
            'date' => $date->format('Y-m-d')
        ]);
        
        $schoolStartTime = Carbon::createFromFormat('H:i', $settings['school_start_time'], 'Asia/Makassar')->setDate($date->year, $date->month, $date->day);
        $lateThreshold = $schoolStartTime->copy()->addMinutes($settings['late_tolerance_minutes']);
        
        \Log::info('Time Comparison', [
            'school_start' => $schoolStartTime->format('H:i:s'),
            'late_threshold' => $lateThreshold->format('H:i:s'),
            'current_time' => $now->format('H:i:s'),
            'is_late' => $now->gt($lateThreshold)
        ]);
        
        $status = $now->gt($lateThreshold) ? 'late' : 'present';

        if (!$attendance) {
            $attendance = Attendance::create([
                'student_id' => $student->id,
                'date' => $date,
                'clock_in' => $now->format('H:i:s'),
                'status' => $status
            ]);
        } else {
            // Update clock_in and status, regardless of previous status
            $attendance->update([
                'clock_in' => $now->format('H:i:s'),
                'status' => $status,
                'notes' => $attendance->status === 'absent' ? 'Clock in setelah ditandai absent' : $attendance->notes
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Clock in berhasil',
            'data' => [
                'clock_in' => $now->format('H:i'),
                'status' => $status,
                'status_badge' => $attendance->status_badge
            ]
        ]);
    }

    public function clockOut(Request $request, Student $student)
    {
        $date = Carbon::today('Asia/Makassar');
        $now = Carbon::now('Asia/Makassar');
        
        $attendance = Attendance::where('student_id', $student->id)
            ->where('date', $date)
            ->first();

        if (!$attendance || !$attendance->clock_in) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa belum melakukan clock in'
            ]);
        }

        if ($attendance->clock_out) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa sudah melakukan clock out hari ini'
            ]);
        }

        $attendance->update([
            'clock_out' => $now->format('H:i:s')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Clock out berhasil',
            'data' => [
                'clock_out' => $now->format('H:i')
            ]
        ]);
    }

    public function updateNote(Request $request, Student $student)
    {
        $request->validate([
            'status' => 'required|in:sick,permission',
            'notes' => 'required|string|max:500'
        ]);

        $date = Carbon::today('Asia/Makassar');
        
        $attendance = Attendance::updateOrCreate(
            [
                'student_id' => $student->id,
                'date' => $date
            ],
            [
                'status' => $request->status,
                'notes' => $request->notes
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diupdate',
            'data' => [
                'status' => $attendance->status,
                'status_badge' => $attendance->status_badge,
                'notes' => $attendance->notes
            ]
        ]);
    }

    public function getAttendanceData(Student $student, $date = null)
    {
        $date = $date ?: Carbon::today('Asia/Makassar')->format('Y-m-d');
        
        $attendance = Attendance::where('student_id', $student->id)
            ->where('date', $date)
            ->first();

        return response()->json([
            'success' => true,
            'data' => $attendance ? [
                'id' => $attendance->id,
                'clock_in' => $attendance->clock_in_time,
                'clock_out' => $attendance->clock_out_time,
                'status' => $attendance->status,
                'notes' => $attendance->notes,
                'can_clock_in' => $attendance->canClockIn(),
                'can_clock_out' => $attendance->canClockOut()
            ] : null
        ]);
    }
}