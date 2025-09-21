# SIKASLINGGAR (Sistem Informasi Kas Linggar)

SIKASLINGGAR adalah sistem informasi manajemen kas yang dirancang khusus untuk mengelola keuangan di lingkungan Desa Linggar. Aplikasi ini memudahkan pencatatan dan pelacakan arus kas masuk dan keluar dengan antarmuka yang user-friendly.

## Fitur Utama

### 1. Dashboard
- Ringkasan total pemasukan dan pengeluaran
- Grafik tren keuangan
- Statistik dan analisis keuangan

### 2. Manajemen Pemasukan
- Pencatatan pemasukan dengan kategori
- Sumber pemasukan (Iuran Warga, Bantuan Pemerintah, Usaha Komunitas, dll)
- Detail informasi setiap transaksi
- Riwayat pemasukan

### 3. Manajemen Pengeluaran
- Pencatatan pengeluaran dengan kategori
- Tujuan pengeluaran (Operasional, Kegiatan, Pemeliharaan, dll)
- Detail informasi setiap transaksi
- Riwayat pengeluaran

### 4. Laporan Keuangan
- Laporan periodik (harian, bulanan, tahunan)
- Filter berdasarkan kategori dan periode
- Export laporan
- Ringkasan statistik

### 5. Manajemen Pengguna
- Multi-level user (Admin dan User)
- Manajemen akses dan hak pengguna
- Profil pengguna
- Riwayat aktivitas pengguna

## Teknologi yang Digunakan

- PHP 8.1.10
- MySQL 8.0.30
- HTML5, CSS3, JavaScript
- Bootstrap Framework
- Font Awesome Icons
- Chart.js untuk visualisasi data

## Persyaratan Sistem

- PHP >= 8.1
- MySQL >= 8.0
- Web Server (Apache/Nginx)
- Browser modern yang mendukung JavaScript

## Instalasi

1. Clone repository ini ke direktori web server Anda:
```bash
git clone [URL_REPOSITORY]
```

2. Import database dari file `db_sikaslinggar1.sql`

3. Konfigurasi koneksi database di file `config/koneksi.php`

4. Akses aplikasi melalui browser

## Struktur Login

### Admin
- Akses penuh ke semua fitur
- Manajemen pengguna
- Verifikasi transaksi
- Laporan lengkap

### User
- Lihat dashboard
- Input transaksi
- Lihat laporan
- Edit profil

## Keamanan

- Password encryption menggunakan bcrypt
- Session management
- Input validation dan sanitization
- SQL injection prevention
- XSS protection

## Panduan Penggunaan

### Pemasukan
1. Login ke sistem
2. Akses menu "Pemasukan"
3. Klik "Tambah Pemasukan"
4. Isi form dengan detail pemasukan
5. Submit dan verifikasi data

### Pengeluaran
1. Login ke sistem
2. Akses menu "Pengeluaran"
3. Klik "Tambah Pengeluaran"
4. Isi form dengan detail pengeluaran
5. Submit dan verifikasi data

### Laporan
1. Akses menu "Laporan"
2. Pilih jenis laporan
3. Set filter periode
4. Generate laporan
5. Export jika diperlukan

## Maintenance

- Backup database secara berkala
- Update security patches
- Monitor log sistem
- Periksa kinerja sistem

## Kontributor

- Ahmadhan Syafiere R. A (Developer)

## Lisensi

Hak Cipta © 2025 SIKASLINGGAR. Hak Cipta Dilindungi.