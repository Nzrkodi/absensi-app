<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateStudentNamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // This seeder is no longer needed since we've already migrated the structure
        // But we'll keep it for reference
        $this->command->info('Student names migration completed during table structure change.');
    }
}
