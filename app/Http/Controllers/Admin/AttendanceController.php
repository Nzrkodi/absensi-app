<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::today('Asia/Makassar')->format('Y-m-d'));
        
        // Check if selected date is holiday
        $isHoliday = \App\Models\Holiday::isHoliday($date);
        $holiday = $isHoliday ? \App\Models\Holiday::getHoliday($date) : null;
        
        $query = Student::query()
            ->select('students.*')
            ->leftJoin('classes', 'students.class_id', '=', 'classes.id')
            ->where('students.status', 'active')
            ->with(['class']);

        // Add attendance data for the selected date
        $query->with(['attendances' => function($q) use ($date) {
            $q->where('date', $date);
        }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('students.nisn', 'like', "%{$search}%")
                    ->orWhere('students.name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_id')) {
            $query->where('students.class_id', $request->class_id);
        }

        $students = $query->orderBy('students.name', 'asc')->paginate(15);
        
        // Get classes for filter
        $classes = \App\Models\Classes::select('id', 'name')->get();
        
        // Get attendance settings for early clock in check
        $settings = \App\Models\Setting::getAttendanceSettings();
        $allowEarlyClockIn = \App\Models\Setting::get('allow_early_clockin', true);
        
        // Check if current time is before school start time
        $now = Carbon::now('Asia/Makassar');
        $schoolStartTime = Carbon::createFromFormat('H:i', $settings['school_start_time'], 'Asia/Makassar');
        $isBeforeSchoolStart = $now->format('H:i') < $schoolStartTime->format('H:i');

        return view('admin.attendance.index', compact(
            'students', 
            'classes', 
            'date', 
            'isHoliday', 
            'holiday',
            'settings',
            'allowEarlyClockIn',
            'isBeforeSchoolStart'
        ));
    }

    public function clockIn(Request $request, Student $student)
    {
        $date = Carbon::today('Asia/Makassar');
        $now = Carbon::now('Asia/Makassar');
        
        // Debug log
        Log::info('Clock In Request', [
            'student_id' => $student->id,
            'request_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);
        
        // Validate request for manual time
        try {
            $request->validate([
                'clock_mode' => 'required|in:current,manual',
                'manual_time' => 'required_if:clock_mode,manual|nullable|date_format:H:i',
                'notes' => 'nullable|string|max:500',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Error', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid: ' . implode(', ', collect($e->errors())->flatten()->toArray())
            ]);
        }
        
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
        $allowEarlyClockIn = \App\Models\Setting::get('allow_early_clockin', true);
        
        // Determine clock in time based on mode
        $clockInTime = $now;
        if ($request->clock_mode === 'manual' && $request->manual_time) {
            $clockInTime = Carbon::createFromFormat('H:i', $request->manual_time, 'Asia/Makassar')
                ->setDate($date->year, $date->month, $date->day);
            
            // Validate manual time is not in the future
            if ($clockInTime->gt($now)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu clock in tidak boleh di masa depan'
                ]);
            }
            
            // Validate manual time is not too far in the past (same day only)
            if ($clockInTime->format('Y-m-d') !== $date->format('Y-m-d')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu clock in harus pada hari yang sama'
                ]);
            }
        }
        
        // Check if early clock in is allowed (only for current time mode)
        $schoolStartTime = Carbon::createFromFormat('H:i', $settings['school_start_time'], 'Asia/Makassar')->setDate($date->year, $date->month, $date->day);
        
        if (!$allowEarlyClockIn && $request->clock_mode === 'current' && $now->lt($schoolStartTime)) {
            return response()->json([
                'success' => false,
                'message' => "Clock in belum diizinkan. Silakan tunggu hingga jam {$settings['school_start_time']}",
                'early_clockin_disabled' => true
            ]);
        }
        
        // Debug: Log current settings
        Log::info('Clock In Settings', [
            'school_start_time' => $settings['school_start_time'],
            'late_tolerance_minutes' => $settings['late_tolerance_minutes'],
            'clock_mode' => $request->clock_mode,
            'clock_in_time' => $clockInTime->format('H:i:s'),
            'current_time' => $now->format('H:i:s'),
            'date' => $date->format('Y-m-d')
        ]);
        
        $lateThreshold = $schoolStartTime->copy()->addMinutes($settings['late_tolerance_minutes']);
        
        Log::info('Time Comparison', [
            'school_start' => $schoolStartTime->format('H:i:s'),
            'late_threshold' => $lateThreshold->format('H:i:s'),
            'clock_in_time' => $clockInTime->format('H:i:s'),
            'is_late' => $clockInTime->gt($lateThreshold)
        ]);
        
        $status = $clockInTime->gt($lateThreshold) ? 'late' : 'present';
        
        // Prepare notes
        $notes = $request->notes;
        if ($request->clock_mode === 'manual') {
            $manualNote = "Clock in manual pada jam {$clockInTime->format('H:i')}";
            $notes = $notes ? $manualNote . ' - ' . $notes : $manualNote;
        }

        if (!$attendance) {
            $attendance = Attendance::create([
                'student_id' => $student->id,
                'date' => $date,
                'clock_in' => $clockInTime->format('H:i:s'),
                'status' => $status,
                'notes' => $notes
            ]);
        } else {
            // Update clock_in and status, regardless of previous status
            $existingNotes = $attendance->status === 'absent' ? 'Clock in setelah ditandai absent' : $attendance->notes;
            $finalNotes = $notes ? ($existingNotes ? $existingNotes . ' | ' . $notes : $notes) : $existingNotes;
            
            $attendance->update([
                'clock_in' => $clockInTime->format('H:i:s'),
                'status' => $status,
                'notes' => $finalNotes
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $request->clock_mode === 'manual' 
                ? "Clock in berhasil dengan waktu manual {$clockInTime->format('H:i')}" 
                : 'Clock in berhasil',
            'data' => [
                'clock_in' => $clockInTime->format('H:i'),
                'status' => $status,
                'status_badge' => $attendance->status_badge
            ]
        ]);
    }

    public function clockOut(Request $request, Student $student)
    {
        $date = Carbon::today('Asia/Makassar');
        $now = Carbon::now('Asia/Makassar');
        
        // Debug log
        Log::info('Clock Out Request', [
            'student_id' => $student->id,
            'request_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);
        
        // Validate request for manual time
        try {
            $request->validate([
                'clock_mode' => 'required|in:current,manual',
                'manual_time' => 'required_if:clock_mode,manual|nullable|date_format:H:i',
                'notes' => 'nullable|string|max:500'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Clock Out Validation Error', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid: ' . implode(', ', collect($e->errors())->flatten()->toArray())
            ]);
        }
        
        $attendance = Attendance::where('student_id', $student->id)
            ->where('date', $date)
            ->first();

        if (!$attendance || !$attendance->clock_in) {
            Log::warning('Clock Out Failed - No Clock In', [
                'student_id' => $student->id,
                'date' => $date,
                'attendance' => $attendance
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Siswa belum melakukan clock in'
            ]);
        }

        if ($attendance->clock_out) {
            Log::warning('Clock Out Failed - Already Clocked Out', [
                'student_id' => $student->id,
                'attendance_id' => $attendance->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Siswa sudah melakukan clock out hari ini'
            ]);
        }

        // Determine clock out time based on mode
        $clockOutTime = $now;
        if ($request->clock_mode === 'manual' && $request->manual_time) {
            $clockOutTime = Carbon::createFromFormat('H:i', $request->manual_time, 'Asia/Makassar')
                ->setDate($date->year, $date->month, $date->day);
            
            // Validate manual time is not in the future
            if ($clockOutTime->gt($now)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu clock out tidak boleh di masa depan'
                ]);
            }
            
            // Validate manual time is not before clock in time
            $clockInTime = Carbon::createFromFormat('H:i:s', $attendance->clock_in, 'Asia/Makassar')
                ->setDate($date->year, $date->month, $date->day);
            
            if ($clockOutTime->lte($clockInTime)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu clock out harus setelah waktu clock in (' . $clockInTime->format('H:i') . ')'
                ]);
            }
            
            // Validate manual time is not too far in the past (same day only)
            if ($clockOutTime->format('Y-m-d') !== $date->format('Y-m-d')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu clock out harus pada hari yang sama'
                ]);
            }
        }
        
        // Prepare notes for manual clock out
        $notes = $request->notes;
        if ($request->clock_mode === 'manual') {
            $manualNote = "Clock out manual pada jam {$clockOutTime->format('H:i')}";
            $existingNotes = $attendance->notes;
            
            if ($notes) {
                $finalNotes = $existingNotes ? $existingNotes . ' | ' . $manualNote . ' - ' . $notes : $manualNote . ' - ' . $notes;
            } else {
                $finalNotes = $existingNotes ? $existingNotes . ' | ' . $manualNote : $manualNote;
            }
        } else {
            $finalNotes = $notes ? ($attendance->notes ? $attendance->notes . ' | ' . $notes : $notes) : $attendance->notes;
        }

        try {
            $attendance->update([
                'clock_out' => $clockOutTime->format('H:i:s'),
                'notes' => $finalNotes
            ]);
            
            Log::info('Clock Out Success', [
                'student_id' => $student->id,
                'attendance_id' => $attendance->id,
                'clock_out_time' => $clockOutTime->format('H:i:s')
            ]);

            return response()->json([
                'success' => true,
                'message' => $request->clock_mode === 'manual' 
                    ? "Clock out berhasil dengan waktu manual {$clockOutTime->format('H:i')}" 
                    : 'Clock out berhasil',
                'data' => [
                    'clock_out' => $clockOutTime->format('H:i')
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Clock Out Database Error', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data clock out'
            ]);
        }
    }

    public function updateNote(Request $request, Student $student)
    {
        $request->validate([
            'status' => 'required|in:sick,permission',
            'notes' => 'required|string|max:500'
        ]);

        $date = Carbon::today('Asia/Makassar');
        $now = Carbon::now('Asia/Makassar');
        
        // Get settings for early clock in check
        $settings = \App\Models\Setting::getAttendanceSettings();
        $allowEarlyClockIn = \App\Models\Setting::get('allow_early_clockin', true);
        
        // Check if early note input is allowed
        $schoolStartTime = Carbon::createFromFormat('H:i', $settings['school_start_time'], 'Asia/Makassar')->setDate($date->year, $date->month, $date->day);
        
        if (!$allowEarlyClockIn && $now->lt($schoolStartTime)) {
            return response()->json([
                'success' => false,
                'message' => "Input note belum diizinkan. Silakan tunggu hingga jam {$settings['school_start_time']}",
                'early_note_disabled' => true
            ]);
        }
        
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

    public function bulkPermission(Request $request, Student $student)
    {
        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d|after_or_equal:start_date',
            'permission_type' => 'required|in:sick,permission',
            'notes' => 'required|string|max:500'
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $permissionType = $request->permission_type;
        $notes = $request->notes;

        // Check if date range is not too long (max 30 days)
        if ($startDate->diffInDays($endDate) > 30) {
            return response()->json([
                'success' => false,
                'message' => 'Periode izin tidak boleh lebih dari 30 hari'
            ]);
        }

        // Check if start date is not too far in the past (max 7 days ago)
        $today = Carbon::today('Asia/Makassar');
        if ($startDate->lt($today->copy()->subDays(7))) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal mulai tidak boleh lebih dari 7 hari yang lalu'
            ]);
        }

        $createdDates = [];
        $updatedDates = [];
        $skippedDates = [];

        // Loop through each date in the range
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            
            // Skip if it's a holiday
            if (\App\Models\Holiday::isHoliday($currentDate)) {
                $skippedDates[] = $dateStr . ' (hari libur)';
                $currentDate->addDay();
                continue;
            }

            // Check existing attendance
            $attendance = Attendance::where('student_id', $student->id)
                ->where('date', $dateStr)
                ->first();

            if (!$attendance) {
                // Create new attendance record
                Attendance::create([
                    'student_id' => $student->id,
                    'date' => $dateStr,
                    'status' => $permissionType,
                    'notes' => $notes
                ]);
                $createdDates[] = $dateStr;
            } else {
                // Update existing record only if it's not already clock in/out
                if (!$attendance->clock_in && !$attendance->clock_out) {
                    $attendance->update([
                        'status' => $permissionType,
                        'notes' => $notes
                    ]);
                    $updatedDates[] = $dateStr;
                } else {
                    $skippedDates[] = $dateStr . ' (sudah ada clock in/out)';
                }
            }

            $currentDate->addDay();
        }

        // Log the bulk permission action
        Log::info('Bulk Permission Applied', [
            'student_id' => $student->id,
            'student_name' => $student->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'permission_type' => $permissionType,
            'notes' => $notes,
            'created_dates' => $createdDates,
            'updated_dates' => $updatedDates,
            'skipped_dates' => $skippedDates,
            'admin_user' => auth()->user()->name ?? 'Unknown'
        ]);

        $totalProcessed = count($createdDates) + count($updatedDates);
        $message = "Berhasil memproses {$totalProcessed} hari";
        
        if (count($createdDates) > 0) {
            $message .= " (dibuat: " . count($createdDates) . ")";
        }
        
        if (count($updatedDates) > 0) {
            $message .= " (diupdate: " . count($updatedDates) . ")";
        }
        
        if (count($skippedDates) > 0) {
            $message .= " (dilewati: " . count($skippedDates) . ")";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'created_dates' => $createdDates,
                'updated_dates' => $updatedDates,
                'skipped_dates' => $skippedDates,
                'total_processed' => $totalProcessed
            ]
        ]);
    }

    /**
     * Get detailed attendance information including photos and location
     */
    public function getAttendanceDetail(Attendance $attendance)
    {
        $attendance->load('student.class');
        
        return response()->json([
            'success' => true,
            'attendance' => $attendance
        ]);
    }
}