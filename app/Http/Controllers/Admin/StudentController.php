<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Student;
use App\Imports\StudentsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query()
            ->select('students.*')
            ->with(['class:id,name']); // Eager loading dengan select spesifik

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

        // Gunakan pagination untuk mengurangi beban
        $students = $query->orderBy('students.name', 'asc')
            ->paginate(50); // Batasi 50 data per halaman
        
        $classes = Classes::select('id', 'name')->get();

        // Handle AJAX request for reports filter
        if ($request->ajax || $request->get('ajax')) {
            return response()->json([
                'students' => $students
            ]);
        }

        return view('admin.students.index', compact('students', 'classes'));
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nisn' => 'required|string|unique:students,nisn',
            'class_id' => 'required|exists:classes,id',
            'birth_place' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            // Create student directly without user account
            Student::create([
                'name' => $request->name,
                'nisn' => $request->nisn,
                'class_id' => $request->class_id,
                'birth_place' => $request->birth_place,
                'birth_date' => $request->birth_date,
                'phone' => $request->phone,
                'address' => $request->address ?? "",
                'status' => 'active',
            ]);

            return redirect()->route('admin.students.index')
                ->with('success', 'Siswa berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->route('admin.students.index')
                ->with('error', 'Gagal menambahkan siswa. ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $student = Student::with(['class'])->find($id);

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa tidak ditemukan atau sudah dihapus'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nisn' => $student->nisn,
                    'class_id' => $student->class_id,
                    'birth_place' => $student->birth_place,
                    'birth_date' => $student->birth_date ? $student->birth_date->format('Y-m-d') : '',
                    'phone' => $student->phone,
                    'address' => $student->address,
                    'status' => $student->status,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nisn' => 'required|string|unique:students,nisn,' . $id,
            'class_id' => 'required|exists:classes,id',
            'birth_place' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            $student = Student::find($id);

            if (!$student) {
                return redirect()->route('admin.students.index')
                    ->with('error', 'Siswa tidak ditemukan atau sudah dihapus');
            }

            // Update student data directly
            $student->update([
                'name' => $request->name,
                'nisn' => $request->nisn,
                'class_id' => $request->class_id,
                'birth_place' => $request->birth_place,
                'birth_date' => $request->birth_date,
                'phone' => $request->phone,
                'address' => $request->address,
                'status' => $request->status,
            ]);

            return redirect()->route('admin.students.index')
                ->with('success', 'Siswa berhasil diupdate');
        } catch (\Exception $e) {
            return redirect()->route('admin.students.index')
                ->with('error', 'Gagal mengupdate siswa. ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $student = Student::find($id);

            if (!$student) {
                return redirect()->route('admin.students.index')
                    ->with('error', 'Siswa tidak ditemukan atau sudah dihapus sebelumnya');
            }

            // Delete related attendances first (if any)
            $student->attendances()->delete();

            // Delete the student record
            $student->delete();

            return redirect()->route('admin.students.index')
                ->with('success', 'Siswa berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.students.index')
                ->with('error', 'Gagal menghapus siswa. ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            // Set longer execution time for import
            set_time_limit(300); // 5 minutes
            ini_set('memory_limit', '256M');
            
            Excel::import(new StudentsImport, $request->file('file'));
            
            return redirect()->route('admin.students.index')
                ->with('success', 'Data siswa berhasil diimport!');
                
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }
            
            return redirect()->route('admin.students.index')
                ->with('error', 'Import gagal: ' . implode(' | ', array_slice($errorMessages, 0, 5))); // Limit error messages
                
        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage());
            return redirect()->route('admin.students.index')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function deleteAll(Request $request)
    {
        // Validasi konfirmasi
        $request->validate([
            'confirmation' => 'required|in:HAPUS SEMUA DATA'
        ], [
            'confirmation.required' => 'Konfirmasi wajib diisi',
            'confirmation.in' => 'Konfirmasi harus berupa teks "HAPUS SEMUA DATA"'
        ]);

        try {
            // Hitung jumlah data yang akan dihapus
            $totalStudents = Student::count();
            
            if ($totalStudents == 0) {
                return redirect()->route('admin.students.index')
                    ->with('info', 'Tidak ada data siswa untuk dihapus');
            }

            // Gunakan database transaction untuk keamanan
            DB::beginTransaction();
            
            // Hapus semua data absensi terlebih dahulu
            if (Schema::hasTable('attendances')) {
                DB::table('attendances')->delete();
            }
            
            // Hapus semua data siswa
            DB::table('students')->delete();
            
            DB::commit();
            
            return redirect()->route('admin.students.index')
                ->with('success', "Berhasil menghapus {$totalStudents} data siswa dan data terkait");
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Delete all students error: ' . $e->getMessage());
            return redirect()->route('admin.students.index')
                ->with('error', 'Gagal menghapus data siswa: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        try {
            // Get available classes for better template
            $classes = Classes::select('name')->get()->pluck('name')->toArray();
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="template_import_siswa.csv"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ];

            // Create sample data for template with real class names
            $sampleData = [
                ['nama', 'nisn', 'kelas', 'tempat_lahir', 'tanggal_lahir', 'no_handphone', 'alamat'],
                ['Ahmad Rizki', '1234567890', '10', 'Jakarta', '15/03/2005', '081234567890', 'Jl. Merdeka No. 123'],
                ['Siti Nurhaliza', '0987654321', '11', 'Bandung', '22/07/2004', '081987654321', 'Jl. Sudirman No. 456'],
                ['Budi Santoso', '1122334455', '12', 'Surabaya', '10/12/2003', '082112233445', 'Jl. Diponegoro No. 789']
            ];

            return response()->streamDownload(function() use ($sampleData) {
                $file = fopen('php://output', 'w');
                
                // Add BOM for UTF-8 CSV to handle Indonesian characters properly
                fwrite($file, "\xEF\xBB\xBF");
                
                foreach ($sampleData as $row) {
                    fputcsv($file, $row);
                }
                
                fclose($file);
            }, 'template_import_siswa.csv', $headers);
            
        } catch (\Exception $e) {
            return redirect()->route('admin.students.index')
                ->with('error', 'Gagal mendownload template: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id'
        ]);

        try {
            $studentIds = $request->student_ids;
            $deletedCount = 0;

            DB::beginTransaction();

            // Delete related attendances first
            if (Schema::hasTable('attendances')) {
                DB::table('attendances')->whereIn('student_id', $studentIds)->delete();
            }

            // Delete students
            $deletedCount = Student::whereIn('id', $studentIds)->delete();

            DB::commit();

            return redirect()->route('admin.students.index')
                ->with('success', "Berhasil menghapus {$deletedCount} siswa");

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('admin.students.index')
                ->with('error', 'Gagal menghapus siswa: ' . $e->getMessage());
        }
    }

    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'status' => 'required|in:active,inactive'
        ]);

        try {
            $studentIds = $request->student_ids;
            $status = $request->status;

            $updatedCount = Student::whereIn('id', $studentIds)
                ->update(['status' => $status]);

            $statusText = $status === 'active' ? 'aktif' : 'tidak aktif';

            return redirect()->route('admin.students.index')
                ->with('success', "Berhasil mengubah status {$updatedCount} siswa menjadi {$statusText}");

        } catch (\Exception $e) {
            return redirect()->route('admin.students.index')
                ->with('error', 'Gagal mengubah status siswa: ' . $e->getMessage());
        }
    }
}
