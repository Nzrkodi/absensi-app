<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Check every minute if it's time to run auto absent
        $schedule->call(function () {
            $autoAbsentTime = \App\Models\Setting::get('auto_absent_time', '15:00');
            $currentTime = now('Asia/Makassar')->format('H:i');
            
            // Parse times for comparison
            $autoAbsentCarbon = \Carbon\Carbon::createFromFormat('H:i', $autoAbsentTime, 'Asia/Makassar');
            $currentCarbon = \Carbon\Carbon::createFromFormat('H:i', $currentTime, 'Asia/Makassar');
            
            // Check if current time matches auto absent time (exact match)
            if ($currentTime === $autoAbsentTime) {
                Log::info("Auto absent time reached ({$autoAbsentTime}), executing jobs...");
                
                // Execute jobs directly instead of dispatching to queue
                try {
                    (new \App\Jobs\UpdateAbsentStudents())->handle();
                    Log::info("UpdateAbsentStudents job executed successfully at {$currentTime}");
                } catch (\Exception $e) {
                    Log::error("UpdateAbsentStudents job failed: " . $e->getMessage());
                }
                
                try {
                    (new \App\Jobs\UpdateAbsentTeachers())->handle();
                    Log::info("UpdateAbsentTeachers job executed successfully at {$currentTime}");
                } catch (\Exception $e) {
                    Log::error("UpdateAbsentTeachers job failed: " . $e->getMessage());
                }
            }
        })
        ->everyMinute()
        ->timezone('Asia/Makassar')
        ->name('auto-absent-checker')
        ->withoutOverlapping();
        
        // Auto-sync holidays monthly (first day of each month at 2 AM)
        $schedule->command('holidays:sync --all')
                 ->monthlyOn(1, '02:00')
                 ->timezone('Asia/Makassar');
        
        // Cleanup old attendance photos every month (first day at 3 AM)
        $schedule->command('attendance:cleanup-photos --months=6')
                 ->monthlyOn(1, '03:00')
                 ->timezone('Asia/Makassar');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
