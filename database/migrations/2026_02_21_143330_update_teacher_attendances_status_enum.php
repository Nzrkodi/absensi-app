<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, alter the enum column to include both old and new values temporarily
        DB::statement("ALTER TABLE teacher_attendances MODIFY COLUMN status ENUM('present', 'late', 'absent', 'permission', 'sick', 'hadir', 'alpha', 'izin', 'sakit') DEFAULT 'present'");
        
        // Then update existing data to new values
        DB::statement("UPDATE teacher_attendances SET status = 'hadir' WHERE status = 'present'");
        DB::statement("UPDATE teacher_attendances SET status = 'alpha' WHERE status = 'absent'");
        DB::statement("UPDATE teacher_attendances SET status = 'izin' WHERE status = 'permission'");
        
        // Finally, alter the enum column to only have new values
        DB::statement("ALTER TABLE teacher_attendances MODIFY COLUMN status ENUM('hadir', 'alpha', 'izin', 'sakit') DEFAULT 'hadir'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum values
        DB::statement("ALTER TABLE teacher_attendances MODIFY COLUMN status ENUM('present', 'late', 'absent', 'permission', 'sick') DEFAULT 'present'");
        
        // Revert data
        DB::statement("UPDATE teacher_attendances SET status = 'present' WHERE status = 'hadir'");
        DB::statement("UPDATE teacher_attendances SET status = 'absent' WHERE status = 'alpha'");
        DB::statement("UPDATE teacher_attendances SET status = 'permission' WHERE status = 'izin'");
    }
};
