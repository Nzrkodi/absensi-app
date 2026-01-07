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
        Schema::create('violation_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama pelanggaran
            $table->text('description')->nullable(); // Deskripsi detail
            $table->enum('category', ['ringan', 'sedang', 'berat'])->default('ringan'); // Kategori
            $table->integer('points')->default(1); // Poin pelanggaran
            $table->enum('status', ['active', 'inactive'])->default('active'); // Status aktif/nonaktif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violation_types');
    }
};