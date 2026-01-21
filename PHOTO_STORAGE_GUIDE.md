# Panduan Penyimpanan Foto Absen

## Overview
Sistem penyimpanan foto absen telah diupgrade untuk mengorganisir foto berdasarkan semester dan otomatis menghapus foto lama untuk menghemat storage.

## Struktur Folder
```
storage/app/public/attendance/photos/
├── 2024-1/    # Semester 1 tahun 2024 (Jan-Jun)
├── 2024-2/    # Semester 2 tahun 2024 (Jul-Des)
├── 2025-1/    # Semester 1 tahun 2025 (Jan-Jun)
├── 2025-2/    # Semester 2 tahun 2025 (Jul-Des)
└── 2026-1/    # Semester 1 tahun 2026 (Jan-Jun) - Current
```

## Fitur Utama

### 1. Penyimpanan Otomatis per Semester
- Foto clock in/out otomatis disimpan dalam folder semester yang sesuai
- Format folder: `YYYY-S` (contoh: `2026-1` untuk semester 1 tahun 2026)
- Semester 1: Januari - Juni (bulan 1-6)
- Semester 2: Juli - Desember (bulan 7-12)

### 2. Cleanup Otomatis
- Cron job berjalan setiap bulan tanggal 1 jam 03:00
- Menghapus folder foto yang lebih lama dari 6 bulan
- Dapat dikonfigurasi untuk periode yang berbeda

### 3. Management Interface
- Admin dapat melihat statistik storage di menu admin
- Dapat melakukan cleanup manual jika diperlukan
- Monitoring ukuran file dan jumlah foto per semester

## Command Line Tools

### Melihat Statistik Storage
```bash
php artisan attendance:storage-stats
```

### Cleanup Manual
```bash
# Hapus foto lebih lama dari 6 bulan
php artisan attendance:cleanup-photos --months=6

# Hapus foto lebih lama dari 12 bulan
php artisan attendance:cleanup-photos --months=12
```

## Cron Job Configuration

Cron job sudah dikonfigurasi di `app/Console/Kernel.php`:

```php
// Cleanup old attendance photos every month (first day at 3 AM)
$schedule->command('attendance:cleanup-photos --months=6')
         ->monthlyOn(1, '03:00')
         ->timezone('Asia/Makassar');
```

## Service Class

### AttendancePhotoService

Kelas service yang menangani:
- `getSemesterFolder()` - Menentukan folder semester berdasarkan tanggal
- `storePhoto()` - Menyimpan foto dengan struktur folder yang benar
- `getOldSemesterFolders()` - Mendapatkan daftar folder lama
- `deleteOldFolders()` - Menghapus folder lama
- `getStorageStats()` - Mendapatkan statistik storage

## Implementasi di Controller

### Student AttendanceController
```php
// Clock in photo storage
$photoPath = AttendancePhotoService::storePhoto(
    $request->file('photo'),
    'clock_in',
    $student->id,
    $date
);

// Clock out photo storage  
$photoPath = AttendancePhotoService::storePhoto(
    $request->file('photo'),
    'clock_out',
    $student->id,
    $date
);
```

## Keamanan dan Backup

### Rekomendasi:
1. **Backup Reguler**: Backup folder `storage/app/public/attendance/photos/` secara berkala
2. **Monitoring**: Pantau ukuran storage secara rutin menggunakan command `attendance:storage-stats`
3. **Konfigurasi**: Sesuaikan periode cleanup sesuai kebutuhan sekolah
4. **Testing**: Test cleanup di environment development sebelum production

## Troubleshooting

### Folder Tidak Terbuat Otomatis
- Pastikan permission folder storage sudah benar (755)
- Cek log Laravel di `storage/logs/laravel.log`

### Cleanup Tidak Berjalan
- Cek cron job server sudah aktif
- Verifikasi timezone setting di aplikasi
- Cek log scheduler: `php artisan schedule:list`

### Storage Penuh
- Jalankan cleanup manual dengan periode yang lebih pendek
- Backup dan hapus folder lama secara manual jika diperlukan

## Monitoring

### Melihat Status Cron Job
```bash
php artisan schedule:list
```

### Test Cron Job
```bash
php artisan schedule:run
```

### Log Monitoring
- Cleanup activity tercatat di `storage/logs/laravel.log`
- Setiap operasi penyimpanan dan penghapusan foto di-log untuk audit trail