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
        Schema::table('users', function (Blueprint $table) {
            // Check if role column exists, if not add it, if exists modify it
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 50)->default('user')->after('password');
            } else {
                // Modify existing role column to ensure it's long enough
                $table->string('role', 50)->default('user')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Don't drop the role column as it might be needed
        });
    }
};
