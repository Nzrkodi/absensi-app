<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'totalStudents' => 0,
            'presentToday' => 0,
            'absentToday' => 0,
            'recentAttendances' => [],
        ]);
    }
}
