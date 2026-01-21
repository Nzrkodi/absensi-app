<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AttendancePhotoService;
use Illuminate\Support\Facades\Log;

class CleanupOldAttendancePhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:cleanup-photos {--months=6 : Number of months to keep photos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old attendance photos older than specified months';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $monthsToKeep = (int) $this->option('months');
        
        $this->info("Starting cleanup of attendance photos older than {$monthsToKeep} months...");
        
        // Get storage stats before cleanup
        $this->info('Getting storage statistics before cleanup...');
        $statsBefore = AttendancePhotoService::getStorageStats();
        
        if (!empty($statsBefore)) {
            $this->table(
                ['Semester', 'Files Count', 'Size (MB)'],
                collect($statsBefore)->map(function ($stats, $semester) {
                    return [$semester, $stats['files_count'], $stats['total_size_mb']];
                })->toArray()
            );
        }
        
        // Get folders to be deleted
        $oldFolders = AttendancePhotoService::getOldSemesterFolders($monthsToKeep);
        
        if (empty($oldFolders)) {
            $this->info('No old folders found to delete.');
            return 0;
        }
        
        $this->warn('The following folders will be deleted:');
        foreach ($oldFolders as $folder) {
            $this->line("  - {$folder}");
        }
        
        if (!$this->confirm('Do you want to proceed with the deletion?')) {
            $this->info('Cleanup cancelled.');
            return 0;
        }
        
        // Perform cleanup
        $result = AttendancePhotoService::deleteOldFolders($monthsToKeep);
        
        // Display results
        if ($result['total_deleted'] > 0) {
            $this->info("Successfully deleted {$result['total_deleted']} folders:");
            foreach ($result['deleted'] as $folder) {
                $this->line("  ✓ {$folder}");
            }
        }
        
        if (!empty($result['errors'])) {
            $this->error('Errors occurred during cleanup:');
            foreach ($result['errors'] as $error) {
                $this->line("  ✗ {$error}");
            }
        }
        
        // Log the cleanup activity
        Log::info('Attendance photos cleanup completed', [
            'months_to_keep' => $monthsToKeep,
            'folders_deleted' => $result['total_deleted'],
            'deleted_folders' => $result['deleted'],
            'errors' => $result['errors']
        ]);
        
        $this->info('Cleanup completed!');
        
        return 0;
    }
}