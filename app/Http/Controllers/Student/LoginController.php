<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    /**
     * Show student login form
     */
    public function showLoginForm()
    {
        // If already logged in as student, redirect to attendance
        if (Session::has('student_id')) {
            return redirect()->route('student.attendance.index');
        }
        
        return view('student.login');
    }
    
    /**
     * Handle student login
     */
    public function login(Request $request)
    {
        $request->validate([
            'nisn' => 'required|digits:10',
            'name' => 'required|string|max:255'
        ], [
            'nisn.required' => 'NISN wajib diisi',
            'nisn.digits' => 'NISN harus 10 digit angka',
            'name.required' => 'Nama wajib diisi',
            'name.max' => 'Nama terlalu panjang'
        ]);
        
        // Find student by NISN and name (case insensitive)
        $student = Student::where('nisn', $request->nisn)
            ->where('status', 'active')
            ->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
            ->first();
            
        if (!$student) {
            return back()->withInput()->with('error', 
                'NISN atau nama tidak ditemukan. Pastikan data yang Anda masukkan benar.');
        }
        
        // Store student session
        Session::put('student_id', $student->id);
        Session::put('student_name', $student->name);
        Session::put('student_nisn', $student->nisn);
        Session::put('student_class', $student->kelas);
        
        return redirect()->route('student.attendance.index')
            ->with('success', 'Selamat datang, ' . $student->name . '!');
    }
    
    /**
     * Handle student logout
     */
    public function logout()
    {
        Session::forget(['student_id', 'student_name', 'student_nisn', 'student_class']);
        
        return redirect()->route('student.login')
            ->with('success', 'Anda telah keluar dari sistem absensi.');
    }
    
    /**
     * Check if student is logged in
     */
    public static function isLoggedIn()
    {
        return Session::has('student_id');
    }
    
    /**
     * Get current logged in student
     */
    public static function getCurrentStudent()
    {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return Student::find(Session::get('student_id'));
    }
}