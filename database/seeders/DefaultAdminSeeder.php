<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DefaultAdminSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create default super admin account
        User::updateOrCreate(
            ['email' => 'aditya.wahyu@smaitpersis.sch.id'],
            [
                'name' => 'Aditya Wahyu',
                'email' => 'aditya.wahyu@smaitpersis.sch.id',
                'password' => Hash::make('admin123456'),
                'role' => 'admin',
                'position' => 'Super Administrator',
                'subject' => 'Sistem Administrator',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Default Super Admin created successfully!');
        $this->command->info('📧 Email: aditya.wahyu@smaitpersis.sch.id');
        $this->command->info('🔑 Password: admin123456');
        $this->command->warn('⚠️  PENTING: Segera ubah password setelah login pertama!');
    }
}