# Panduan Menjalankan Auto Absent Scheduler

## Masalah
Laravel scheduler membutuhkan cron job untuk berjalan otomatis. Di Windows, ini perlu setup khusus.

## Solusi untuk Development/Testing

### 1. Jalankan Manual (untuk testing)
```bash
# Test job auto absent secara manual
php artisan attendance:update-absent

# Jalankan queue worker untuk memproses job
php artisan queue:work --once
```

### 2. Jalankan Scheduler Manual
```bash
# Jalankan semua scheduled task yang due
php artisan schedule:run

# Lihat daftar scheduled task
php artisan schedule:list
```

### 3. Untuk Production (Windows)

#### Menggunakan Task Scheduler Windows:
1. Buka Task Scheduler Windows
2. Create Basic Task
3. Set trigger: Daily pada waktu yang diinginkan
4. Set action: Start a program
5. Program: `php`
6. Arguments: `artisan schedule:run`
7. Start in: `C:\path\to\your\absensi-app`

#### Atau menggunakan batch file:
Buat file `run_scheduler.bat`:
```batch
@echo off
cd /d "C:\path\to\your\absensi-app"
php artisan schedule:run
```

Lalu set Task Scheduler untuk menjalankan batch file ini setiap menit.

## Cara Kerja Auto Absent

1. **Waktu diatur di Settings** - Admin set waktu auto absent (misal 17:17)
2. **Scheduler berjalan** - Laravel scheduler cek setiap hari pada waktu tersebut
3. **Job dijalankan** - UpdateAbsentStudents job akan:
   - Cek semua siswa aktif
   - Siswa yang tidak clock in → status "absent"
   - Siswa yang clock in tapi tidak clock out → status "bolos"
   - Skip jika hari libur

## Testing
Untuk test apakah sistem bekerja:
```bash
# Test manual
php test_auto_absent.php

# Atau jalankan job langsung
php artisan attendance:update-absent
php artisan queue:work --once
```

## Log
Cek log di `storage/logs/laravel.log` untuk melihat apakah scheduler berjalan.