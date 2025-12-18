<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Update absent students based on setting time
        $autoAbsentTime = \App\Models\Setting::get('auto_absent_time', '15:00');
        $schedule->command('attendance:update-absent')
                 ->dailyAt($autoAbsentTime)
                 ->timezone('Asia/Makassar')
                 ->onSuccess(function () {
                     \Log::info('Auto absent job completed successfully at ' . now('Asia/Makassar'));
                 })
                 ->onFailure(function () {
                     \Log::error('Auto absent job failed at ' . now('Asia/Makassar'));
                 });
        
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
