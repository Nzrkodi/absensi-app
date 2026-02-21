<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherAttendance;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TeacherReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $teacherId = $request->get('teacher_id');
        
        // Build query
        $query = TeacherAttendance::with(['teacher'])
            ->whereBetween('date', [$startDate, $endDate]);
        
        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }
        
        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('updated_at', 'desc')
            ->paginate(20);
        
        // Get summary statistics
        $summaryQuery = TeacherAttendance::whereBetween('date', [$startDate, $endDate]);
        
        if ($teacherId) {
            $summaryQuery->where('teacher_id', $teacherId);
        }
        
        $summary = [
            'total' => $summaryQuery->count(),
            'present' => $summaryQuery->whereNotNull('clock_in')->count(),
            'absent' => $summaryQuery->whereNull('clock_in')->count(),
            'permission' => $summaryQuery->where('status', 'izin')->count(),
            'sick' => $summaryQuery->where('status', 'sakit')->count(),
        ];
        
        // Get all teachers for filter
        $teachers = Teacher::where('status', 'active')
            ->orderBy('name')
            ->get();
        
        return view('admin.teacher-reports.index', compact(
            'attendances',
            'summary',
            'teachers',
            'startDate',
            'endDate',
            'teacherId'
        ));
    }
}
