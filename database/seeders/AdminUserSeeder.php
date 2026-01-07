<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'position' => 'Administrator',
            'subject' => 'System Administration',
            'email_verified_at' => now(),
        ]);

        // Create teacher user for testing
        User::create([
            'name' => 'Guru Test',
            'email' => 'guru@test.com',
            'password' => Hash::make('guru123'),
            'role' => 'teacher',
            'position' => 'Guru',
            'subject' => 'Matematika',
            'email_verified_at' => now(),
        ]);
    }
}