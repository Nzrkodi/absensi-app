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
            
            if ($currentTime === $autoAbsentTime) {
                Log::info("Auto absent time reached ({$autoAbsentTime}), dispatching job...");
                \App\Jobs\UpdateAbsentStudents::dispatch();
            }
        })
        ->everyMinute()
        ->timezone('Asia/Makassar')
        ->name('auto-absent-checker');
        
        // Auto-sync holidays monthly (first day of each month at 2 AM)
        $schedule->command('holidays:sync --all')
                 ->monthlyOn(1, '02:00')
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
