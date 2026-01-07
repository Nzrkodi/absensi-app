<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Classes;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    private $classes;

    public function __construct()
    {
        // Cache classes untuk menghindari query berulang
        $this->classes = Classes::pluck('id', 'name')->toArray();
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Skip baris kosong
        if (empty(trim($row['nama'] ?? '')) || empty(trim($row['nisn'] ?? ''))) {
            return null;
        }
        
        // Cari class_id dari cache dengan berbagai format
        $className = trim($row['kelas'] ?? '');
        $classId = null;
        
        if (!empty($className)) {
            // Coba cari langsung dulu (exact match)
            if (isset($this->classes[$className])) {
                $classId = $this->classes[$className];
            } else {
                // Mapping untuk berbagai format kelas ke format sederhana (10, 11, 12)
                $classMapping = [
                    // Format romawi ke angka
                    'X' => '10', 'x' => '10',
                    'XI' => '11', 'xi' => '11', 
                    'XII' => '12', 'xii' => '12',
                    // Format dengan kelas
                    'Kelas 10' => '10', 'kelas 10' => '10',
                    'Kelas 11' => '11', 'kelas 11' => '11',
                    'Kelas 12' => '12', 'kelas 12' => '12',
                    'Kelas X' => '10', 'kelas x' => '10',
                    'Kelas XI' => '11', 'kelas xi' => '11',
                    'Kelas XII' => '12', 'kelas xii' => '12'
                ];
                
                // Coba mapping
                if (isset($classMapping[$className])) {
                    $mappedClass = $classMapping[$className];
                    if (isset($this->classes[$mappedClass])) {
                        $classId = $this->classes[$mappedClass];
                    }
                } else {
                    // Coba cari dengan case-insensitive
                    foreach ($this->classes as $dbClassName => $dbClassId) {
                        if (strcasecmp($dbClassName, $className) === 0) {
                            $classId = $dbClassId;
                            break;
                        }
                    }
                }
            }
        }
        
        // Handle tanggal lahir yang kosong
        $birthDate = null;
        $birthDateValue = trim($row['tanggal_lahir'] ?? '');
        if (!empty($birthDateValue) && $birthDateValue !== '-' && $birthDateValue !== 'null') {
            try {
                // Coba berbagai format tanggal
                if (strpos($birthDateValue, '/') !== false) {
                    $birthDate = \Carbon\Carbon::createFromFormat('d/m/Y', $birthDateValue)->format('Y-m-d');
                } elseif (strpos($birthDateValue, '-') !== false) {
                    $birthDate = \Carbon\Carbon::createFromFormat('Y-m-d', $birthDateValue)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $birthDate = null; // Jika format tanggal salah, set null
            }
        }
        
        // Helper function untuk clean data
        $cleanValue = function($value) {
            $cleaned = trim($value ?? '');
            return (!empty($cleaned) && $cleaned !== '-' && $cleaned !== 'null') ? $cleaned : null;
        };
        
        // Validasi class_id - jika tidak ditemukan, skip row ini
        if (!$classId) {
            // Log error untuk debugging tapi tidak terlalu verbose
            Log::warning("Import: Kelas '{$className}' tidak ditemukan untuk siswa: " . trim($row['nama'] ?? ''));
            return null; // Skip row ini
        }
        
        $studentData = [
            'name' => trim($row['nama'] ?? ''),
            'nisn' => trim($row['nisn'] ?? ''),
            'class_id' => $classId,
            'birth_place' => $cleanValue($row['tempat_lahir'] ?? ''),
            'birth_date' => $birthDate,
            'phone' => $cleanValue($row['no_handphone'] ?? ''),
            'address' => $cleanValue($row['alamat'] ?? ''),
            'status' => 'active'
        ];
        
        return new Student($studentData);
    }

    /**
     * Validation rules for each row
     */
    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'nisn' => 'required|max:20|unique:students,nisn', // Removed string validation
            // Hapus validasi kelas karena kita handle manual di model()
            'tempat_lahir' => 'nullable|max:255',
            'tanggal_lahir' => 'nullable|max:20',
            'no_handphone' => 'nullable|max:20',
            'alamat' => 'nullable|max:500'
        ];
    }

    /**
     * Custom error messages
     */
    public function customValidationMessages()
    {
        return [
            'nama.required' => 'Nama siswa wajib diisi (baris :row)',
            'nama.string' => 'Nama siswa harus berupa teks (baris :row)',
            'nama.max' => 'Nama siswa maksimal 255 karakter (baris :row)',
            
            'nisn.required' => 'NISN wajib diisi (baris :row)',
            'nisn.max' => 'NISN maksimal 20 karakter (baris :row)',
            'nisn.unique' => 'NISN sudah ada dalam database (baris :row)',
            
            'tempat_lahir.max' => 'Tempat lahir maksimal 255 karakter (baris :row)',
            'tanggal_lahir.max' => 'Tanggal lahir maksimal 20 karakter (baris :row)',
            'no_handphone.max' => 'No handphone maksimal 20 karakter (baris :row)',
            'alamat.max' => 'Alamat maksimal 500 karakter (baris :row)'
        ];
    }

    /**
     * Batch insert untuk performa lebih baik
     */
    public function batchSize(): int
    {
        return 50; // Reduced from 100
    }

    /**
     * Chunk reading untuk menghindari memory issues
     */
    public function chunkSize(): int
    {
        return 50; // Reduced from 100
    }
}
