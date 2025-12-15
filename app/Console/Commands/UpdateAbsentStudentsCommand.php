<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateAbsentStudentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:update-absent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update absent status for students who did not clock in/out';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to update absent students...');
        
        \App\Jobs\UpdateAbsentStudents::dispatch();
        
        $this->info('UpdateAbsentStudents job has been dispatched successfully!');
        
        return 0;
    }
}
