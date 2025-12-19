# Setup Task Scheduler Windows untuk Auto Absent

## Masalah
Laravel scheduler tidak berjalan otomatis karena butuh cron job. Di Windows, kita gunakan Task Scheduler.

## Langkah Setup (Sekali saja)

### 1. Buka Task Scheduler
- Tekan `Win + R`
- Ketik `taskschd.msc`
- Tekan Enter

### 2. Create Basic Task
- Klik "Create Basic Task..." di panel kanan
- Name: `Laravel Scheduler - Absensi`
- Description: `Menjalankan Laravel scheduler untuk auto absent siswa`
- Klik Next

### 3. Set Trigger
- Pilih "Daily"
- Klik Next
- Start: Pilih tanggal hari ini
- Recur every: `1` days
- Klik Next

### 4. Set Action
- Pilih "Start a program"
- Klik Next
- Program/script: `php`
- Add arguments: `artisan schedule:run`
- Start in: `I:\Ngoding\absensi-app` (sesuaikan dengan path project kamu)
- Klik Next

### 5. Finish
- Review semua setting
- Centang "Open the Properties dialog..."
- Klik Finish

### 6. Advanced Settings
Di Properties dialog yang terbuka:
- Tab "General": Centang "Run whether user is logged on or not"
- Tab "Triggers": Edit trigger, centang "Repeat task every" → pilih "1 minute"
- Tab "Settings": Centang "Run task as soon as possible after a scheduled start is missed"
- Klik OK

## Testing
Setelah setup, tunggu beberapa menit lalu cek:
```bash
# Lihat log Laravel
tail -f storage/logs/laravel.log

# Atau cek manual
php artisan schedule:list
```

## Troubleshooting

### Jika Task Scheduler Error:
1. Pastikan PHP ada di PATH Windows
2. Atau gunakan full path: `C:\path\to\php.exe`

### Jika Masih Tidak Jalan:
Buat file batch `run_scheduler.bat`:
```batch
@echo off
cd /d "I:\Ngoding\absensi-app"
php artisan schedule:run
```

Lalu di Task Scheduler, ganti:
- Program/script: `I:\Ngoding\absensi-app\run_scheduler.bat`
- Arguments: (kosongkan)

## Verifikasi
Setelah jam auto absent (17:43), cek halaman absensi. Siswa yang tidak clock in harus otomatis jadi "absent".

## Catatan Penting
- Setup ini cuma perlu dilakukan SEKALI
- Setelah setup, sistem akan jalan otomatis setiap hari
- Kalau komputer mati, Task Scheduler akan catch up saat nyala lagi