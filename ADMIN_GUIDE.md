# 🔐 Panduan Super Admin - Sistem Absensi SMA IT Persis Palu

## Default Super Admin Account

Sistem ini dilengkapi dengan **akun Super Admin default** yang tidak dapat dihapus untuk memastikan akses sistem selalu tersedia.

### 📋 Kredensial Default

```
Email    : aditya.wahyu@smaitpersis.sch.id
Password : admin123456
Role     : Super Administrator
```

### 🛡️ Fitur Keamanan

1. **Protected Account**: Akun ini tidak dapat dihapus melalui interface web
2. **Always Available**: Selalu tersedia meskipun semua user lain terhapus
3. **Full Access**: Memiliki akses penuh ke semua fitur sistem
4. **Reset Capability**: Password dapat direset menggunakan command artisan

### 🔧 Command Artisan

#### Membuat/Reset Akun Default Admin
```bash
# Membuat akun default admin (jika belum ada)
php artisan admin:create-default

# Reset password ke default
php artisan admin:create-default --reset

# Force create (overwrite existing)
php artisan admin:create-default --force
```

#### Menjalankan Seeder
```bash
# Jalankan seeder default admin
php artisan db:seed --class=DefaultAdminSeeder

# Jalankan semua seeder (termasuk default admin)
php artisan db:seed
```

### ⚠️ Peringatan Keamanan

1. **SEGERA UBAH PASSWORD** setelah login pertama
2. Jangan bagikan kredensial ini ke orang yang tidak berwenang
3. Gunakan password yang kuat dan unik
4. Aktifkan 2FA jika tersedia di masa depan

### 🚨 Skenario Darurat

Jika semua akun admin terhapus atau tidak bisa diakses:

1. Akses server/hosting
2. Jalankan command: `php artisan admin:create-default`
3. Login menggunakan kredensial default
4. Buat akun admin baru
5. Ubah password akun default

### 📞 Kontak Darurat

Jika mengalami masalah akses sistem:
- Developer: Aditya Wahyu
- Email: aditya.wahyu@smaitpersis.sch.id
- Sekolah: SMA IT Persis Palu

---

**Catatan**: Dokumentasi ini hanya untuk Super Administrator. Jaga kerahasiaan informasi ini.