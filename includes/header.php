<?php
// $active dipakai untuk highlight menu aktif (diset di halaman pemanggil sebelum require header.php)
if (!isset($active)) { $active = ''; }
// $hasHero: set true di halaman pemanggil (index.php) agar navbar tampil transparan di atas hero
if (!isset($hasHero)) { $hasHero = false; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>SIG Pariwisata Tana Toraja</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Manrope:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo @filemtime(__DIR__ . '/../assets/css/style.css') ?: time(); ?>">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/leaflet/leaflet.css" />
<script src="<?php echo BASE_URL; ?>assets/leaflet/leaflet.js"></script>
</head>
<body<?php echo $hasHero ? ' class="has-hero"' : ''; ?>>

<header class="navbar">
  <a href="<?php echo BASE_URL; ?>index.php" class="brand">
    <span class="logo-mark">
      <svg viewBox="0 0 48 48" width="34" height="34" xmlns="http://www.w3.org/2000/svg">
        <path d="M24 6c-2 5-9 9-16 10 6 1 12 1 16-2 4 3 10 3 16 2-7-1-14-5-16-10z" fill="#b1602f"/>
        <path d="M10 18c5 2 9 2 14 0 5 2 9 2 14 0-1 4-2 9-2 14H12c0-5-1-10-2-14z" fill="currentColor"/>
        <rect x="16" y="30" width="16" height="10" rx="1" fill="#b1602f"/>
      </svg>
    </span>
    <span>
      SIG PARIWISATA
      <span class="small">Kabupaten Tana Toraja</span>
    </span>
  </a>

  <nav class="nav-links">
    <a href="<?php echo BASE_URL; ?>index.php" class="<?php echo $active=='home'?'active':''; ?>">Beranda</a>
    <a href="<?php echo BASE_URL; ?>peta.php" class="<?php echo $active=='peta'?'active':''; ?>">Peta</a>
    <a href="<?php echo BASE_URL; ?>daftar_wisata.php" class="<?php echo $active=='daftar'?'active':''; ?>">Destinasi</a>
    <a href="<?php echo BASE_URL; ?>wisata_terdekat.php" class="<?php echo $active=='terdekat'?'active':''; ?>">Terdekat</a>
    <a href="<?php echo BASE_URL; ?>index.php#tentang">Tentang</a>
    <a href="<?php echo BASE_URL; ?>index.php#kontak">Kontak</a>
    <a href="<?php echo BASE_URL; ?>admin/login.php" class="nav-login">Login Admin</a>
  </nav>

  <button class="nav-toggle" aria-label="Buka menu">
    <span></span><span></span><span></span>
  </button>
</header>

<div class="mobile-nav">
  <button class="mobile-nav-close" aria-label="Tutup menu">&times;</button>
  <a href="<?php echo BASE_URL; ?>index.php" class="<?php echo $active=='home'?'active':''; ?>">Beranda</a>
  <a href="<?php echo BASE_URL; ?>peta.php" class="<?php echo $active=='peta'?'active':''; ?>">Peta</a>
  <a href="<?php echo BASE_URL; ?>daftar_wisata.php" class="<?php echo $active=='daftar'?'active':''; ?>">Destinasi</a>
  <a href="<?php echo BASE_URL; ?>wisata_terdekat.php" class="<?php echo $active=='terdekat'?'active':''; ?>">Terdekat</a>
  <a href="<?php echo BASE_URL; ?>index.php#tentang">Tentang</a>
  <a href="<?php echo BASE_URL; ?>index.php#kontak">Kontak</a>
  <a href="<?php echo BASE_URL; ?>admin/login.php">Login Admin</a>
</div>
