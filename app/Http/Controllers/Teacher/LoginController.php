<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    /**
     * Show teacher login form
     */
    public function showLoginForm()
    {
        // If already logged in as teacher, redirect to attendance
        if (Session::has('teacher_id')) {
            return redirect()->route('teacher.attendance.index');
        }
        
        return view('teacher.login');
    }
    
    /**
     * Handle teacher login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255'
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'name.required' => 'Nama wajib diisi',
            'name.max' => 'Nama terlalu panjang'
        ]);
        
        // Find teacher by email and name (case insensitive)
        $teacher = Teacher::where('email', $request->email)
            ->where('status', 'active')
            ->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
            ->first();
            
        if (!$teacher) {
            return back()->withInput()->with('error', 
                'Email atau nama tidak ditemukan. Pastikan data yang Anda masukkan benar.');
        }
        
        // Store teacher session
        Session::put('teacher_id', $teacher->id);
        Session::put('teacher_name', $teacher->name);
        Session::put('teacher_email', $teacher->email);
        Session::put('teacher_jabatan', $teacher->jabatan);
        
        return redirect()->route('teacher.attendance.index')
            ->with('success', 'Selamat datang, ' . $teacher->name . '!');
    }
    
    /**
     * Handle teacher logout
     */
    public function logout()
    {
        Session::forget(['teacher_id', 'teacher_name', 'teacher_email', 'teacher_jabatan']);
        
        return redirect()->route('teacher.login')
            ->with('success', 'Anda telah keluar dari sistem absensi.');
    }
    
    /**
     * Check if teacher is logged in
     */
    public static function isLoggedIn()
    {
        return Session::has('teacher_id');
    }
    
    /**
     * Get current logged in teacher
     */
    public static function getCurrentTeacher()
    {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return Teacher::find(Session::get('teacher_id'));
    }
}
