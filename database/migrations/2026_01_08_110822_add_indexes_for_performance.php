<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Add indexes for better performance
            $table->index(['status', 'name'], 'idx_students_status_name');
            $table->index(['class_id', 'status'], 'idx_students_class_status');
            $table->index(['nisn'], 'idx_students_nisn');
            $table->index(['name'], 'idx_students_name');
        });

        Schema::table('attendances', function (Blueprint $table) {
            // Add composite index for date and student_id
            $table->index(['date', 'student_id'], 'idx_attendances_date_student');
            $table->index(['student_id', 'date'], 'idx_attendances_student_date');
            $table->index(['date', 'status'], 'idx_attendances_date_status');
        });

        Schema::table('classes', function (Blueprint $table) {
            // Add index for class name ordering
            $table->index(['name'], 'idx_classes_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_students_status_name');
            $table->dropIndex('idx_students_class_status');
            $table->dropIndex('idx_students_nisn');
            $table->dropIndex('idx_students_name');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_attendances_date_student');
            $table->dropIndex('idx_attendances_student_date');
            $table->dropIndex('idx_attendances_date_status');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropIndex('idx_classes_name');
        });
    }
};