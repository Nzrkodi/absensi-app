<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('admin.attendances.index', [
            'attendances' => [],
        ]);
    }

    public function create()
    {
        return view('admin.attendances.create');
    }

    public function store(Request $request)
    {
        // TODO: Implement store
        return redirect()->route('admin.attendances.index')->with('success', 'Absensi berhasil ditambahkan');
    }

    public function edit($id)
    {
        return view('admin.attendances.edit');
    }

    public function update(Request $request, $id)
    {
        // TODO: Implement update
        return redirect()->route('admin.attendances.index')->with('success', 'Absensi berhasil diupdate');
    }

    public function destroy($id)
    {
        // TODO: Implement destroy
        return redirect()->route('admin.attendances.index')->with('success', 'Absensi berhasil dihapus');
    }
}
