# Fitur Absensi Siswa

## Fitur yang Tersedia

### 1. Halaman Absensi Siswa
- **URL**: `/admin/attendance`
- **Fitur**:
  - Menampilkan semua data siswa aktif
  - Filter berdasarkan tanggal, nama siswa, dan kelas
  - Tombol Clock In/Clock Out untuk setiap siswa
  - Tombol Note untuk menambahkan status sakit/izin

### 2. Tombol Clock In/Clock Out
- **Clock In**: 
  - Muncul ketika siswa belum melakukan clock in (apapun statusnya)
  - Otomatis menentukan status "present" atau "late" berdasarkan waktu
  - Bisa mengubah status "absent/sick/permission" menjadi "present/late" jika siswa datang
  - Setelah clock in, tombol berubah menjadi "Clock Out"
- **Clock Out**:
  - Muncul setelah siswa melakukan clock in (apapun statusnya)
  - Mencatat waktu pulang siswa
  - Setelah clock out, tombol hilang

### 3. Tombol Note
- Untuk menandai siswa sakit atau izin
- Form modal dengan pilihan:
  - Status: Sakit atau Izin
  - Keterangan: Deskripsi alasan

### 4. Job Scheduler Otomatis
- **Waktu**: Setiap hari jam 15:00 (3 sore)
- **Fungsi**: Mengupdate status siswa yang tidak clock in/out menjadi "absent"
- **Command manual**: `php artisan attendance:update-absent`

## Status Absensi
- **Present** (Hadir): Siswa clock in tepat waktu
- **Late** (Terlambat): Siswa clock in setelah jam 07:15
- **Absent** (Tidak Hadir): Siswa tidak clock in/out (otomatis jam 15:00)
- **Sick** (Sakit): Ditandai manual melalui tombol Note
- **Permission** (Izin): Ditandai manual melalui tombol Note

## Cara Setup Scheduler (Production)
Untuk menjalankan scheduler otomatis di server production, tambahkan cron job:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Testing
1. Akses `/admin/attendance`
2. Pilih tanggal hari ini
3. Coba clock in untuk beberapa siswa
4. Coba clock out untuk siswa yang sudah clock in
5. Coba tambahkan note untuk siswa sakit/izin
6. Test command: `php artisan attendance:update-absent`

## Database
- Tabel `attendances` menyimpan semua data absensi
- Relasi dengan tabel `students` dan `users`
- Unique constraint pada `student_id` dan `date`