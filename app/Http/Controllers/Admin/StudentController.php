<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query()
            ->leftJoin('classes', 'students.class_id', '=', 'classes.id');

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

        $students = $query->orderBy('students.name', 'asc')->with(['class'])->get();
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
            'address' => 'required|string',
            'status' => 'required|in:active,inactive',
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
                'address' => $request->address,
                'status' => $request->status,
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
            'address' => 'required|string',
            'status' => 'required|in:active,inactive',
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
}
