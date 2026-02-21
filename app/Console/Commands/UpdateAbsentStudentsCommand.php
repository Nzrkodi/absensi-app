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
    protected $description = 'Update absent status for students and teachers who did not clock in/out';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to update absent status...');
        $this->newLine();
        
        // Update students
        $this->info('📚 Processing students...');
        try {
            (new \App\Jobs\UpdateAbsentStudents())->handle();
            $this->info('✓ Students processed successfully');
        } catch (\Exception $e) {
            $this->error('✗ Error processing students: ' . $e->getMessage());
        }
        
        $this->newLine();
        
        // Update teachers
        $this->info('👨‍🏫 Processing teachers...');
        try {
            (new \App\Jobs\UpdateAbsentTeachers())->handle();
            $this->info('✓ Teachers processed successfully');
        } catch (\Exception $e) {
            $this->error('✗ Error processing teachers: ' . $e->getMessage());
        }
        
        $this->newLine();
        $this->info('✅ Done! Check storage/logs/laravel.log for details.');
        
        return 0;
    }
}
