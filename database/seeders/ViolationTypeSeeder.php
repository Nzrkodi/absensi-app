<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ViolationType;

class ViolationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $violationTypes = [
            // Pelanggaran Ringan (1-5 poin)
            [
                'name' => 'Terlambat Masuk Kelas',
                'description' => 'Siswa terlambat masuk kelas lebih dari 15 menit',
                'category' => 'ringan',
                'points' => 2,
                'status' => 'active'
            ],
            [
                'name' => 'Tidak Mengerjakan PR',
                'description' => 'Siswa tidak mengerjakan pekerjaan rumah yang diberikan guru',
                'category' => 'ringan',
                'points' => 3,
                'status' => 'active'
            ],
            [
                'name' => 'Tidak Membawa Buku Pelajaran',
                'description' => 'Siswa tidak membawa buku pelajaran sesuai jadwal',
                'category' => 'ringan',
                'points' => 2,
                'status' => 'active'
            ],
            [
                'name' => 'Seragam Tidak Lengkap',
                'description' => 'Siswa tidak memakai seragam sekolah dengan lengkap',
                'category' => 'ringan',
                'points' => 3,
                'status' => 'active'
            ],
            [
                'name' => 'Tidak Mengikuti Upacara',
                'description' => 'Siswa tidak mengikuti upacara bendera tanpa izin',
                'category' => 'ringan',
                'points' => 4,
                'status' => 'active'
            ],

            // Pelanggaran Sedang (6-15 poin)
            [
                'name' => 'Bolos Pelajaran',
                'description' => 'Siswa tidak masuk kelas tanpa izin yang jelas',
                'category' => 'sedang',
                'points' => 8,
                'status' => 'active'
            ],
            [
                'name' => 'Menyontek Saat Ujian',
                'description' => 'Siswa melakukan kecurangan saat ujian atau ulangan',
                'category' => 'sedang',
                'points' => 10,
                'status' => 'active'
            ],
            [
                'name' => 'Tidak Sopan kepada Guru',
                'description' => 'Siswa bersikap tidak sopan atau kurang hormat kepada guru',
                'category' => 'sedang',
                'points' => 12,
                'status' => 'active'
            ],
            [
                'name' => 'Merusak Fasilitas Sekolah',
                'description' => 'Siswa merusak atau mencoret-coret fasilitas sekolah',
                'category' => 'sedang',
                'points' => 15,
                'status' => 'active'
            ],
            [
                'name' => 'Membawa HP ke Kelas',
                'description' => 'Siswa membawa dan menggunakan handphone saat pelajaran',
                'category' => 'sedang',
                'points' => 6,
                'status' => 'active'
            ],

            // Pelanggaran Berat (16+ poin)
            [
                'name' => 'Berkelahi di Sekolah',
                'description' => 'Siswa terlibat perkelahian fisik di lingkungan sekolah',
                'category' => 'berat',
                'points' => 25,
                'status' => 'active'
            ],
            [
                'name' => 'Membawa Senjata Tajam',
                'description' => 'Siswa membawa benda tajam atau senjata ke sekolah',
                'category' => 'berat',
                'points' => 30,
                'status' => 'active'
            ],
            [
                'name' => 'Merokok di Sekolah',
                'description' => 'Siswa merokok di lingkungan sekolah',
                'category' => 'berat',
                'points' => 20,
                'status' => 'active'
            ],
            [
                'name' => 'Bullying/Intimidasi',
                'description' => 'Siswa melakukan bullying atau intimidasi terhadap siswa lain',
                'category' => 'berat',
                'points' => 25,
                'status' => 'active'
            ],
            [
                'name' => 'Mencuri',
                'description' => 'Siswa mengambil barang milik orang lain tanpa izin',
                'category' => 'berat',
                'points' => 30,
                'status' => 'active'
            ]
        ];

        foreach ($violationTypes as $violationType) {
            ViolationType::create($violationType);
        }
    }
}