<?php

namespace Database\Seeders;

use App\Models\Classes;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some test classes if they don't exist
        $classes = [
            ['name' => 'X IPA 1'],
            ['name' => 'X IPA 2'],
            ['name' => 'XI IPA 1'],
        ];

        foreach ($classes as $classData) {
            Classes::firstOrCreate(['name' => $classData['name']], $classData);
        }

        // Create test students
        $students = [
            [
                'name' => 'Ahmad Rizki',
                'email' => 'ahmad.rizki@student.test',
                'student_code' => 'STD001',
                'class_name' => 'X IPA 1',
                'phone' => '081234567890',
                'address' => 'Jl. Merdeka No. 1, Jakarta'
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@student.test',
                'student_code' => 'STD002',
                'class_name' => 'X IPA 1',
                'phone' => '081234567891',
                'address' => 'Jl. Sudirman No. 2, Jakarta'
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@student.test',
                'student_code' => 'STD003',
                'class_name' => 'X IPA 2',
                'phone' => '081234567892',
                'address' => 'Jl. Thamrin No. 3, Jakarta'
            ],
            [
                'name' => 'Dewi Sartika',
                'email' => 'dewi.sartika@student.test',
                'student_code' => 'STD004',
                'class_name' => 'XI IPA 1',
                'phone' => '081234567893',
                'address' => 'Jl. Gatot Subroto No. 4, Jakarta'
            ],
            [
                'name' => 'Andi Wijaya',
                'email' => 'andi.wijaya@student.test',
                'student_code' => 'STD005',
                'class_name' => 'XI IPA 1',
                'phone' => '081234567894',
                'address' => 'Jl. Kuningan No. 5, Jakarta'
            ]
        ];

        foreach ($students as $studentData) {
            // Find the class
            $class = Classes::where('name', $studentData['class_name'])->first();
            
            if (!$class) continue;

            // Check if user already exists
            $existingUser = User::where('email', $studentData['email'])->first();
            
            if (!$existingUser) {
                // Create user
                $user = User::create([
                    'name' => $studentData['name'],
                    'email' => $studentData['email'],
                    'password' => bcrypt('password123'),
                ]);

                // Create student
                Student::create([
                    'user_id' => $user->id,
                    'student_code' => $studentData['student_code'],
                    'class_id' => $class->id,
                    'phone' => $studentData['phone'],
                    'address' => $studentData['address'],
                    'status' => 'active',
                ]);

                $this->command->info("Created student: {$studentData['name']}");
            } else {
                $this->command->info("Student {$studentData['name']} already exists, skipping...");
            }
        }
    }
}