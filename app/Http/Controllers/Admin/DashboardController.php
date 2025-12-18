<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today('Asia/Makassar');
        $selectedDate = $request->get('date', $today->format('Y-m-d'));
        $date = Carbon::parse($selectedDate);
        
        // Check if selected date is holiday
        $isHoliday = Holiday::isHoliday($selectedDate);
        $holiday = $isHoliday ? Holiday::getHoliday($selectedDate) : null;
        
        // Get total active students
        $totalStudents = Student::where('status', 'active')->count();
        
        // Get attendance statistics for selected date
        $attendanceStats = Attendance::where('date', $selectedDate)
            ->selectRaw('
                status,
                COUNT(*) as count,
                COUNT(CASE WHEN clock_in IS NOT NULL THEN 1 END) as clocked_in,
                COUNT(CASE WHEN clock_out IS NOT NULL THEN 1 END) as clocked_out
            ')
            ->groupBy('status')
            ->get()
            ->keyBy('status');
        
        // Calculate statistics
        $presentToday = ($attendanceStats->get('present')->count ?? 0) + ($attendanceStats->get('late')->count ?? 0);
        $absentToday = $attendanceStats->get('absent')->count ?? 0;
        $sickToday = $attendanceStats->get('sick')->count ?? 0;
        $permissionToday = $attendanceStats->get('permission')->count ?? 0;
        $notRecordedToday = $totalStudents - $attendanceStats->sum('count');
        
        // Get recent attendances for selected date (ordered by clock_in time - earliest first)
        $recentAttendances = Attendance::where('date', $selectedDate)
            ->with(['student', 'student.class'])
            ->whereNotNull('clock_in')
            ->orderBy('clock_in', 'asc')
            ->limit(10)
            ->get();
        
        // Get weekly attendance summary (last 7 days from selected date)
        $weeklyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $checkDate = $date->copy()->subDays($i);
            $dayStats = Attendance::where('date', $checkDate->format('Y-m-d'))
                ->selectRaw('
                    COUNT(*) as total,
                    COUNT(CASE WHEN status IN ("present", "late") THEN 1 END) as present,
                    COUNT(CASE WHEN status = "absent" THEN 1 END) as absent,
                    COUNT(CASE WHEN status IN ("sick", "permission") THEN 1 END) as excused
                ')
                ->first();
            
            $weeklyStats[] = [
                'date' => $checkDate->format('Y-m-d'),
                'day' => $checkDate->format('D'),
                'day_name' => $checkDate->format('d M'),
                'total' => $dayStats->total ?? 0,
                'present' => $dayStats->present ?? 0,
                'absent' => $dayStats->absent ?? 0,
                'excused' => $dayStats->excused ?? 0,
                'is_holiday' => Holiday::isHoliday($checkDate),
                'is_today' => $checkDate->isToday(),
                'is_selected' => $checkDate->format('Y-m-d') === $selectedDate
            ];
        }
        
        // Get students who haven't been recorded today (if not holiday)
        $unrecordedStudents = [];
        if (!$isHoliday && $selectedDate === $today->format('Y-m-d')) {
            $recordedStudentIds = Attendance::where('date', $selectedDate)->pluck('student_id');
            $unrecordedStudents = Student::where('status', 'active')
                ->whereNotIn('id', $recordedStudentIds)
                ->with(['class'])
                ->limit(5)
                ->get();
        }
        
        return view('admin.dashboard', compact(
            'totalStudents',
            'presentToday',
            'absentToday',
            'sickToday',
            'permissionToday',
            'notRecordedToday',
            'recentAttendances',
            'weeklyStats',
            'selectedDate',
            'date',
            'isHoliday',
            'holiday',
            'unrecordedStudents'
        ));
    }
}
