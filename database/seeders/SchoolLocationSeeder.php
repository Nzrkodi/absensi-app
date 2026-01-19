<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolLocation;

class SchoolLocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Gedung Utama',
                'latitude' => -5.147665, // Ganti dengan koordinat sekolah yang sebenarnya
                'longitude' => 119.432732,
                'radius_meters' => 100,
                'description' => 'Area gedung utama sekolah - tempat utama untuk absensi',
                'color' => '#007bff',
                'is_active' => true
            ],
            [
                'name' => 'Lapangan Sekolah',
                'latitude' => -5.147800, // Contoh koordinat lapangan
                'longitude' => 119.432900,
                'radius_meters' => 150,
                'description' => 'Area lapangan sekolah - untuk kegiatan olahraga',
                'color' => '#28a745',
                'is_active' => true
            ],
            [
                'name' => 'Laboratorium Komputer',
                'latitude' => -5.147500, // Contoh koordinat lab
                'longitude' => 119.432600,
                'radius_meters' => 50,
                'description' => 'Area laboratorium komputer',
                'color' => '#ffc107',
                'is_active' => true
            ]
        ];

        foreach ($locations as $location) {
            SchoolLocation::create($location);
        }
    }
}