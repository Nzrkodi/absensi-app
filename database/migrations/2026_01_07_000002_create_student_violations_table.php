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
        Schema::create('student_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade'); // Relasi ke students
            $table->foreignId('violation_type_id')->constrained()->onDelete('cascade'); // Relasi ke violation_types
            $table->date('violation_date'); // Tanggal pelanggaran
            $table->time('violation_time')->nullable(); // Waktu pelanggaran
            $table->string('location')->nullable(); // Lokasi pelanggaran
            $table->text('description')->nullable(); // Keterangan tambahan
            $table->string('reported_by')->nullable(); // Dilaporkan oleh (guru/staff)
            $table->enum('status', ['pending', 'confirmed', 'resolved'])->default('pending'); // Status penanganan
            $table->text('resolution_notes')->nullable(); // Catatan penyelesaian
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_violations');
    }
};