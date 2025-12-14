<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::with(['user', 'class'])
            ->when($request->search, function ($query, $search) {
                $query->where('student_code', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->paginate(10);

        return view('admin.students.index', compact('students'));
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
