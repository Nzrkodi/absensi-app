<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 50)->unique()->comment('Nomor Induk Pegawai');
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('email')->unique();
            $table->enum('jenis_kelamin', ['L', 'P'])->comment('L=Laki-laki, P=Perempuan');
            $table->string('nomor_hp', 20)->nullable();
            $table->string('jabatan')->nullable()->comment('Contoh: Guru Tetap, Guru Honorer, Kepala Sekolah');
            $table->string('mata_pelajaran')->nullable()->comment('Mata pelajaran yang diampu');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
