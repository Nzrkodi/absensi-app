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
        Schema::table('students', function (Blueprint $table) {
            // Add nisn column
            $table->string('nisn')->after('class_id')->nullable();
        });
        
        // Copy data from student_code to nisn
        DB::statement('UPDATE students SET nisn = student_code');
        
        // Drop old column
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('student_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Add back student_code column
            $table->string('student_code')->after('class_id')->nullable();
        });
        
        // Copy data back from nisn to student_code
        DB::statement('UPDATE students SET student_code = nisn');
        
        // Drop nisn column
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('nisn');
        });
    }
};
