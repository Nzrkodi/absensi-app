<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateAbsentTeachers implements ShouldQueue
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
            \Illuminate\Support\Facades\Log::info("Skipping teacher absent update - Today is a holiday: {$holiday->name}");
            return;
        }
        
        // Get all active teachers
        $activeTeachers = \App\Models\Teacher::where('status', 'active')->get();
        
        $absentCount = 0;
        
        foreach ($activeTeachers as $teacher) {
            // Check if teacher has attendance record for today
            $attendance = \App\Models\TeacherAttendance::where('teacher_id', $teacher->id)
                ->where('date', $today)
                ->first();
            
            // If no attendance record exists, create one with absent status
            if (!$attendance) {
                \App\Models\TeacherAttendance::create([
                    'teacher_id' => $teacher->id,
                    'date' => $today,
                    'status' => 'alpha',
                    'notes' => 'Otomatis ditandai tidak hadir karena tidak melakukan clock in'
                ]);
                $absentCount++;
            } 
            // If attendance exists but no clock_in and status is not izin/sakit, mark as absent
            elseif (!$attendance->clock_in && !in_array($attendance->status, ['sakit', 'izin'])) {
                $attendance->update([
                    'status' => 'alpha',
                    'notes' => $attendance->notes ?: 'Otomatis ditandai tidak hadir karena tidak melakukan clock in'
                ]);
                $absentCount++;
            }
        }
        
        \Illuminate\Support\Facades\Log::info("Auto absent update completed for teachers on {$today}: {$absentCount} teachers marked as absent");
    }
}
