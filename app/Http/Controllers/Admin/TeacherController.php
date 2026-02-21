<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Teacher::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $teachers = $query->orderBy('name', 'asc')->paginate(50);

        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('admin.teachers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'email' => 'required|email|unique:teachers,email',
            'jenis_kelamin' => 'required|in:L,P',
            'nomor_hp' => 'nullable|string|max:20',
            'jabatan' => 'nullable|string|max:255',
            'mata_pelajaran' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            Teacher::create([
                'name' => $request->name,
                'address' => $request->address,
                'email' => $request->email,
                'jenis_kelamin' => $request->jenis_kelamin,
                'nomor_hp' => $request->nomor_hp,
                'jabatan' => $request->jabatan,
                'mata_pelajaran' => $request->mata_pelajaran,
                'status' => $request->status ?? 'active',
            ]);

            return redirect()->route('admin.teachers.index')
                ->with('success', 'Guru berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->route('admin.teachers.index')
                ->with('error', 'Gagal menambahkan guru. ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $teacher = Teacher::find($id);

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guru tidak ditemukan atau sudah dihapus'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $teacher
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
            'address' => 'nullable|string',
            'email' => 'required|email|unique:teachers,email,' . $id,
            'jenis_kelamin' => 'required|in:L,P',
            'nomor_hp' => 'nullable|string|max:20',
            'jabatan' => 'nullable|string|max:255',
            'mata_pelajaran' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            $teacher = Teacher::find($id);

            if (!$teacher) {
                return redirect()->route('admin.teachers.index')
                    ->with('error', 'Guru tidak ditemukan atau sudah dihapus');
            }

            $teacher->update([
                'name' => $request->name,
                'address' => $request->address,
                'email' => $request->email,
                'jenis_kelamin' => $request->jenis_kelamin,
                'nomor_hp' => $request->nomor_hp,
                'jabatan' => $request->jabatan,
                'mata_pelajaran' => $request->mata_pelajaran,
                'status' => $request->status,
            ]);

            return redirect()->route('admin.teachers.index')
                ->with('success', 'Guru berhasil diupdate');
        } catch (\Exception $e) {
            return redirect()->route('admin.teachers.index')
                ->with('error', 'Gagal mengupdate guru. ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $teacher = Teacher::find($id);

            if (!$teacher) {
                return redirect()->route('admin.teachers.index')
                    ->with('error', 'Guru tidak ditemukan atau sudah dihapus sebelumnya');
            }

            // Delete related attendances first (if any)
            $teacher->attendances()->delete();

            // Delete the teacher record
            $teacher->delete();

            return redirect()->route('admin.teachers.index')
                ->with('success', 'Guru berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.teachers.index')
                ->with('error', 'Gagal menghapus guru. ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'teacher_ids' => 'required|array|min:1',
            'teacher_ids.*' => 'exists:teachers,id'
        ]);

        try {
            $teacherIds = $request->teacher_ids;
            $deletedCount = 0;

            DB::beginTransaction();

            // Delete related attendances first
            if (Schema::hasTable('teacher_attendances')) {
                DB::table('teacher_attendances')->whereIn('teacher_id', $teacherIds)->delete();
            }

            // Delete teachers
            $deletedCount = Teacher::whereIn('id', $teacherIds)->delete();

            DB::commit();

            return redirect()->route('admin.teachers.index')
                ->with('success', "Berhasil menghapus {$deletedCount} guru");

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('admin.teachers.index')
                ->with('error', 'Gagal menghapus guru: ' . $e->getMessage());
        }
    }

    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'teacher_ids' => 'required|array|min:1',
            'teacher_ids.*' => 'exists:teachers,id',
            'status' => 'required|in:active,inactive'
        ]);

        try {
            $teacherIds = $request->teacher_ids;
            $status = $request->status;

            $updatedCount = Teacher::whereIn('id', $teacherIds)
                ->update(['status' => $status]);

            $statusText = $status === 'active' ? 'aktif' : 'tidak aktif';

            return redirect()->route('admin.teachers.index')
                ->with('success', "Berhasil mengubah status {$updatedCount} guru menjadi {$statusText}");

        } catch (\Exception $e) {
            return redirect()->route('admin.teachers.index')
                ->with('error', 'Gagal mengubah status guru: ' . $e->getMessage());
        }
    }
}
