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
            'present' => $summaryQuery->where('status', 'hadir')->count(),
            'absent' => $summaryQuery->where('status', 'alpha')->count(),
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

    public function preview(Request $request)
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
        
        $attendances = $query->orderBy('date', 'desc')->get();
        
        // Get summary statistics
        $summaryQuery = TeacherAttendance::whereBetween('date', [$startDate, $endDate]);
        
        if ($teacherId) {
            $summaryQuery->where('teacher_id', $teacherId);
        }
        
        $summary = [
            'total' => $summaryQuery->count(),
            'present' => $summaryQuery->where('status', 'hadir')->count(),
            'absent' => $summaryQuery->where('status', 'alpha')->count(),
            'permission' => $summaryQuery->where('status', 'izin')->count(),
            'sick' => $summaryQuery->where('status', 'sakit')->count(),
        ];
        
        // Get filter info
        $filterInfo = [
            'teacher_name' => $teacherId ? Teacher::find($teacherId)->name : 'Semua Guru',
        ];
        
        return view('admin.teacher-reports.preview', compact(
            'attendances',
            'summary',
            'startDate',
            'endDate',
            'teacherId',
            'filterInfo'
        ));
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $teacherId = $request->get('teacher_id');
        
        // Build query
        $query = TeacherAttendance::with(['teacher'])
            ->whereBetween('date', [$startDate, $endDate]);
        
        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }
        
        $attendances = $query->orderBy('date', 'desc')->get();
        
        switch ($format) {
            case 'pdf':
                return $this->exportPdf($attendances, $startDate, $endDate, $teacherId);
            case 'excel':
                return $this->exportExcel($attendances, $startDate, $endDate, $teacherId);
            case 'word':
                return $this->exportWord($attendances, $startDate, $endDate, $teacherId);
            default:
                return $this->exportCsv($attendances, $startDate, $endDate);
        }
    }
    
    private function exportCsv($attendances, $startDate, $endDate)
    {
        $filename = 'laporan_absensi_guru_' . $startDate . '_to_' . $endDate . '.csv';
        
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
                'Nama Guru',
                'Email',
                'Jabatan',
                'Clock In',
                'Clock Out',
                'Durasi Kerja',
                'Status',
                'Keterangan'
            ]);
            
            // CSV data
            foreach ($attendances as $attendance) {
                fputcsv($file, [
                    Carbon::parse($attendance->date)->format('d/m/Y'),
                    $attendance->teacher->name,
                    $attendance->teacher->email,
                    $attendance->teacher->jabatan ?? '-',
                    $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '-',
                    $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '-',
                    $attendance->getWorkDurationFormatted(),
                    $this->getStatusLabel($attendance->status),
                    $attendance->notes ?? '-'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    private function exportPdf($attendances, $startDate, $endDate, $teacherId)
    {
        $filterInfo = [
            'teacher_name' => $teacherId ? Teacher::find($teacherId)->name : 'Semua Guru',
        ];
        
        return view('admin.teacher-reports.pdf', compact(
            'attendances',
            'startDate',
            'endDate',
            'filterInfo'
        ));
    }
    
    private function exportExcel($attendances, $startDate, $endDate, $teacherId)
    {
        $filename = 'laporan_absensi_guru_' . $startDate . '_to_' . $endDate . '.xls';
        
        $filterInfo = [
            'teacher_name' => $teacherId ? Teacher::find($teacherId)->name : 'Semua Guru',
        ];
        
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $content = view('admin.teacher-reports.excel', compact(
            'attendances',
            'startDate',
            'endDate',
            'filterInfo'
        ))->render();
        
        return response($content, 200, $headers);
    }
    
    private function exportWord($attendances, $startDate, $endDate, $teacherId)
    {
        $filename = 'laporan_absensi_guru_' . $startDate . '_to_' . $endDate . '.doc';
        
        $filterInfo = [
            'teacher_name' => $teacherId ? Teacher::find($teacherId)->name : 'Semua Guru',
        ];
        
        $headers = [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $content = view('admin.teacher-reports.word', compact(
            'attendances',
            'startDate',
            'endDate',
            'filterInfo'
        ))->render();
        
        return response($content, 200, $headers);
    }
    
    private function getStatusLabel($status)
    {
        $labels = [
            'hadir' => 'Hadir',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alpha' => 'Tidak Hadir'
        ];
        
        return $labels[$status] ?? 'Tidak Hadir';
    }
}
