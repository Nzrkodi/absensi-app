<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherAttendance;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeacherAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::today('Asia/Makassar')->format('Y-m-d'));
        
        // Check if selected date is holiday
        $isHoliday = \App\Models\Holiday::isHoliday($date);
        $holiday = $isHoliday ? \App\Models\Holiday::getHoliday($date) : null;
        
        $query = Teacher::query()
            ->where('status', 'active');

        // Add attendance data for the selected date
        $query->with(['attendances' => function($q) use ($date) {
            $q->where('date', $date);
        }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nip', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $teachers = $query->orderBy('name', 'asc')->paginate(15);
        
        // Get attendance settings for early clock in check
        $settings = \App\Models\Setting::getAttendanceSettings();
        $allowEarlyClockIn = \App\Models\Setting::get('allow_early_clockin', true);
        
        // Check if current time is before school start time
        $now = Carbon::now('Asia/Makassar');
        $schoolStartTime = Carbon::createFromFormat('H:i', $settings['school_start_time'], 'Asia/Makassar');
        $isBeforeSchoolStart = $now->format('H:i') < $schoolStartTime->format('H:i');

        return view('admin.teacher-attendance.index', compact(
            'teachers', 
            'date', 
            'isHoliday', 
            'holiday',
            'settings',
            'allowEarlyClockIn',
            'isBeforeSchoolStart'
        ));
    }

    public function clockIn(Request $request, Teacher $teacher)
    {
        $date = Carbon::today('Asia/Makassar');
        $now = Carbon::now('Asia/Makassar');
        
        Log::info('Teacher Clock In Request', [
            'teacher_id' => $teacher->id,
            'request_data' => $request->all()
        ]);
        
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
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid: ' . implode(', ', collect($e->errors())->flatten()->toArray())
            ]);
        }
        
        // Check if attendance already exists for today
        $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
            ->where('date', $date)
            ->first();

        if ($attendance && $attendance->clock_in) {
            return response()->json([
                'success' => false,
                'message' => 'Guru sudah melakukan clock in hari ini'
            ]);
        }

        // Get settings
        $settings = \App\Models\Setting::getAttendanceSettings();
        $allowEarlyClockIn = \App\Models\Setting::get('allow_early_clockin', true);
        
        // Determine clock in time
        $clockInTime = $now;
        if ($request->clock_mode === 'manual' && $request->manual_time) {
            $clockInTime = Carbon::createFromFormat('H:i', $request->manual_time, 'Asia/Makassar')
                ->setDate($date->year, $date->month, $date->day);
            
            if ($clockInTime->gt($now)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu clock in tidak boleh di masa depan'
                ]);
            }
            
            if ($clockInTime->format('Y-m-d') !== $date->format('Y-m-d')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu clock in harus pada hari yang sama'
                ]);
            }
        }
        
        // Check early clock in
        $schoolStartTime = Carbon::createFromFormat('H:i', $settings['school_start_time'], 'Asia/Makassar')->setDate($date->year, $date->month, $date->day);
        
        if (!$allowEarlyClockIn && $request->clock_mode === 'current' && $now->lt($schoolStartTime)) {
            return response()->json([
                'success' => false,
                'message' => "Clock in belum diizinkan. Silakan tunggu hingga jam {$settings['school_start_time']}",
                'early_clockin_disabled' => true
            ]);
        }
        
        $lateThreshold = $schoolStartTime->copy()->addMinutes($settings['late_tolerance_minutes']);
        $status = $clockInTime->gt($lateThreshold) ? 'late' : 'present';
        
        // Prepare notes
        $notes = $request->notes;
        if ($request->clock_mode === 'manual') {
            $manualNote = "Clock in manual pada jam {$clockInTime->format('H:i')}";
            $notes = $notes ? $manualNote . ' - ' . $notes : $manualNote;
        }

        if (!$attendance) {
            $attendance = TeacherAttendance::create([
                'teacher_id' => $teacher->id,
                'date' => $date,
                'clock_in' => $clockInTime->format('H:i:s'),
                'status' => $status,
                'notes' => $notes
            ]);
        } else {
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

    public function clockOut(Request $request, Teacher $teacher)
    {
        $date = Carbon::today('Asia/Makassar');
        $now = Carbon::now('Asia/Makassar');
        
        try {
            $request->validate([
                'clock_mode' => 'required|in:current,manual',
                'manual_time' => 'required_if:clock_mode,manual|nullable|date_format:H:i',
                'notes' => 'nullable|string|max:500'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid: ' . implode(', ', collect($e->errors())->flatten()->toArray())
            ]);
        }
        
        $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
            ->where('date', $date)
            ->first();

        if (!$attendance || !$attendance->clock_in) {
            return response()->json([
                'success' => false,
                'message' => 'Guru belum melakukan clock in'
            ]);
        }

        if ($attendance->clock_out) {
            return response()->json([
                'success' => false,
                'message' => 'Guru sudah melakukan clock out hari ini'
            ]);
        }

        // Determine clock out time
        $clockOutTime = $now;
        if ($request->clock_mode === 'manual' && $request->manual_time) {
            $clockOutTime = Carbon::createFromFormat('H:i', $request->manual_time, 'Asia/Makassar')
                ->setDate($date->year, $date->month, $date->day);
            
            if ($clockOutTime->gt($now)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu clock out tidak boleh di masa depan'
                ]);
            }
            
            $clockInTime = Carbon::createFromFormat('H:i:s', $attendance->clock_in, 'Asia/Makassar')
                ->setDate($date->year, $date->month, $date->day);
            
            if ($clockOutTime->lte($clockInTime)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu clock out harus setelah waktu clock in (' . $clockInTime->format('H:i') . ')'
                ]);
            }
            
            if ($clockOutTime->format('Y-m-d') !== $date->format('Y-m-d')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu clock out harus pada hari yang sama'
                ]);
            }
        }
        
        // Prepare notes
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

        $attendance->update([
            'clock_out' => $clockOutTime->format('H:i:s'),
            'notes' => $finalNotes
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
    }

    public function updateNote(Request $request, Teacher $teacher)
    {
        $request->validate([
            'status' => 'required|in:sick,permission',
            'notes' => 'required|string|max:500'
        ]);

        $date = Carbon::today('Asia/Makassar');
        $now = Carbon::now('Asia/Makassar');
        
        $settings = \App\Models\Setting::getAttendanceSettings();
        $allowEarlyClockIn = \App\Models\Setting::get('allow_early_clockin', true);
        
        $schoolStartTime = Carbon::createFromFormat('H:i', $settings['school_start_time'], 'Asia/Makassar')->setDate($date->year, $date->month, $date->day);
        
        if (!$allowEarlyClockIn && $now->lt($schoolStartTime)) {
            return response()->json([
                'success' => false,
                'message' => "Input note belum diizinkan. Silakan tunggu hingga jam {$settings['school_start_time']}",
                'early_note_disabled' => true
            ]);
        }
        
        $attendance = TeacherAttendance::updateOrCreate(
            [
                'teacher_id' => $teacher->id,
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

    public function bulkPermission(Request $request, Teacher $teacher)
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

        if ($startDate->diffInDays($endDate) > 30) {
            return response()->json([
                'success' => false,
                'message' => 'Periode izin tidak boleh lebih dari 30 hari'
            ]);
        }

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

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            
            if (\App\Models\Holiday::isHoliday($currentDate)) {
                $skippedDates[] = $dateStr . ' (hari libur)';
                $currentDate->addDay();
                continue;
            }

            $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
                ->where('date', $dateStr)
                ->first();

            if (!$attendance) {
                TeacherAttendance::create([
                    'teacher_id' => $teacher->id,
                    'date' => $dateStr,
                    'status' => $permissionType,
                    'notes' => $notes
                ]);
                $createdDates[] = $dateStr;
            } else {
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

    public function getAttendanceDetail(TeacherAttendance $attendance)
    {
        $attendance->load('teacher');
        
        return response()->json([
            'success' => true,
            'attendance' => $attendance
        ]);
    }
}
