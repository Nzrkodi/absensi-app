<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query()
            ->select('students.*')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('classes', 'students.class_id', '=', 'classes.id');

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

        $students = $query->orderBy('users.name', 'asc')->with(['user', 'class'])->paginate(10);
        $classes = Classes::select('id', 'name')->get();

        // Handle AJAX request for reports filter
        if ($request->ajax || $request->get('ajax')) {
            return response()->json([
                'students' => $students->items()
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
            'email' => 'required|email|unique:users,email',
            'student_code' => 'required|string|unique:students,student_code',
            'class_id' => 'required|exists:classes,id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            // Create user first
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt('password123'), // Default password
            ]);

            // Create student
            Student::create([
                'user_id' => $user->id,
                'student_code' => $request->student_code,
                'class_id' => $request->class_id,
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
            $student = Student::with(['user', 'class'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'email' => $student->user->email,
                    'student_code' => $student->student_code,
                    'class_id' => $student->class_id,
                    'phone' => $student->phone,
                    'address' => $student->address,
                    'status' => $student->status,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student tidak ditemukan'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Student::findOrFail($id)->user_id,
            'student_code' => 'required|string|unique:students,student_code,' . $id,
            'class_id' => 'required|exists:classes,id',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            $student = Student::findOrFail($id);
            
            // Update user data
            $student->user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            // Update student data
            $student->update([
                'student_code' => $request->student_code,
                'class_id' => $request->class_id,
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
            $student = Student::findOrFail($id);
            
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
