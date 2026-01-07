<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ViolationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ViolationTypeController extends Controller
{
    /**
     * Display a listing of violation types
     */
    public function index()
    {
        $violationTypes = ViolationType::orderBy('category')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.violation-types.index', compact('violationTypes'));
    }

    /**
     * Show the form for creating a new violation type
     */
    public function create()
    {
        return view('admin.violation-types.create');
    }

    /**
     * Store a newly created violation type
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:violation_types,name',
            'description' => 'nullable|string',
            'category' => 'required|in:ringan,sedang,berat',
            'points' => 'required|integer|min:1|max:100',
            'status' => 'required|in:active,inactive'
        ]);

        try {
            ViolationType::create($request->all());

            Log::info('Violation type created', [
                'name' => $request->name,
                'category' => $request->category,
                'points' => $request->points
            ]);

            return redirect()->route('admin.violation-types.index')
                ->with('success', 'Jenis pelanggaran berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Error creating violation type: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal menambahkan jenis pelanggaran!');
        }
    }

    /**
     * Show the form for editing violation type
     */
    public function edit(ViolationType $violationType)
    {
        return view('admin.violation-types.edit', compact('violationType'));
    }

    /**
     * Update the specified violation type
     */
    public function update(Request $request, ViolationType $violationType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:violation_types,name,' . $violationType->id,
            'description' => 'nullable|string',
            'category' => 'required|in:ringan,sedang,berat',
            'points' => 'required|integer|min:1|max:100',
            'status' => 'required|in:active,inactive'
        ]);

        try {
            $violationType->update($request->all());

            Log::info('Violation type updated', [
                'id' => $violationType->id,
                'name' => $request->name
            ]);

            return redirect()->route('admin.violation-types.index')
                ->with('success', 'Jenis pelanggaran berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Error updating violation type: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal memperbarui jenis pelanggaran!');
        }
    }

    /**
     * Remove the specified violation type
     */
    public function destroy(ViolationType $violationType)
    {
        try {
            // Check if violation type is being used
            if ($violationType->studentViolations()->count() > 0) {
                return back()->with('error', 'Tidak dapat menghapus jenis pelanggaran yang sudah digunakan!');
            }

            $violationType->delete();

            Log::info('Violation type deleted', [
                'id' => $violationType->id,
                'name' => $violationType->name
            ]);

            return redirect()->route('admin.violation-types.index')
                ->with('success', 'Jenis pelanggaran berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting violation type: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus jenis pelanggaran!');
        }
    }

    /**
     * Toggle status of violation type
     */
    public function toggleStatus(ViolationType $violationType)
    {
        try {
            $newStatus = $violationType->status === 'active' ? 'inactive' : 'active';
            $violationType->update(['status' => $newStatus]);

            Log::info('Violation type status toggled', [
                'id' => $violationType->id,
                'new_status' => $newStatus
            ]);

            return back()->with('success', 'Status jenis pelanggaran berhasil diubah!');
        } catch (\Exception $e) {
            Log::error('Error toggling violation type status: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengubah status jenis pelanggaran!');
        }
    }
}