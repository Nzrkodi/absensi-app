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

        $students = $query->with(['user', 'class'])->paginate(10);
        $classes = Classes::select('id', 'name')->get();

        return view('admin.students.index', compact('students', 'classes'));
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(Request $request)
    {
        // TODO: Implement store
        return redirect()->route('admin.students.index')->with('success', 'Siswa berhasil ditambahkan');
    }

    public function edit($id)
    {
        return view('admin.students.edit');
    }

    public function update(Request $request, $id)
    {
        // TODO: Implement update
        return redirect()->route('admin.students.index')->with('success', 'Siswa berhasil diupdate');
    }

    public function destroy($id)
    {
        // TODO: Implement destroy
        return redirect()->route('admin.students.index')->with('success', 'Siswa berhasil dihapus');
    }
}
