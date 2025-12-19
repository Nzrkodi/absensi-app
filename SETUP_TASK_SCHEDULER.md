# Setup Task Scheduler Windows untuk Auto Absent

## Masalah
Laravel scheduler tidak berjalan otomatis karena butuh cron job. Di Windows, kita gunakan Task Scheduler.

## ✨ CARA MUDAH - Import XML (Recommended)

### 1. Buka Task Scheduler sebagai Administrator
- Tekan `Win + R`
- Ketik `taskschd.msc`
- Tekan Enter
- **PENTING:** Klik kanan pada Task Scheduler → "Run as administrator"

### 2. Import Task XML
- Klik "Import Task..." di panel kanan
- Browse ke file `Laravel_Scheduler_Task.xml` di folder project
- Klik Open
- **PENTING:** Edit path di "Actions" tab jika folder project berbeda dari `I:\Ngoding\absensi-app`
- Klik OK

### 3. Selesai!
Task sudah siap dan akan berjalan otomatis setiap menit.

## 📋 CARA MANUAL (Jika XML tidak work)

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
- Program/script: `I:\Ngoding\absensi-app\run_scheduler.bat`
- Add arguments: (kosongkan)
- Start in: `I:\Ngoding\absensi-app`
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

## ✅ Fitur Otomatis yang Sudah Tersedia

### 🏖️ Skip Hari Libur Otomatis
Sistem sudah pintar untuk **TIDAK JALAN** di hari libur:
- ✅ Hari libur nasional (dari API)
- ✅ Weekend (Sabtu & Minggu)
- ✅ Hari libur custom yang ditambahkan admin

### 📊 Logging Otomatis
- Semua aktivitas tercatat di `storage/logs/laravel.log`
- Task Scheduler juga bikin log di `scheduler_log.txt`

## Verifikasi
Setelah jam auto absent (17:43), cek halaman absensi. Siswa yang tidak clock in harus otomatis jadi "absent".

**Cek log untuk memastikan:**
```bash
# Lihat log Laravel
tail -f storage/logs/laravel.log

# Lihat log Task Scheduler
type scheduler_log.txt
```

## Catatan Penting
- ✅ Setup ini cuma perlu dilakukan **SEKALI**
- ✅ Setelah setup, sistem akan jalan **OTOMATIS SETIAP HARI**
- ✅ **TIDAK JALAN** di hari libur (otomatis skip)
- ✅ Kalau komputer mati, Task Scheduler akan catch up saat nyala lagi
- ✅ Bisa jalan meski kamu tidak buka browser/coding

## 🎯 Hasil Akhir
Setelah setup ini, kamu **TIDAK PERLU** lagi:
- ❌ Buka halaman coding setiap hari
- ❌ Manual jalankan command
- ❌ Khawatir lupa absent siswa
- ❌ Repot dengan hari libur

Sistem akan **100% OTOMATIS** handle semuanya! 🚀