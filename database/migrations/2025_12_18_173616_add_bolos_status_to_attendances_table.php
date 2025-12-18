<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Modify the status enum to include 'bolos'
            $table->enum('status', ['present', 'late', 'absent', 'permission', 'sick', 'bolos'])
                  ->default('present')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Revert back to original enum values
            $table->enum('status', ['present', 'late', 'absent', 'permission', 'sick'])
                  ->default('present')
                  ->change();
        });
    }
};