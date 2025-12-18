<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CleanupStudentUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:cleanup-users {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove user accounts that were created for students';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Find users with student email pattern
        $studentUsers = User::where('email', 'like', '%@student.school.id')
            ->where('email', '!=', 'aditya.wahyu@smaitpersis.sch.id') // Protect default admin
            ->get();

        if ($studentUsers->isEmpty()) {
            $this->info('No student user accounts found to cleanup.');
            return 0;
        }

        $this->info("Found {$studentUsers->count()} student user accounts:");
        
        foreach ($studentUsers as $user) {
            $this->line("- {$user->name} ({$user->email})");
        }

        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to delete these user accounts?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $deleted = 0;
        foreach ($studentUsers as $user) {
            try {
                $user->delete();
                $deleted++;
                $this->line("✓ Deleted: {$user->name}");
            } catch (\Exception $e) {
                $this->error("✗ Failed to delete {$user->name}: {$e->getMessage()}");
            }
        }

        $this->info("Successfully deleted {$deleted} student user accounts.");
        return 0;
    }
}
