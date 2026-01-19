<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama lokasi (misal: "Gedung Utama", "Lapangan", "Lab Komputer")
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('radius_meters')->default(50); // Radius dalam meter
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#007bff'); // Warna untuk map marker
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_locations');
    }
};