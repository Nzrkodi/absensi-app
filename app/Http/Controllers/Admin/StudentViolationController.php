<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentViolation;
use App\Models\Student;
use App\Models\ViolationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StudentViolationController extends Controller
{
    /**
     * Display a listing of student violations
     */
    public function index(Request $request)
    {
        $query = StudentViolation::with(['student', 'violationType'])
            ->orderBy('violation_date', 'desc')
            ->orderBy('violation_time', 'desc');

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('violation_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('violation_date', '<=', $request->end_date);
        }

        // Filter by student
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by violation type
        if ($request->filled('violation_type_id')) {
            $query->where('violation_type_id', $request->violation_type_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $violations = $query->paginate(15);

        // Data for filters
        $students = Student::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'nisn']);
        
        $violationTypes = ViolationType::active()
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'category']);

        return view('admin.student-violations.index', compact(
            'violations', 
            'students', 
            'violationTypes'
        ));
    }

    /**
     * Show the form for creating a new violation
     */
    public function create()
    {
        $students = Student::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'nisn', 'kelas']);
        
        $violationTypes = ViolationType::active()
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        // Get teachers/staff for "reported by" dropdown
        $teachers = \App\Models\User::where('role', '!=', 'student')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.student-violations.create', compact('students', 'violationTypes', 'teachers'));
    }

    /**
     * Store a newly created violation
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'violation_type_id' => 'required|exists:violation_types,id',
            'violation_date' => 'required|date|before_or_equal:today',
            'violation_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'reported_by' => 'nullable|string|max:255',
            'status' => 'required|in:pending,confirmed,resolved'
        ]);

        try {
            StudentViolation::create($request->all());

            $student = Student::find($request->student_id);
            $violationType = ViolationType::find($request->violation_type_id);

            Log::info('Student violation created', [
                'student' => $student->name,
                'violation_type' => $violationType->name,
                'date' => $request->violation_date
            ]);

            return redirect()->route('admin.student-violations.index')
                ->with('success', 'Data pelanggaran siswa berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Error creating student violation: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal menambahkan data pelanggaran siswa!');
        }
    }

    /**
     * Display the specified violation
     */
    public function show(StudentViolation $studentViolation)
    {
        $studentViolation->load(['student', 'violationType']);
        return view('admin.student-violations.show', compact('studentViolation'));
    }

    /**
     * Show the form for editing violation
     */
    public function edit(StudentViolation $studentViolation)
    {
        $students = Student::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'nisn']);
        
        $violationTypes = ViolationType::active()
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('admin.student-violations.edit', compact(
            'studentViolation', 
            'students', 
            'violationTypes'
        ));
    }

    /**
     * Update the specified violation
     */
    public function update(Request $request, StudentViolation $studentViolation)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'violation_type_id' => 'required|exists:violation_types,id',
            'violation_date' => 'required|date|before_or_equal:today',
            'violation_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'reported_by' => 'nullable|string|max:255',
            'status' => 'required|in:pending,confirmed,resolved',
            'resolution_notes' => 'nullable|string'
        ]);

        try {
            $studentViolation->update($request->all());

            Log::info('Student violation updated', [
                'id' => $studentViolation->id,
                'student' => $studentViolation->student->name
            ]);

            return redirect()->route('admin.student-violations.index')
                ->with('success', 'Data pelanggaran siswa berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Error updating student violation: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Gagal memperbarui data pelanggaran siswa!');
        }
    }

    /**
     * Remove the specified violation
     */
    public function destroy(StudentViolation $studentViolation)
    {
        try {
            $studentViolation->delete();

            Log::info('Student violation deleted', [
                'id' => $studentViolation->id,
                'student' => $studentViolation->student->name
            ]);

            return redirect()->route('admin.student-violations.index')
                ->with('success', 'Data pelanggaran siswa berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting student violation: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus data pelanggaran siswa!');
        }
    }

    /**
     * Get student violations for specific student (AJAX)
     */
    public function getStudentViolations(Student $student)
    {
        $violations = $student->violations()
            ->with('violationType')
            ->orderBy('violation_date', 'desc')
            ->get();

        $totalPoints = $violations->sum(function($violation) {
            return $violation->violationType->points;
        });

        $violationsByCategory = [
            'ringan' => $violations->filter(function($violation) {
                return $violation->violationType->category === 'ringan';
            })->count(),
            'sedang' => $violations->filter(function($violation) {
                return $violation->violationType->category === 'sedang';
            })->count(),
            'berat' => $violations->filter(function($violation) {
                return $violation->violationType->category === 'berat';
            })->count()
        ];

        return response()->json([
            'violations' => $violations,
            'total_points' => $totalPoints,
            'violations_by_category' => $violationsByCategory,
            'recent_violations' => $violations->take(5)
        ]);
    }

    /**
     * Get student info for AJAX requests
     */
    public function getStudentInfo(Student $student)
    {
        $totalPoints = $student->violations()
            ->join('violation_types', 'student_violations.violation_type_id', '=', 'violation_types.id')
            ->sum('violation_types.points');

        $recentViolations = $student->violations()
            ->with('violationType')
            ->orderBy('violation_date', 'desc')
            ->take(3)
            ->get();

        return response()->json([
            'student' => $student,
            'total_points' => $totalPoints,
            'recent_violations' => $recentViolations,
            'violations_count' => $student->violations()->count()
        ]);
    }
}