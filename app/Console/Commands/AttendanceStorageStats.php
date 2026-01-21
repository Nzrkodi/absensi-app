<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AttendancePhotoService;

class AttendanceStorageStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:storage-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display attendance photos storage statistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Attendance Photos Storage Statistics');
        $this->line('=====================================');
        
        $stats = AttendancePhotoService::getStorageStats();
        
        if (empty($stats)) {
            $this->warn('No attendance photos found in storage.');
            return 0;
        }
        
        $totalFiles = 0;
        $totalSizeMB = 0;
        
        $tableData = [];
        foreach ($stats as $semester => $data) {
            $tableData[] = [
                $semester,
                $data['files_count'],
                $data['total_size_mb'] . ' MB'
            ];
            
            $totalFiles += $data['files_count'];
            $totalSizeMB += $data['total_size_mb'];
        }
        
        // Add total row
        $tableData[] = ['---', '---', '---'];
        $tableData[] = ['TOTAL', $totalFiles, round($totalSizeMB, 2) . ' MB'];
        
        $this->table(
            ['Semester', 'Files Count', 'Total Size'],
            $tableData
        );
        
        // Show current semester
        $currentSemester = AttendancePhotoService::getSemesterFolder();
        $this->info("Current semester: {$currentSemester}");
        
        // Show old folders that would be deleted
        $oldFolders = AttendancePhotoService::getOldSemesterFolders(6);
        if (!empty($oldFolders)) {
            $this->warn('Folders older than 6 months (would be deleted by cleanup):');
            foreach ($oldFolders as $folder) {
                $folderName = basename($folder);
                $folderStats = $stats[$folderName] ?? null;
                if ($folderStats) {
                    $this->line("  - {$folderName} ({$folderStats['files_count']} files, {$folderStats['total_size_mb']} MB)");
                } else {
                    $this->line("  - {$folderName}");
                }
            }
        } else {
            $this->info('No folders older than 6 months found.');
        }
        
        return 0;
    }
}