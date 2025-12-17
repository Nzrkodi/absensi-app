<?php

namespace Database\Seeders;

use App\Models\Classes;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            ['name' => 'Kelas X'],
            ['name' => 'Kelas XI'],
            ['name' => 'Kelas XII'],
        ];

        foreach ($classes as $class) {
            Classes::updateOrCreate(
                ['name' => $class['name']],
                $class
            );
        }

        $this->command->info('School classes seeded successfully!');
    }
}
