<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Daftar Wisata';
$active = 'daftar';

$kategoriFilter = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';

// Ambil data lewat class Wisata (bukan query manual lagi)
$wisataResult = $wisataModel->getAll([
    'kategori' => $kategoriFilter,
    'keyword' => $keyword,
]);

$kategoriList = $kategoriModel->getAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-banner">
  <div class="section">
    <div class="ed-eyebrow"><span class="num">02</span> Destinations</div>
    <h1 class="page-banner-title">Daftar Wisata Tana Toraja</h1>
    <p>Jelajahi seluruh destinasi wisata yang terdaftar dalam sistem.</p>
  </div>
</div>

<div class="section" style="padding-top:30px;">
  <form method="get" class="filter-bar">
    <input type="text" name="q" placeholder="Cari nama wisata..." value="<?php echo htmlspecialchars($keyword); ?>">
    <select name="kategori">
      <option value="">Semua Kategori</option>
      <?php foreach ($kategoriList as $k): ?>
        <option value="<?php echo htmlspecialchars($k['nama_kategori']); ?>"
          <?php echo $kategoriFilter === $k['nama_kategori'] ? 'selected' : ''; ?>>
          <?php echo htmlspecialchars($k['nama_kategori']); ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-sm" type="submit">Filter</button>
  </form>

  <div class="wisata-grid">
    <?php if (!empty($wisataResult)): ?>
      <?php foreach ($wisataResult as $row): ?>
        <a class="wisata-card reveal" href="<?php echo BASE_URL; ?>detail.php?id=<?php echo (int)$row['id_wisata']; ?>">
          <div class="thumb" style="<?php echo $row['foto'] ? 'background-image:url(' . UPLOAD_URL . htmlspecialchars($row['foto']) . ')' : ''; ?>">
            <?php if (!$row['foto']): ?>Belum ada foto<?php endif; ?>
          </div>
          <div class="body">
            <span class="badge"><?php echo htmlspecialchars($row['kategori']); ?></span>
            <h4><?php echo htmlspecialchars($row['nama_wisata']); ?></h4>
            <div class="alamat">📍 <?php echo htmlspecialchars($row['alamat']); ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    <?php else: ?>
      <p>Tidak ada wisata yang cocok dengan pencarian.</p>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
