<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateAbsentStudents implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $today = \Carbon\Carbon::today('Asia/Makassar');
        
        // Check if today is a holiday
        if (\App\Models\Holiday::isTodayHoliday()) {
            $holiday = \App\Models\Holiday::getHoliday($today);
            \Illuminate\Support\Facades\Log::info("Skipping absent update - Today is a holiday: {$holiday->name}");
            return;
        }
        
        // Get auto absent time from settings
        $autoAbsentTime = \App\Models\Setting::get('auto_absent_time', '15:00');
        
        // Get all active students
        $activeStudents = \App\Models\Student::where('status', 'active')->get();
        
        $absentCount = 0;
        
        foreach ($activeStudents as $student) {
            // Check if student has attendance record for today
            $attendance = \App\Models\Attendance::where('student_id', $student->id)
                ->where('date', $today)
                ->first();
            
            // If no attendance record exists, create one with absent status
            if (!$attendance) {
                \App\Models\Attendance::create([
                    'student_id' => $student->id,
                    'date' => $today,
                    'status' => 'absent',
                    'notes' => 'Otomatis ditandai absent karena tidak melakukan clock in/out'
                ]);
                $absentCount++;
            } 
            // If attendance exists but no clock_in and status is not sick/permission, mark as absent
            elseif (!$attendance->clock_in && !in_array($attendance->status, ['sick', 'permission'])) {
                $attendance->update([
                    'status' => 'absent',
                    'notes' => $attendance->notes ?: 'Otomatis ditandai absent karena tidak melakukan clock in'
                ]);
                $absentCount++;
            }
            // If clock_in exists but no clock_out, mark as bolos
            elseif ($attendance->clock_in && !$attendance->clock_out && in_array($attendance->status, ['present', 'late'])) {
                $attendance->update([
                    'status' => 'bolos',
                    'notes' => 'Otomatis ditandai bolos - clock in tanpa clock out sampai waktu auto absent'
                ]);
                $absentCount++; // Count bolos as part of problematic attendance
                \Illuminate\Support\Facades\Log::info("Student {$student->nisn} marked as bolos (clock in without clock out) on {$today}");
            }
        }
        
        \Illuminate\Support\Facades\Log::info("UpdateAbsentStudents job completed. {$absentCount} students processed (absent/bolos) for {$today}");
    }
}
