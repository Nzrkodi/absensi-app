# Fitur Absensi Siswa

## Fitur yang Tersedia

### 1. Halaman Absensi Siswa
- **URL**: `/admin/attendance`
- **Fitur**:
  - Menampilkan semua data siswa aktif
  - Filter berdasarkan tanggal, nama siswa, dan kelas
  - Tombol Clock In/Clock Out untuk setiap siswa
  - Tombol Note untuk menambahkan status sakit/izin

### 2. Pengaturan Sistem (BARU!)
- **URL**: `/admin/settings`
- **Fitur**:
  - Setting waktu mulai sekolah (fleksibel)
  - Setting toleransi keterlambatan (dalam menit)
  - Setting waktu auto absent
  - Setting nama sekolah
  - Setting izin clock in sebelum jam mulai

### 3. Manual Time Override (FITUR TERBARU! 🆕)
- **Fitur Clock In Manual**: Memungkinkan admin/guru untuk mencatat waktu kedatangan siswa yang sebenarnya
- **Kapan Digunakan**: 
  - Ketika guru terlambat datang ke sekolah
  - Perlu mencatat waktu kedatangan siswa yang akurat
  - Siswa datang di waktu yang sudah melewati batas keterlambatan
- **Cara Kerja**:
  - Klik tombol "Clock In" → Modal akan terbuka
  - Pilih "Waktu Sekarang" atau "Waktu Manual"
  - Jika pilih "Waktu Manual", input waktu kedatangan yang sebenarnya
  - Sistem otomatis menentukan status (Present/Late) berdasarkan waktu yang diinput
  - Tambahkan keterangan jika diperlukan
- **Validasi**:
  - Waktu tidak boleh di masa depan
  - Waktu harus pada hari yang sama
  - Peringatan otomatis jika waktu menghasilkan status "Terlambat"

### 4. Tombol Clock In/Clock Out
- **Clock In**: 
  - Muncul ketika siswa belum melakukan clock in (apapun statusnya)
  - **BARU**: Membuka modal untuk pilihan waktu manual atau waktu sekarang
  - Otomatis menentukan status "present" atau "late" berdasarkan waktu
  - Bisa mengubah status "absent/sick/permission" menjadi "present/late" jika siswa datang
  - Setelah clock in, tombol berubah menjadi "Clock Out"
- **Clock Out**:
  - Muncul setelah siswa melakukan clock in (apapun statusnya)
  - Mencatat waktu pulang siswa
  - Setelah clock out, tombol hilang

### 5. Tombol Note
- Untuk menandai siswa sakit atau izin
- Form modal dengan pilihan:
  - Status: Sakit atau Izin
  - Keterangan: Deskripsi alasan
- **Otomatis disabled** setelah siswa clock out (absensi sudah lengkap)

### 6. Job Scheduler Otomatis
- **Waktu**: Sesuai setting (default jam 15:00)
- **Fungsi**: Mengupdate status siswa yang tidak clock in/out menjadi "absent"
- **Command manual**: `php artisan attendance:update-absent`
- **Fleksibel**: Waktu bisa diubah melalui menu Settings
- **Smart Holiday Detection**: Otomatis skip di hari libur

### 7. Manajemen Hari Libur (BARU!)
- **URL**: `/admin/holidays`
- **Fitur**:
  - Tambah/edit/hapus hari libur
  - Jenis libur: Nasional, Sekolah, Weekend
  - Auto-generate weekend untuk setahun
  - Status aktif/nonaktif
  - Cek status hari ini real-time

### 8. Laporan Absensi (BARU!)
- **URL**: `/admin/reports`
- **Fitur**:
  - Laporan absensi per periode
  - Filter berdasarkan kelas dan siswa
  - Statistik kehadiran lengkap
  - Detail laporan per siswa
  - Export ke CSV
  - Progress chart kehadiran

## Status Absensi
- **Present** (Hadir): Siswa clock in tepat waktu
- **Late** (Terlambat): Siswa clock in setelah jam 07:15
- **Absent** (Tidak Hadir): Siswa tidak clock in/out (otomatis jam 15:00)
- **Sick** (Sakit): Ditandai manual melalui tombol Note
- **Permission** (Izin): Ditandai manual melalui tombol Note

## Skenario Penggunaan Manual Time Override

### Skenario 1: Guru Terlambat Datang
1. Guru datang jam 08:30, tapi ada siswa yang sudah datang jam 07:45
2. Klik tombol "Clock In" untuk siswa tersebut
3. Pilih "Waktu Manual" dan input "07:45"
4. Sistem akan memberikan status "Present" karena masih dalam toleransi
5. Tambahkan keterangan: "Siswa datang tepat waktu, guru yang terlambat"

### Skenario 2: Siswa Datang Sangat Terlambat
1. Siswa datang jam 09:00 (sudah melewati batas keterlambatan)
2. Klik tombol "Clock In" untuk siswa tersebut
3. Pilih "Waktu Manual" dan input "09:00"
4. Sistem akan menampilkan peringatan bahwa status akan menjadi "Late"
5. Tambahkan keterangan: "Terlambat karena macet"
6. Data tercatat sesuai waktu kedatangan yang sebenarnya

## Cara Setup Scheduler (Production)
Untuk menjalankan scheduler otomatis di server production, tambahkan cron job:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Testing
1. Akses `/admin/attendance`
2. Pilih tanggal hari ini
3. Coba clock in untuk beberapa siswa dengan waktu manual
4. Coba clock out untuk siswa yang sudah clock in
5. Coba tambahkan note untuk siswa sakit/izin
6. Test command: `php artisan attendance:update-absent`

## Database
- Tabel `attendances` menyimpan semua data absensi
- Relasi dengan tabel `students` dan `users`
- Unique constraint pada `student_id` dan `date`
- **BARU**: Field `notes` menyimpan informasi clock in manual dan keterangan tambahan