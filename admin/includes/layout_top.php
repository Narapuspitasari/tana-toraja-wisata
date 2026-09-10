<?php if (!isset($adminActive)) { $adminActive = ''; } ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>Admin | SIG Pariwisata Tana Toraja</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo @filemtime(__DIR__ . '/../../assets/css/style.css') ?: time(); ?>">
</head>
<body>
<div class="admin-layout">
  <aside class="admin-sidebar">
    <div class="logo">
      <span class="logo-badge">🏛️</span>
      SIG Toraja
    </div>

    <div class="nav-label">Menu</div>
    <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="<?php echo $adminActive=='dashboard'?'active':''; ?>">
      <span class="nav-icon">📊</span> Dashboard
    </a>
    <a href="<?php echo BASE_URL; ?>admin/wisata_list.php" class="<?php echo $adminActive=='wisata'?'active':''; ?>">
      <span class="nav-icon">📍</span> Data Wisata
    </a>
    <a href="<?php echo BASE_URL; ?>peta.php" target="_blank">
      <span class="nav-icon">🗺️</span> Lihat Peta (Publik)
    </a>

    <div class="nav-label">General</div>
    <a href="<?php echo BASE_URL; ?>admin/change_password.php" class="<?php echo $adminActive=='password'?'active':''; ?>">
      <span class="nav-icon">🔑</span> Ganti Password
    </a>

    <div class="sidebar-spacer"></div>

    <a href="<?php echo BASE_URL; ?>admin/logout.php" class="logout-link">
      <span class="nav-icon">🚪</span> Logout
    </a>
  </aside>

  <main class="admin-main">
    <div class="admin-topbar-row">
      <div class="admin-search">
        🔍 <input type="text" placeholder="Cari data wisata..." onkeydown="if(event.key==='Enter'){window.location='<?php echo BASE_URL; ?>admin/wisata_list.php?q='+encodeURIComponent(this.value);}">
      </div>
      <div class="admin-topbar-icons">
        <div class="icon-btn">✉️</div>
        <div class="icon-btn">🔔</div>
        <div class="admin-avatar">
          <div class="avatar-circle">
            <?php echo strtoupper(substr($_SESSION['admin_nama'] ?: $_SESSION['admin_username'], 0, 1)); ?>
          </div>
          <div>
            <div class="avatar-name"><?php echo htmlspecialchars($_SESSION['admin_nama'] ?: $_SESSION['admin_username']); ?></div>
            <div class="avatar-role">Administrator</div>
          </div>
        </div>
      </div>
    </div>
