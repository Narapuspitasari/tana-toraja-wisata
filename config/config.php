<?php
/**
 * Konfigurasi umum aplikasi.
 * BASE_URL otomatis mendeteksi folder proyek, jadi biasanya tidak perlu diubah,
 * baik diakses lewat http://localhost/tana-toraja-wisata/ maupun via virtual host
 * Laragon seperti http://tana-toraja-wisata.test/
 */

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
// Jika file dipanggil dari dalam folder admin/ atau ajax/ (atau subfolder-nya), naik satu/dua level
$scriptDir = preg_replace('#/(admin|ajax)(/.*)?$#', '', $scriptDir);

$base = rtrim($scriptDir, '/') . '/';
if ($base === '/') { $base = '/'; }

define('BASE_URL', $base);
define('UPLOAD_DIR', __DIR__ . '/../uploads/wisata/');
define('UPLOAD_URL', BASE_URL . 'uploads/wisata/');
define('GALERI_UPLOAD_DIR', __DIR__ . '/../uploads/galeri/');
define('GALERI_UPLOAD_URL', BASE_URL . 'uploads/galeri/');

require_once __DIR__ . '/db.php';

// Autoload seluruh class model (OOP) — lihat Class Diagram pada proposal (Gambar 3.4)
require_once __DIR__ . '/../includes/models/Wisata.php';
require_once __DIR__ . '/../includes/models/KategoriWisata.php';
require_once __DIR__ . '/../includes/models/GaleriWisata.php';
require_once __DIR__ . '/../includes/models/Admin.php';

// Instance global tiap class model, siap dipakai langsung di semua halaman
$wisataModel = new Wisata($koneksi);
$kategoriModel = new KategoriWisata($koneksi);
$galeriModel = new GaleriWisata($koneksi);
$adminModel = new Admin($koneksi);
