<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Galon App

Aplikasi manajemen galon, pelanggan, transaksi, hutang, karyawan, absensi, penggajian, biaya operasional, dan pinjaman karyawan.

## Fitur Utama

- **Manajemen Pelanggan**: CRUD pelanggan, transaksi, hutang.
- **Manajemen Karyawan**: CRUD karyawan, absensi harian, laporan penggajian, pinjaman karyawan.
- **Absensi Karyawan**: Catat jam masuk/pulang, laporan kehadiran.
- **Laporan Penggajian**:
  - Bagi hasil: 40% untuk karyawan (dibagi rata yang absen), 60% pemilik.
  - Infak: Rp 1.000 per galon masuk.
  - Biaya operasional otomatis mengurangi pemasukan.
  - Pinjaman karyawan otomatis memotong gaji.
  - Pembayaran hutang pelanggan menambah pemasukan.
  - Rincian biaya operasional, pinjaman, dan pembayaran hutang tampil di laporan.
- **Biaya Operasional**: CRUD biaya, terintegrasi laporan.
- **Pinjaman Karyawan**: CRUD pinjaman, otomatis potong gaji.
- **Pembayaran Hutang**: Log pembayaran hutang, validasi tidak boleh melebihi sisa hutang.
- **Menu Navigasi Terstruktur**: Semua fitur karyawan dalam satu menu dropdown.

## Instalasi & Setup

1. **Clone repo & install dependency**
   ```bash
   git clone <repo-url>
   cd galon-app
   composer install
   npm install && npm run build # jika pakai Vite
   cp .env.example .env
   php artisan key:generate
   ```
2. **Setup database**
   - Buat database baru (MySQL/PostgreSQL).
   - Import `database/database.sql` atau jalankan semua migration:
     ```bash
     php artisan migrate
     ```
3. **Jalankan aplikasi**
   ```bash
   php artisan serve
   ```
4. **Login**
   - Register user pertama kali via seeder atau manual.

## Struktur Menu Utama

- **Pelanggan**: Data pelanggan, transaksi, hutang.
- **Karyawan** (dropdown):
  - Data Karyawan
  - Absen Karyawan
  - Laporan Penggajian
  - Biaya Operasional
  - Pinjaman Karyawan
- **Laporan**: Laporan transaksi, penggajian, dsb.

## Catatan Penting
- Semua proses absensi, penggajian, pinjaman, biaya operasional, dan pembayaran hutang **tanpa model Eloquent** (langsung DB facade).
- Validasi pembayaran hutang: tidak boleh melebihi sisa hutang.
- Gaji karyawan otomatis dipotong pinjaman, dan tidak bisa minus.
- Semua laporan bisa difilter range tanggal/bulan.

## Kontribusi
Pull request dan issue sangat diterima!

---

**Aplikasi ini cocok untuk usaha galon, koperasi, atau bisnis serupa yang butuh manajemen transaksi, hutang, dan SDM secara terintegrasi.**

## Teknologi

- Laravel 10.x
- PHP 8.1+
- MySQL
- Bootstrap 5
- JavaScript/jQuery

## Instalasi

1. Clone repository
2. Insert user admin
```
INSERT INTO users (name,email, password,created_at,updated_at,email_verified_at) 
VALUES ('admin','admin@aero.com', SHA2('aerotripandawa2024', 256),now(),now(),now());
```     