<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AttendancePhotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class StorageController extends Controller
{
    public function attendancePhotos()
    {
        $stats = AttendancePhotoService::getStorageStats();
        $currentSemester = AttendancePhotoService::getSemesterFolder();
        $oldFolders = AttendancePhotoService::getOldSemesterFolders(6);
        
        return view('admin.storage.attendance-photos', compact(
            'stats',
            'currentSemester', 
            'oldFolders'
        ));
    }
    
    public function cleanupPhotos(Request $request)
    {
        $request->validate([
            'months' => 'required|integer|min:1|max:24'
        ]);
        
        $months = $request->months;
        
        try {
            $result = AttendancePhotoService::deleteOldFolders($months);
            
            if ($result['total_deleted'] > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menghapus {$result['total_deleted']} folder lama",
                    'data' => $result
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada folder lama yang perlu dihapus',
                    'data' => $result
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }
    
    public function getStorageStats()
    {
        try {
            $stats = AttendancePhotoService::getStorageStats();
            $currentSemester = AttendancePhotoService::getSemesterFolder();
            $oldFolders = AttendancePhotoService::getOldSemesterFolders(6);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'current_semester' => $currentSemester,
                    'old_folders' => $oldFolders
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik storage: ' . $e->getMessage()
            ]);
        }
    }
}