<?php

namespace Database\Seeders;

use App\Models\Classes;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            ['student_code' => '2024001', 'name' => 'Ahmad Rizki', 'class' => 'X-A'],
            ['student_code' => '2024002', 'name' => 'Siti Nurhaliza', 'class' => 'X-A'],
            ['student_code' => '2024003', 'name' => 'Budi Santoso', 'class' => 'X-A'],
            ['student_code' => '2024004', 'name' => 'Dewi Lestari', 'class' => 'X-B'],
            ['student_code' => '2024005', 'name' => 'Eko Prasetyo', 'class' => 'X-B'],
            ['student_code' => '2024006', 'name' => 'Fitri Handayani', 'class' => 'X-B'],
            ['student_code' => '2024007', 'name' => 'Galih Pratama', 'class' => 'XI-A'],
            ['student_code' => '2024008', 'name' => 'Hana Safitri', 'class' => 'XI-A'],
            ['student_code' => '2024009', 'name' => 'Irfan Hakim', 'class' => 'XI-A'],
            ['student_code' => '2024010', 'name' => 'Jasmine Putri', 'class' => 'XI-B'],
            ['student_code' => '2024011', 'name' => 'Kevin Anggara', 'class' => 'XI-B'],
            ['student_code' => '2024012', 'name' => 'Laras Wulandari', 'class' => 'XI-B'],
            ['student_code' => '2024013', 'name' => 'Muhammad Fauzi', 'class' => 'XII-A'],
            ['student_code' => '2024014', 'name' => 'Nadia Permata', 'class' => 'XII-A'],
            ['student_code' => '2024015', 'name' => 'Oscar Wijaya', 'class' => 'XII-B'],
        ];

        foreach ($students as $data) {
            $class = Classes::where('name', $data['class'])->first();

            $user = User::create([
                'name' => $data['name'],
                'email' => strtolower(str_replace(' ', '.', $data['name'])) . '@student.test',
                'password' => Hash::make('password'),
                'role' => 'employee',
            ]);

            Student::create([
                'user_id' => $user->id,
                'student_code' => $data['student_code'],
                'class_id' => $class?->id,
            ]);
        }
    }
}
