# Sistem Informasi Pemetaan Pariwisata Kabupaten Tana Toraja Berbasis Website

Stack: **PHP native (mysqli) + MySQL + Leaflet JS**, tanpa framework — sesuai proposal.
Panduan di bawah ini khusus untuk **Laragon** (bukan XAMPP).

## 1. Letakkan folder proyek

Salin folder `tana-toraja-wisata` ke:
```
C:\laragon\www\tana-toraja-wisata
```

## 2. Jalankan Laragon

Buka Laragon → klik **Start All** (Apache + MySQL akan aktif).

## 3. Buat & import database

Ada 2 cara, pilih salah satu:

**A. Lewat HeidiSQL (bawaan Laragon)**
1. Klik menu Laragon → **Database** (otomatis membuka HeidiSQL, sudah login sebagai `root` tanpa password).
2. Klik kanan pada koneksi → **Create new** → **Database**, beri nama `tana_toraja_wisata`.
3. Klik kanan database tersebut → **Load SQL file** → pilih `database/tana_toraja_wisata.sql` → Execute (F9).

**B. Lewat phpMyAdmin (jika sudah terpasang di Laragon)**
1. Buka `http://localhost/phpmyadmin`.
2. Buat database baru `tana_toraja_wisata`.
3. Tab **Import** → pilih file `database/tana_toraja_wisata.sql` → Go.

> File SQL sudah membuat database-nya sendiri (`CREATE DATABASE IF NOT EXISTS`), jadi Anda juga bisa langsung import tanpa membuat database manual lebih dulu — asal user `root` punya izin membuat database (default Laragon: ya).

## 4. Akses website

Buka browser ke salah satu:
- `http://localhost/tana-toraja-wisata/index.php`
- atau lewat **Pretty URL / Auto Virtual Host** Laragon: `http://tana-toraja-wisata.test/`
  (biasanya otomatis aktif karena Laragon membuat virtual host sesuai nama folder).

## 5. Login admin

- URL: `admin/login.php` (contoh: `http://tana-toraja-wisata.test/admin/login.php`)
- Username: `admin`
- Password: `admin123`

⚠️ **Segera ganti password default** setelah login pertama kali (lewat query update ke tabel `admin` menggunakan `password_hash()`, atau tambahkan fitur ganti password jika diperlukan untuk pengembangan lanjutan).

## 6. Struktur folder

```
tana-toraja-wisata/
├─ admin/                  # Panel admin (login, dashboard, CRUD wisata)
│  └─ includes/            # auth guard + layout admin
├─ assets/css/style.css    # Styling utama (tema Toraja: merah-hitam-emas)
├─ config/
│  ├─ db.php               # Koneksi mysqli (host/user/pass Laragon default)
│  └─ config.php           # BASE_URL otomatis + include db.php
├─ database/
│  └─ tana_toraja_wisata.sql   # Struktur tabel + data contoh
├─ includes/                # header/footer halaman publik
├─ uploads/wisata/          # Folder upload foto wisata
├─ index.php                # Beranda
├─ peta.php                 # Peta interaktif Leaflet (fitur utama)
├─ daftar_wisata.php        # Daftar + filter kategori + pencarian
└─ detail.php                # Detail wisata + mini map + tombol rute Google Maps
```

## 7. Jika port MySQL Laragon Anda bukan 3306

Buka `config/db.php`, ubah:
```php
define('DB_HOST', 'localhost;port=3307'); // sesuaikan port Anda
```
Cek port MySQL di Laragon: klik kanan ikon Laragon → **MySQL** → **port**.

## 8. Menambahkan koordinat wisata baru

Di form tambah/edit wisata (admin), isi Latitude & Longitude. Cara mudah mendapatkan koordinat:
1. Buka Google Maps, cari lokasi wisata.
2. Klik kanan pada titik lokasi → koordinat akan muncul di bagian atas menu (format: `lat, long`).
3. Salin dan tempel ke form (pisahkan latitude dan longitude ke kolom masing-masing).

## Catatan pengembangan lanjutan (opsional, sesuai Bab III proposal)

- Tabel `galeri_wisata` sudah dibuat di database untuk pengembangan galeri foto multi-gambar per wisata (belum ada UI-nya di versi ini — bisa ditambahkan sebagai pengembangan lanjutan skripsi, misalnya di BAB IV/V sebagai saran).
- Struktur kode sengaja dibuat sederhana (PHP native, tanpa framework) agar sesuai dengan Bab III.F Teknologi yang digunakan dalam proposal (HTML, CSS, JavaScript, PHP, MySQL, XAMPP/Laragon, Leaflet, VS Code).
