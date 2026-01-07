<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG IMPORT PROCESS ===\n";

// Simulasi data dari Excel kamu (data real)
$testRows = [
    [
        'nama' => 'Aidil Nur',
        'nisn' => '0096183456',
        'kelas' => '10',
        'tempat_lahir' => 'Palu',
        'tanggal_lahir' => '02/11/2009',
        'no_handphone' => '085166514370',
        'alamat' => 'Jl Sungai Sausu'
    ],
    [
        'nama' => 'Alifah Maharani',
        'nisn' => '0099521714',
        'kelas' => '10',
        'tempat_lahir' => 'Palu',
        'tanggal_lahir' => '29/12/2009',
        'no_handphone' => '087731265835',
        'alamat' => 'Jl Nuri'
    ],
    [
        'nama' => 'Alyah Rani Habibullah',
        'nisn' => '0106840215',
        'kelas' => '10',
        'tempat_lahir' => 'Kendari',
        'tanggal_lahir' => '13/10/2010',
        'no_handphone' => '087743194776',
        'alamat' => 'Jl Kijang Raya'
    ],
    [
        'nama' => 'Dimas Mulana',
        'nisn' => '0092316490',
        'kelas' => '10',
        'tempat_lahir' => 'Palu',
        'tanggal_lahir' => '05/10/2009',
        'no_handphone' => '',
        'alamat' => ''
    ],
    [
        'nama' => 'Diva Nur Aisyah',
        'nisn' => '0983929210',
        'kelas' => '10',
        'tempat_lahir' => '',
        'tanggal_lahir' => '',
        'no_handphone' => '082191721165',
        'alamat' => 'Jl Labu'
    ],
    [
        'nama' => 'Gatil Khairina',
        'nisn' => '3095020108',
        'kelas' => '10',
        'tempat_lahir' => 'Dolago',
        'tanggal_lahir' => '25/09/2009',
        'no_handphone' => '085259037827',
        'alamat' => 'Jl Labu'
    ]
];

// Load classes
$classes = App\Models\Classes::pluck('id', 'name')->toArray();
echo "Available classes:\n";
foreach ($classes as $name => $id) {
    echo "  '{$name}' => {$id}\n";
}
echo "\n";

foreach ($testRows as $index => $row) {
    echo "=== PROCESSING ROW " . ($index + 1) . " ===\n";
    echo "Data: " . json_encode($row) . "\n";
    
    try {
        // Simulasi proses import
        $className = trim($row['kelas'] ?? '');
        $classId = null;
        
        // Class mapping logic
        if (isset($classes[$className])) {
            $classId = $classes[$className];
        } else {
            $classNameWithPrefix = "Kelas " . $className;
            if (isset($classes[$classNameWithPrefix])) {
                $classId = $classes[$classNameWithPrefix];
            }
        }
        
        echo "Class mapping: '{$className}' -> ID: {$classId}\n";
        
        // Date processing
        $birthDate = null;
        $birthDateValue = trim($row['tanggal_lahir'] ?? '');
        if (!empty($birthDateValue) && $birthDateValue !== '-') {
            try {
                $birthDate = \Carbon\Carbon::createFromFormat('d/m/Y', $birthDateValue)->format('Y-m-d');
                echo "Date converted: '{$birthDateValue}' -> '{$birthDate}'\n";
            } catch (\Exception $e) {
                echo "Date conversion failed: " . $e->getMessage() . "\n";
            }
        }
        
        // Clean data
        $cleanValue = function($value) {
            $cleaned = trim($value ?? '');
            return (!empty($cleaned) && $cleaned !== '-') ? $cleaned : null;
        };
        
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
        
        echo "Final data: " . json_encode($studentData) . "\n";
        
        // Try to create student
        $student = new App\Models\Student($studentData);
        echo "✅ Student model created successfully\n";
        
        // Try to save (but don't actually save)
        // $student->save();
        echo "✅ Would save successfully\n";
        
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
    }
    
    echo "\n";
}

echo "=== DEBUG COMPLETED ===\n";