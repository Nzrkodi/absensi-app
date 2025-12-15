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
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        
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
        $date = Carbon::today();
        $now = Carbon::now();
        
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

        // Determine status based on time (assuming school starts at 07:00)
        $schoolStartTime = Carbon::today()->setTime(7, 0);
        $status = $now->gt($schoolStartTime->copy()->addMinutes(15)) ? 'late' : 'present';

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
        $date = Carbon::today();
        $now = Carbon::now();
        
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

        $date = Carbon::today();
        
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
        $date = $date ?: Carbon::today()->format('Y-m-d');
        
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