<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'school_start_time',
                'value' => '07:00',
                'type' => 'time',
                'description' => 'Waktu mulai sekolah (jam berapa siswa harus sudah masuk)'
            ],
            [
                'key' => 'late_tolerance_minutes',
                'value' => '15',
                'type' => 'integer',
                'description' => 'Toleransi keterlambatan dalam menit (setelah ini dianggap terlambat)'
            ],
            [
                'key' => 'auto_absent_time',
                'value' => '15:00',
                'type' => 'time',
                'description' => 'Waktu otomatis menandai siswa absent jika belum clock in'
            ],
            [
                'key' => 'school_name',
                'value' => 'SMA Negeri 1',
                'type' => 'string',
                'description' => 'Nama sekolah'
            ],
            [
                'key' => 'allow_early_clockin',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Izinkan clock in sebelum jam mulai sekolah'
            ]
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Default settings created successfully!');
    }
}