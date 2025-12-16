<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [
            // Hari Libur Nasional 2025
            [
                'name' => 'Tahun Baru Masehi',
                'date' => '2025-01-01',
                'description' => 'Hari libur nasional perayaan tahun baru',
                'type' => 'national'
            ],
            [
                'name' => 'Isra Miraj',
                'date' => '2025-01-27',
                'description' => 'Hari besar Islam',
                'type' => 'national'
            ],
            [
                'name' => 'Tahun Baru Imlek',
                'date' => '2025-01-29',
                'description' => 'Perayaan tahun baru Tionghoa',
                'type' => 'national'
            ],
            [
                'name' => 'Hari Raya Nyepi',
                'date' => '2025-03-29',
                'description' => 'Hari raya Hindu',
                'type' => 'national'
            ],
            [
                'name' => 'Wafat Isa Almasih',
                'date' => '2025-04-18',
                'description' => 'Hari besar Kristen',
                'type' => 'national'
            ],
            [
                'name' => 'Hari Buruh',
                'date' => '2025-05-01',
                'description' => 'Hari libur nasional untuk pekerja',
                'type' => 'national'
            ],
            [
                'name' => 'Kenaikan Isa Almasih',
                'date' => '2025-05-29',
                'description' => 'Hari besar Kristen',
                'type' => 'national'
            ],
            [
                'name' => 'Hari Lahir Pancasila',
                'date' => '2025-06-01',
                'description' => 'Hari bersejarah Indonesia',
                'type' => 'national'
            ],
            [
                'name' => 'Idul Fitri',
                'date' => '2025-03-30',
                'description' => 'Hari raya Islam',
                'type' => 'national'
            ],
            [
                'name' => 'Cuti Bersama Idul Fitri',
                'date' => '2025-03-31',
                'description' => 'Cuti bersama Idul Fitri',
                'type' => 'national'
            ],
            [
                'name' => 'Hari Kemerdekaan RI',
                'date' => '2025-08-17',
                'description' => 'Hari kemerdekaan Indonesia',
                'type' => 'national'
            ],
            [
                'name' => 'Idul Adha',
                'date' => '2025-06-07',
                'description' => 'Hari raya Islam',
                'type' => 'national'
            ],
            [
                'name' => 'Tahun Baru Hijriah',
                'date' => '2025-06-27',
                'description' => 'Tahun baru Islam',
                'type' => 'national'
            ],
            [
                'name' => 'Maulid Nabi Muhammad SAW',
                'date' => '2025-09-05',
                'description' => 'Hari kelahiran Nabi Muhammad',
                'type' => 'national'
            ],
            [
                'name' => 'Hari Natal',
                'date' => '2025-12-25',
                'description' => 'Hari raya Kristen',
                'type' => 'national'
            ],
            
            // Contoh hari libur sekolah
            [
                'name' => 'Libur Semester Ganjil',
                'date' => '2025-12-23',
                'description' => 'Libur akhir semester ganjil',
                'type' => 'school'
            ],
            [
                'name' => 'Libur Semester Ganjil',
                'date' => '2025-12-24',
                'description' => 'Libur akhir semester ganjil',
                'type' => 'school'
            ],
            [
                'name' => 'Libur Semester Ganjil',
                'date' => '2025-12-26',
                'description' => 'Libur akhir semester ganjil',
                'type' => 'school'
            ],
            [
                'name' => 'Libur Semester Ganjil',
                'date' => '2025-12-27',
                'description' => 'Libur akhir semester ganjil',
                'type' => 'school'
            ]
        ];

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                ['date' => $holiday['date']],
                $holiday
            );
        }

        $this->command->info('Holiday data seeded successfully!');
    }
}