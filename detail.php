<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$wisata = $wisataModel->getByIdAsObject($id);

if (!$wisata) {
    header('Location: ' . BASE_URL . 'daftar_wisata.php');
    exit;
}

$pageTitle = $wisata->nama_wisata;
$active = 'daftar';

$fasilitas = $wisata->fasilitas ? array_filter(array_map('trim', explode(',', $wisata->fasilitas))) : [];

// Ambil foto galeri untuk wisata ini lewat class GaleriWisata
$galeriFotos = $galeriModel->getByWisataId($id);

require_once __DIR__ . '/includes/header.php';
?>

<div class="section" style="padding-top:30px;">
  <div class="breadcrumb">
    <a href="<?php echo BASE_URL; ?>index.php">Beranda</a> &raquo;
    <a href="<?php echo BASE_URL; ?>daftar_wisata.php">Destinasi</a> &raquo;
    <?php echo htmlspecialchars($wisata->nama_wisata); ?>
  </div>

  <div class="detail-grid">
    <div>
      <div class="detail-photo" style="<?php echo $wisata->foto ? 'background-image:url(' . UPLOAD_URL . htmlspecialchars($wisata->foto) . ')' : ''; ?>">
        <?php if (!$wisata->foto): ?>Belum ada foto<?php endif; ?>
      </div>

      <h3 style="margin:22px 0 8px;">Deskripsi</h3>
      <p style="font-size:14px;line-height:1.75;color:#4a463e;"><?php echo nl2br(htmlspecialchars($wisata->deskripsi)); ?></p>

      <?php if (!empty($fasilitas)): ?>
        <h3 style="margin:22px 0 4px;">Fasilitas</h3>
        <div class="facility-list">
          <?php foreach ($fasilitas as $f): ?>
            <span class="facility-chip">
              <span class="fc-icon"><?php echo facilityIcon($f); ?></span>
              <?php echo htmlspecialchars($f); ?>
            </span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($galeriFotos)): ?>
        <h3 style="margin:22px 0 4px;">Galeri Foto</h3>
        <div class="gallery-modern-grid">
          <?php foreach ($galeriFotos as $i => $g): ?>
            <div class="g-tile" onclick="openLightbox(<?php echo $i; ?>)">
              <img src="<?php echo GALERI_UPLOAD_URL . htmlspecialchars($g['nama_foto']); ?>"
                   alt="<?php echo htmlspecialchars($g['keterangan'] ?: $wisata->nama_wisata); ?>" loading="lazy">
              <?php if ($g['keterangan']): ?>
                <div class="g-caption"><?php echo htmlspecialchars($g['keterangan']); ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div>
      <h2 style="margin:0 0 6px;font-size:24px;"><?php echo htmlspecialchars($wisata->nama_wisata); ?></h2>
      <span class="badge"><?php echo htmlspecialchars($wisata->kategori); ?></span>

      <div class="info-cards">
        <div class="info-card">
          <div class="info-icon">📍</div>
          <div>
            <div class="info-label">Alamat</div>
            <div class="info-value"><?php echo htmlspecialchars($wisata->alamat ?: '-'); ?></div>
          </div>
        </div>
        <div class="info-card">
          <div class="info-icon">🕒</div>
          <div>
            <div class="info-label">Jam Buka</div>
            <div class="info-value"><?php echo htmlspecialchars($wisata->jam_buka ?: '-'); ?></div>
          </div>
        </div>
        <div class="info-card">
          <div class="info-icon">🎫</div>
          <div>
            <div class="info-label">Tiket Masuk</div>
            <div class="info-value"><?php echo htmlspecialchars($wisata->tiket_masuk ?: '-'); ?></div>
          </div>
        </div>
        <div class="info-card">
          <div class="info-icon">🧭</div>
          <div>
            <div class="info-label">Koordinat</div>
            <div class="info-value"><?php echo htmlspecialchars($wisata->latitude); ?>, <?php echo htmlspecialchars($wisata->longitude); ?></div>
          </div>
        </div>
      </div>

      <a class="btn btn-sm" target="_blank"
         href="https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode($wisata->latitude . ',' . $wisata->longitude); ?>">
        🧭 Rute ke Lokasi
      </a>

      <div id="detail-map"></div>
    </div>
  </div>
</div>

<?php if (!empty($galeriFotos)): ?>
<div class="lightbox-overlay" id="lightboxOverlay">
  <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
  <span class="lightbox-nav lightbox-prev" onclick="navLightbox(-1)">&#8249;</span>
  <img id="lightboxImg" src="" alt="">
  <span class="lightbox-nav lightbox-next" onclick="navLightbox(1)">&#8250;</span>
</div>
<?php endif; ?>

<script>
<?php if (!empty($galeriFotos)): ?>
  const galeriFotos = <?php echo json_encode(array_map(function($g) {
      return ['src' => GALERI_UPLOAD_URL . $g['nama_foto'], 'caption' => $g['keterangan']];
  }, $galeriFotos), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  let lightboxIndex = 0;

  function openLightbox(i) {
    lightboxIndex = i;
    updateLightbox();
    document.getElementById('lightboxOverlay').classList.add('open');
  }
  function closeLightbox() {
    document.getElementById('lightboxOverlay').classList.remove('open');
  }
  function navLightbox(dir) {
    lightboxIndex = (lightboxIndex + dir + galeriFotos.length) % galeriFotos.length;
    updateLightbox();
  }
  function updateLightbox() {
    document.getElementById('lightboxImg').src = galeriFotos[lightboxIndex].src;
  }
  document.addEventListener('keydown', function (e) {
    if (!document.getElementById('lightboxOverlay').classList.contains('open')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') navLightbox(-1);
    if (e.key === 'ArrowRight') navLightbox(1);
  });
<?php endif; ?>

  const lat = <?php echo json_encode((float)$wisata->latitude); ?>;
  const lng = <?php echo json_encode((float)$wisata->longitude); ?>;

  const dmap = L.map('detail-map').setView([lat, lng], 14);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(dmap);
  L.marker([lat, lng]).addTo(dmap)
    .bindPopup(<?php echo json_encode($wisata->nama_wisata); ?>)
    .openPopup();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
