<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $classId = $request->get('class_id');
        $studentId = $request->get('student_id');
        
        // Build query
        $query = Attendance::with(['student', 'student.class'])
            ->whereBetween('date', [$startDate, $endDate]);
        
        if ($classId) {
            $query->whereHas('student', function($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }
        
        if ($studentId) {
            $query->where('student_id', $studentId);
        }
        
        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('updated_at', 'desc')
            ->paginate(20);
        
        // Get summary statistics
        $summaryQuery = Attendance::whereBetween('date', [$startDate, $endDate]);
        
        if ($classId) {
            $summaryQuery->whereHas('student', function($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }
        
        if ($studentId) {
            $summaryQuery->where('student_id', $studentId);
        }
        
        $summary = $summaryQuery->selectRaw('
            COUNT(*) as total,
            COUNT(CASE WHEN status = "present" THEN 1 END) as present,
            COUNT(CASE WHEN status = "late" THEN 1 END) as late,
            COUNT(CASE WHEN status = "absent" THEN 1 END) as absent,
            COUNT(CASE WHEN status = "sick" THEN 1 END) as sick,
            COUNT(CASE WHEN status = "permission" THEN 1 END) as permission
        ')->first();
        
        // Get classes and students for filters
        $classes = Classes::select('id', 'name')->orderBy('name')->get();
        $students = Student::where('status', 'active');
        
        if ($classId) {
            $students->where('class_id', $classId);
        }
        
        $students = $students->get();
        
        return view('admin.reports.index', compact(
            'attendances',
            'summary',
            'classes',
            'students',
            'startDate',
            'endDate',
            'classId',
            'studentId'
        ));
    }
    
    public function student(Request $request, Student $student)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        // Get student attendances
        $attendances = Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->paginate(15);
        
        // Get summary for this student
        $summary = Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total,
                COUNT(CASE WHEN status = "present" THEN 1 END) as present,
                COUNT(CASE WHEN status = "late" THEN 1 END) as late,
                COUNT(CASE WHEN status = "absent" THEN 1 END) as absent,
                COUNT(CASE WHEN status = "sick" THEN 1 END) as sick,
                COUNT(CASE WHEN status = "permission" THEN 1 END) as permission,
                COUNT(CASE WHEN clock_in IS NOT NULL THEN 1 END) as total_clock_in,
                COUNT(CASE WHEN clock_out IS NOT NULL THEN 1 END) as total_clock_out
            ')->first();
        
        // Calculate attendance percentage
        $totalDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $attendanceRate = $summary->total > 0 ? round((($summary->present + $summary->late) / $summary->total) * 100, 1) : 0;
        
        return view('admin.reports.student', compact(
            'student',
            'attendances',
            'summary',
            'startDate',
            'endDate',
            'totalDays',
            'attendanceRate'
        ));
    }
    
    public function preview(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $classId = $request->get('class_id');
        $studentId = $request->get('student_id');
        
        // Build query
        $query = Attendance::with(['student', 'student.class'])
            ->whereBetween('date', [$startDate, $endDate]);
        
        if ($classId) {
            $query->whereHas('student', function($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }
        
        if ($studentId) {
            $query->where('student_id', $studentId);
        }
        
        $attendances = $query->orderBy('date', 'desc')->get();
        
        // Get summary statistics
        $summaryQuery = Attendance::whereBetween('date', [$startDate, $endDate]);
        
        if ($classId) {
            $summaryQuery->whereHas('student', function($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }
        
        if ($studentId) {
            $summaryQuery->where('student_id', $studentId);
        }
        
        $summary = $summaryQuery->selectRaw('
            COUNT(*) as total,
            COUNT(CASE WHEN status = "present" THEN 1 END) as present,
            COUNT(CASE WHEN status = "late" THEN 1 END) as late,
            COUNT(CASE WHEN status = "absent" THEN 1 END) as absent,
            COUNT(CASE WHEN status = "sick" THEN 1 END) as sick,
            COUNT(CASE WHEN status = "permission" THEN 1 END) as permission
        ')->first();
        
        // Get filter info
        $filterInfo = [
            'class_name' => $classId ? Classes::find($classId)->name : 'Semua Kelas',
            'student_name' => $studentId ? Student::find($studentId)->name : 'Semua Siswa',
        ];
        
        return view('admin.reports.preview', compact(
            'attendances',
            'summary',
            'startDate',
            'endDate',
            'classId',
            'studentId',
            'filterInfo'
        ));
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'csv'); // csv, pdf, excel, word
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $classId = $request->get('class_id');
        $studentId = $request->get('student_id');
        
        // Build query
        $query = Attendance::with(['student', 'student.class'])
            ->whereBetween('date', [$startDate, $endDate]);
        
        if ($classId) {
            $query->whereHas('student', function($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }
        
        if ($studentId) {
            $query->where('student_id', $studentId);
        }
        
        $attendances = $query->orderBy('date', 'desc')->get();
        
        switch ($format) {
            case 'pdf':
                return $this->exportPdf($attendances, $startDate, $endDate, $classId, $studentId);
            case 'excel':
                return $this->exportExcel($attendances, $startDate, $endDate, $classId, $studentId);
            case 'word':
                return $this->exportWord($attendances, $startDate, $endDate, $classId, $studentId);
            default:
                return $this->exportCsv($attendances, $startDate, $endDate, $classId, $studentId);
        }
    }
    
    private function exportCsv($attendances, $startDate, $endDate, $classId, $studentId)
    {
        $filename = 'laporan_absensi_' . $startDate . '_to_' . $endDate . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($attendances) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // CSV headers
            fputcsv($file, [
                'Tanggal',
                'NISN',
                'Nama Siswa',
                'Kelas',
                'Clock In',
                'Clock Out',
                'Status',
                'Keterangan'
            ]);
            
            // CSV data
            foreach ($attendances as $attendance) {
                fputcsv($file, [
                    $attendance->date->format('d/m/Y'),
                    $attendance->student->nisn ?? '-',
                    $attendance->student->name,
                    $attendance->student->class->name ?? '-',
                    $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '-',
                    $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '-',
                    ucfirst($attendance->status),
                    $attendance->notes ?? '-'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    private function exportPdf($attendances, $startDate, $endDate, $classId, $studentId)
    {
        // For now, return a simple HTML response that can be printed as PDF
        // You can integrate with libraries like DomPDF or wkhtmltopdf later
        
        $filterInfo = [
            'class_name' => $classId ? Classes::find($classId)->name : 'Semua Kelas',
            'student_name' => $studentId ? Student::find($studentId)->name : 'Semua Siswa',
        ];
        
        return view('admin.reports.pdf', compact(
            'attendances',
            'startDate',
            'endDate',
            'filterInfo'
        ));
    }
    
    private function exportExcel($attendances, $startDate, $endDate, $classId, $studentId)
    {
        // Simple Excel export using HTML table with Excel MIME type
        $filename = 'laporan_absensi_' . $startDate . '_to_' . $endDate . '.xls';
        
        $filterInfo = [
            'class_name' => $classId ? Classes::find($classId)->name : 'Semua Kelas',
            'student_name' => $studentId ? Student::find($studentId)->name : 'Semua Siswa',
        ];
        
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $content = view('admin.reports.excel', compact(
            'attendances',
            'startDate',
            'endDate',
            'filterInfo'
        ))->render();
        
        return response($content, 200, $headers);
    }
    
    private function exportWord($attendances, $startDate, $endDate, $classId, $studentId)
    {
        // Simple Word export using HTML with Word MIME type
        $filename = 'laporan_absensi_' . $startDate . '_to_' . $endDate . '.doc';
        
        $filterInfo = [
            'class_name' => $classId ? Classes::find($classId)->name : 'Semua Kelas',
            'student_name' => $studentId ? Student::find($studentId)->user->name : 'Semua Siswa',
        ];
        
        $headers = [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $content = view('admin.reports.word', compact(
            'attendances',
            'startDate',
            'endDate',
            'filterInfo'
        ))->render();
        
        return response($content, 200, $headers);
    }
}