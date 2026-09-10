<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Dashboard';
$adminActive = 'dashboard';

$totalWisata = $wisataModel->countAll();
$totalKategori = $kategoriModel->countAll();
$totalBudaya = count($wisataModel->getAll(['kategori' => 'Wisata Budaya']));
$totalAlam = count($wisataModel->getAll(['kategori' => 'Wisata Alam']));

$recent = $wisataModel->getAll(['limit' => 5, 'order_by_terbaru' => true]);

// --- Data untuk donut chart distribusi kategori (CSS conic-gradient) ---
$cats = $wisataModel->countByKategori();
$palette = ['#16281d', '#223a29', '#b1602f', '#8a8578', '#c9c2ac', '#8f4a22'];

$gradientParts = [];
$cumulative = 0;
foreach ($cats as $i => $c) {
    $pct = $totalWisata > 0 ? ($c['jumlah'] / $totalWisata * 100) : 0;
    $start = $cumulative;
    $cumulative += $pct;
    $color = $palette[$i % count($palette)];
    $gradientParts[] = "$color {$start}% {$cumulative}%";
}
$gradientCss = !empty($gradientParts) ? implode(', ', $gradientParts) : '#e5e2d8 0% 100%';

require_once __DIR__ . '/includes/layout_top.php';
?>

<div class="admin-topbar">
  <div>
    <h2>Dashboard</h2>
    <p class="sub">Kelola data wisata dengan mudah, selamat datang <?php echo htmlspecialchars($_SESSION['admin_nama'] ?: $_SESSION['admin_username']); ?> 👋</p>
  </div>
  <div class="admin-topbar-actions">
    <a href="<?php echo BASE_URL; ?>admin/wisata_form.php" class="btn btn-sm">+ Tambah Wisata</a>
    <a href="<?php echo BASE_URL; ?>peta.php" target="_blank" class="btn btn-sm btn-outline" style="color:var(--toraja-green-dark);border-color:var(--toraja-green-dark);">Lihat Peta</a>
  </div>
</div>

<div class="stat-cards">
  <div class="stat-card highlight">
    <div class="stat-top">
      <span class="label">Total Wisata</span>
      <span class="arrow-badge">↗</span>
    </div>
    <div class="num"><?php echo $totalWisata; ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-top">
      <span class="label">Total Kategori</span>
      <span class="arrow-badge">↗</span>
    </div>
    <div class="num"><?php echo $totalKategori; ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-top">
      <span class="label">Wisata Budaya</span>
      <span class="arrow-badge">↗</span>
    </div>
    <div class="num"><?php echo $totalBudaya; ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-top">
      <span class="label">Wisata Alam</span>
      <span class="arrow-badge">↗</span>
    </div>
    <div class="num"><?php echo $totalAlam; ?></div>
  </div>
</div>

<div class="panel-row">
  <div class="card-panel">
    <h3>Data Wisata Terbaru</h3>
    <table class="data-table">
      <thead>
        <tr><th>Nama Wisata</th><th>Kategori</th><th>Ditambahkan</th></tr>
      </thead>
      <tbody>
        <?php if (!empty($recent)): ?>
          <?php foreach ($recent as $r): ?>
            <tr>
              <td><?php echo htmlspecialchars($r['nama_wisata']); ?></td>
              <td><?php echo htmlspecialchars($r['kategori']); ?></td>
              <td><?php echo date('d/m/Y H:i', strtotime($r['created_at'])); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="3">Belum ada data.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card-panel">
    <h3>Distribusi Kategori</h3>
    <div class="donut-wrap">
      <div class="donut" style="background: conic-gradient(<?php echo $gradientCss; ?>);">
        <div class="donut-center">
          <div class="pct"><?php echo $totalWisata; ?></div>
          <div class="pct-label">Total Destinasi</div>
        </div>
      </div>
      <div class="donut-legend">
        <?php foreach ($cats as $i => $c): ?>
          <div class="legend-item">
            <span class="dot-legend" style="background:<?php echo $palette[$i % count($palette)]; ?>;"></span>
            <?php echo htmlspecialchars($c['kategori']); ?> (<?php echo $c['jumlah']; ?>)
          </div>
        <?php endforeach; ?>
        <?php if (empty($cats)): ?>
          <div class="legend-item" style="color:#999;">Belum ada data wisata.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/layout_bottom.php'; ?>
