<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AttendancePhotoService
{
    /**
     * Get semester folder based on date
     * Format: YYYY-S (e.g., 2026-1 for first semester 2026)
     */
    public static function getSemesterFolder($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now('Asia/Makassar');
        
        // Semester 1: January - June (1-6)
        // Semester 2: July - December (7-12)
        $semester = $date->month <= 6 ? 1 : 2;
        
        return $date->year . '-' . $semester;
    }

    /**
     * Store attendance photo with semester-based folder structure
     */
    public static function storePhoto($file, $type, $studentId, $date)
    {
        if (!$file) {
            return null;
        }

        $semesterFolder = self::getSemesterFolder($date);
        $dateStr = Carbon::parse($date)->format('Y-m-d');
        $timestamp = time();
        
        $filename = "{$type}_{$studentId}_{$dateStr}_{$timestamp}." . $file->getClientOriginalExtension();
        $path = "attendance/photos/{$semesterFolder}";
        
        try {
            $photoPath = $file->storeAs($path, $filename, 'public');
            
            Log::info('Photo stored successfully', [
                'path' => $photoPath,
                'semester_folder' => $semesterFolder,
                'student_id' => $studentId,
                'type' => $type
            ]);
            
            return $photoPath;
        } catch (\Exception $e) {
            Log::error('Failed to store photo', [
                'error' => $e->getMessage(),
                'student_id' => $studentId,
                'type' => $type,
                'semester_folder' => $semesterFolder
            ]);
            
            return null;
        }
    }

    /**
     * Get list of old semester folders to delete
     * Returns folders older than specified months
     */
    public static function getOldSemesterFolders($monthsToKeep = 6)
    {
        $cutoffDate = Carbon::now('Asia/Makassar')->subMonths($monthsToKeep);
        $cutoffSemester = self::getSemesterFolder($cutoffDate);
        
        $basePath = 'attendance/photos';
        $folders = [];
        
        try {
            $allFolders = Storage::disk('public')->directories($basePath);
            
            foreach ($allFolders as $folder) {
                $folderName = basename($folder);
                
                // Check if folder name matches semester format (YYYY-S)
                if (preg_match('/^(\d{4})-([12])$/', $folderName, $matches)) {
                    $year = (int)$matches[1];
                    $semester = (int)$matches[2];
                    
                    // Convert to comparable format
                    $folderValue = $year * 10 + $semester;
                    $cutoffValue = (int)substr($cutoffSemester, 0, 4) * 10 + (int)substr($cutoffSemester, -1);
                    
                    if ($folderValue < $cutoffValue) {
                        $folders[] = $folder;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to get old semester folders', [
                'error' => $e->getMessage(),
                'cutoff_date' => $cutoffDate->format('Y-m-d'),
                'cutoff_semester' => $cutoffSemester
            ]);
        }
        
        return $folders;
    }

    /**
     * Delete old semester folders
     */
    public static function deleteOldFolders($monthsToKeep = 6)
    {
        $oldFolders = self::getOldSemesterFolders($monthsToKeep);
        $deletedFolders = [];
        $errors = [];
        
        foreach ($oldFolders as $folder) {
            try {
                if (Storage::disk('public')->deleteDirectory($folder)) {
                    $deletedFolders[] = $folder;
                    Log::info('Deleted old semester folder', ['folder' => $folder]);
                } else {
                    $errors[] = "Failed to delete folder: {$folder}";
                }
            } catch (\Exception $e) {
                $error = "Error deleting folder {$folder}: " . $e->getMessage();
                $errors[] = $error;
                Log::error($error);
            }
        }
        
        return [
            'deleted' => $deletedFolders,
            'errors' => $errors,
            'total_deleted' => count($deletedFolders)
        ];
    }

    /**
     * Get storage statistics
     */
    public static function getStorageStats()
    {
        $basePath = 'attendance/photos';
        $stats = [];
        
        try {
            $folders = Storage::disk('public')->directories($basePath);
            
            foreach ($folders as $folder) {
                $folderName = basename($folder);
                $files = Storage::disk('public')->files($folder);
                $totalSize = 0;
                
                foreach ($files as $file) {
                    $totalSize += Storage::disk('public')->size($file);
                }
                
                $stats[$folderName] = [
                    'files_count' => count($files),
                    'total_size' => $totalSize,
                    'total_size_mb' => round($totalSize / 1024 / 1024, 2)
                ];
            }
        } catch (\Exception $e) {
            Log::error('Failed to get storage stats', ['error' => $e->getMessage()]);
        }
        
        return $stats;
    }
}