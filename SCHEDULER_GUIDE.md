# Panduan Scheduler Auto Absent

## Cara Kerja

Sistem auto absent berjalan otomatis setiap hari pada waktu yang ditentukan di pengaturan (`auto_absent_time`).

### Proses Otomatis:
1. Scheduler Laravel cek setiap menit apakah sudah waktunya auto absent
2. Jika waktu sekarang = `auto_absent_time`, maka:
   - Job `UpdateAbsentStudents` dijalankan untuk menandai siswa yang tidak hadir
   - Job `UpdateAbsentTeachers` dijalankan untuk menandai guru yang tidak hadir

### Logika Auto Absent:

**Untuk Siswa:**
- Jika tidak ada record absensi → buat record baru dengan status `absent`
- Jika ada record tapi tidak ada `clock_in` → update status jadi `absent`
- Jika ada `clock_in` tapi tidak ada `clock_out` → update status jadi `bolos`

**Untuk Guru:**
- Jika tidak ada record absensi → buat record baru dengan status `alpha`
- Jika ada record tapi tidak ada `clock_in` → update status jadi `alpha`
- Status `izin` dan `sakit` tidak akan diubah

## Menjalankan di Production

### Opsi 1: Laravel Scheduler (Recommended)
Jalankan command ini di background (gunakan supervisor atau screen):
```bash
php artisan schedule:work
```

Command ini akan terus berjalan dan mengecek schedule setiap menit.

### Opsi 2: Cron Job
Tambahkan ke crontab:
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## Testing Manual

Untuk testing tanpa menunggu waktu auto absent:

```bash
php artisan attendance:update-absent
```

Command ini akan langsung menjalankan job untuk siswa dan guru.

## Troubleshooting

### Job tidak jalan otomatis?
1. Pastikan `schedule:work` atau cron job sudah berjalan
2. Cek log: `tail -f storage/logs/laravel.log`
3. Pastikan timezone sudah benar: `Asia/Makassar`

### Status tidak berubah?
1. Cek apakah hari ini adalah hari libur (job akan skip jika libur)
2. Jalankan manual: `php artisan attendance:update-absent`
3. Cek log untuk melihat berapa record yang diupdate

### Melihat Log
```bash
# Lihat log terbaru
tail -n 100 storage/logs/laravel.log

# Filter log auto absent
tail -n 100 storage/logs/laravel.log | grep "Auto absent"
```

## Konfigurasi

Waktu auto absent bisa diubah di halaman Pengaturan Admin atau langsung di database:

```sql
UPDATE settings SET value = '15:00' WHERE key = 'auto_absent_time';
```

Format: `HH:MM` (24 jam)
