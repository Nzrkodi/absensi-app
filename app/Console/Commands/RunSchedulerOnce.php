<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;

class RunSchedulerOnce extends Command
{
    protected $signature = 'schedule:run-once';
    protected $description = 'Run the scheduler once (for testing purposes)';

    public function handle()
    {
        $this->info('Running scheduler once...');
        
        // Get the schedule
        $schedule = app(Schedule::class);
        
        // Run all due events
        $events = $schedule->dueEvents(app());
        
        if (count($events) === 0) {
            $this->info('No scheduled events are due to run.');
            return 0;
        }
        
        foreach ($events as $event) {
            $this->info("Running: {$event->getExpression()} {$event->command}");
            $event->run(app());
        }
        
        $this->info('Scheduler run completed!');
        return 0;
    }
}