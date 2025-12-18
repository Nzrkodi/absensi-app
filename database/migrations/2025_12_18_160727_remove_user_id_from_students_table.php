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
            // Drop foreign key constraint first
            $table->dropForeign(['user_id']);
            // Then drop the column
            $table->dropColumn('user_id');
            // Add name column to store student name directly
            $table->string('name')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Remove name column
            $table->dropColumn('name');
            // Add back user_id column
            $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
        });
    }
};
