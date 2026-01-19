<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Photo columns
            $table->string('clock_in_photo')->nullable()->after('clock_in');
            $table->string('clock_out_photo')->nullable()->after('clock_out');
            
            // Location columns
            $table->decimal('clock_in_latitude', 10, 8)->nullable()->after('clock_in_photo');
            $table->decimal('clock_in_longitude', 11, 8)->nullable()->after('clock_in_latitude');
            $table->decimal('clock_out_latitude', 10, 8)->nullable()->after('clock_out_photo');
            $table->decimal('clock_out_longitude', 11, 8)->nullable()->after('clock_out_latitude');
            
            // Location validation
            $table->boolean('location_verified')->default(false)->after('clock_out_longitude');
            $table->decimal('distance_from_school', 8, 2)->nullable()->after('location_verified');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'clock_in_photo',
                'clock_out_photo', 
                'clock_in_latitude',
                'clock_in_longitude',
                'clock_out_latitude',
                'clock_out_longitude',
                'location_verified',
                'distance_from_school'
            ]);
        });
    }
};