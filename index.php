<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Beranda';
$active = 'home';
$hasHero = true;

// Ambil wisata terbaru untuk grid destinasi + featured destination (lewat class Wisata)
$wisataTerbaru = $wisataModel->getAll(['limit' => 7, 'order_by_terbaru' => true]);
$featured = $wisataTerbaru[0] ?? null;
$destinasiGrid = $featured ? array_slice($wisataTerbaru, 1, 6) : array_slice($wisataTerbaru, 0, 6);

// Kategori wisata untuk section editorial
$kategoriList = $kategoriModel->getAll();

// Foto untuk section "about" dan "cultural story": pakai foto wisata yang sudah ada di uploads bila tersedia
$aboutPhoto = null;
$storyPhoto = null;
foreach ($wisataTerbaru as $w) {
    if (!empty($w['foto'])) {
        if (!$aboutPhoto) { $aboutPhoto = UPLOAD_URL . $w['foto']; continue; }
        if (!$storyPhoto) { $storyPhoto = UPLOAD_URL . $w['foto']; break; }
    }
}
$heroPhoto = 'https://commons.wikimedia.org/wiki/Special:FilePath/Rumah_adat_tongkonan_Toraja.jpg?width=1800';
$ctaPhoto = $storyPhoto ?: $heroPhoto;

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="hero-media" style="background-image:url('<?php echo $heroPhoto; ?>');"></div>
  <div class="hero-scrim"></div>
  <div class="hero-content">
    <span class="hero-label">Pariwisata Kabupaten Tana Toraja</span>
    <h1 class="hero-title">Temukan<br><em>Tana Toraja</em></h1>
    <p class="hero-sub">Jelajahi budaya, alam, dan destinasi wisata Tana Toraja melalui pengalaman digital yang interaktif.</p>
    <div class="hero-actions">
      <a href="<?php echo BASE_URL; ?>daftar_wisata.php" class="btn">Jelajahi Destinasi</a>
      <a href="<?php echo BASE_URL; ?>peta.php" class="btn btn-line">Lihat Peta</a>
    </div>
  </div>
  <div class="hero-side">
    <span>Scroll</span>
    <span class="hero-vline"></span>
  </div>
  <div class="hero-credit">
    Foto: <a href="https://commons.wikimedia.org/wiki/File:Rumah_adat_tongkonan_Toraja.jpg" target="_blank" rel="noopener">Wikimedia Commons</a> (CC BY-SA 4.0)
  </div>
</section>

<!-- Section 01: Discover Tana Toraja -->
<section class="about-editorial" id="tentang">
  <div class="section">
    <div class="ed-eyebrow reveal"><span class="num">01</span> Discover Tana Toraja</div>
    <div class="about-grid">
      <div>
        <h2 class="about-heading reveal">Tana Toraja bukan<br>hanya sebuah destinasi.</h2>
        <div class="about-copy reveal reveal-delay-1">
          <p>Ia adalah ruang tempat alam, budaya, tradisi, dan kehidupan masyarakat bertemu — dari rumah adat
            Tongkonan yang berdiri megah, upacara adat yang sarat makna, hingga lanskap pegunungan yang
            membentang di setiap penjuru kabupaten.</p>
          <p>Sistem ini dibangun untuk membantu wisatawan menemukan, memahami, dan merencanakan perjalanan
            menjelajahi kekayaan pariwisata Tana Toraja secara digital dan interaktif.</p>
        </div>
        <div class="about-stats reveal reveal-delay-2">
          <div>
            <div class="stat-num"><?php echo count($wisataModel->getAll()); ?>+</div>
            <div class="stat-lbl">Destinasi Terdaftar</div>
          </div>
          <div>
            <div class="stat-num"><?php echo count($kategoriList); ?></div>
            <div class="stat-lbl">Kategori Wisata</div>
          </div>
          <div>
            <div class="stat-num">1</div>
            <div class="stat-lbl">Kabupaten</div>
          </div>
        </div>
      </div>
      <div class="about-photo reveal reveal-delay-2">
        <?php if ($aboutPhoto): ?>
          <img src="<?php echo htmlspecialchars($aboutPhoto); ?>" alt="Destinasi wisata Tana Toraja" loading="lazy">
        <?php else: ?>
          <img src="<?php echo $heroPhoto; ?>" alt="Rumah adat Tongkonan Tana Toraja" loading="lazy">
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- Section 02: Destinations -->
<section class="dest-section">
  <div class="section">
    <div class="ed-eyebrow reveal"><span class="num">02</span> Destinations</div>
    <div class="dest-head">
      <h2 class="about-heading reveal">Jelajahi Destinasi</h2>
      <a href="<?php echo BASE_URL; ?>daftar_wisata.php" class="dest-view-all reveal">Lihat Semua Destinasi &rarr;</a>
    </div>

    <?php if (!empty($destinasiGrid)): ?>
      <div class="dest-grid">
        <?php foreach ($destinasiGrid as $i => $row): ?>
          <a class="dest-card reveal <?php echo ($i === 0) ? 'large' : ''; ?>"
             href="<?php echo BASE_URL; ?>detail.php?id=<?php echo (int)$row['id_wisata']; ?>">
            <?php if (!empty($row['foto'])): ?>
              <div class="thumb-img" style="background-image:url('<?php echo UPLOAD_URL . htmlspecialchars($row['foto']); ?>');"></div>
            <?php else: ?>
              <div class="thumb-empty">Belum ada foto</div>
            <?php endif; ?>
            <div class="dc-overlay"></div>
            <span class="dc-arrow">&#8594;</span>
            <div class="dc-body">
              <span class="dc-cat"><?php echo htmlspecialchars($row['kategori']); ?></span>
              <h3 class="dc-title"><?php echo htmlspecialchars($row['nama_wisata']); ?></h3>
              <div class="dc-loc">&#128205; <?php echo htmlspecialchars($row['alamat']); ?></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p>Belum ada data wisata. Silakan tambahkan data melalui halaman admin.</p>
    <?php endif; ?>
  </div>
</section>

<!-- Section 03: Featured Destination -->
<?php if ($featured): ?>
<section class="featured-section">
  <div class="featured-grid">
    <div class="featured-photo reveal">
      <?php if (!empty($featured['foto'])): ?>
        <img src="<?php echo UPLOAD_URL . htmlspecialchars($featured['foto']); ?>" alt="<?php echo htmlspecialchars($featured['nama_wisata']); ?>" loading="lazy">
      <?php else: ?>
        <div class="fp-empty">Belum ada foto</div>
      <?php endif; ?>
    </div>
    <div class="featured-text reveal reveal-delay-1">
      <div class="ed-eyebrow"><span class="num">03</span> Featured Destination</div>
      <h2><?php echo htmlspecialchars($featured['nama_wisata']); ?></h2>
      <p class="fp-tagline">Panorama dan pesona Tana Toraja dari dekat.</p>
      <p class="fp-loc">&#128205; <?php echo htmlspecialchars($featured['alamat']); ?> &middot; <?php echo htmlspecialchars($featured['kategori']); ?></p>
      <div class="fp-actions">
        <a href="<?php echo BASE_URL; ?>detail.php?id=<?php echo (int)$featured['id_wisata']; ?>" class="btn">Lihat Detail</a>
        <a href="<?php echo BASE_URL; ?>peta.php" class="btn btn-line">Lihat di Peta</a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Section 04: Interactive Map teaser -->
<section class="strip strip-white">
  <div class="section" style="padding-top:10px;">
    <div class="ed-eyebrow reveal"><span class="num">04</span> Explore on the Map</div>
    <div class="about-grid" style="align-items:center;">
      <div>
        <h2 class="about-heading reveal">Peta Interaktif<br>Seluruh Destinasi</h2>
        <p class="section-sub reveal reveal-delay-1" style="max-width:480px;">
          Jelajahi lokasi setiap destinasi wisata Tana Toraja melalui peta digital berbasis Leaflet JS —
          lengkap dengan pencarian, filter, dan penanda lokasi interaktif.
        </p>
        <div class="reveal reveal-delay-2">
          <a href="<?php echo BASE_URL; ?>peta.php" class="btn btn-forest">Buka Peta &rarr;</a>
        </div>
      </div>
      <div class="about-photo reveal reveal-delay-1" style="aspect-ratio:4/3;">
        <img src="<?php echo $heroPhoto; ?>" alt="Peta wisata Tana Toraja" loading="lazy">
      </div>
    </div>
  </div>
</section>

<!-- Section 06: Categories -->
<?php if (!empty($kategoriList)): ?>
<section class="cat-section">
  <div class="section">
    <div class="ed-eyebrow reveal"><span class="num">06</span> Kategori Wisata</div>
    <div class="cat-list">
      <?php foreach ($kategoriList as $i => $k): ?>
        <a class="cat-item reveal" href="<?php echo BASE_URL; ?>daftar_wisata.php?kategori=<?php echo urlencode($k['nama_kategori']); ?>">
          <span class="cat-bg" style="background-image:url('<?php echo $heroPhoto; ?>');"></span>
          <span class="cat-num"><?php echo str_pad($i + 1, 2, '0', STR_PAD_LEFT); ?></span>
          <span class="cat-name"><?php echo htmlspecialchars($k['nama_kategori']); ?></span>
          <span class="cat-arrow">&#8594;</span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Section 07: Cultural Story -->
<section class="story-section">
  <div class="section">
    <div class="story-grid">
      <div class="story-photo reveal">
        <img src="<?php echo $storyPhoto ?: $heroPhoto; ?>" alt="Budaya Tana Toraja" loading="lazy">
      </div>
      <div class="reveal reveal-delay-1">
        <div class="ed-eyebrow"><span class="num">07</span> Cultural Story</div>
        <h2 class="story-heading">Di Mana <em>Tradisi</em><br>Tetap Hidup</h2>
        <div class="story-text">
          <p>Rumah adat Tongkonan, upacara Rambu Solo, dan kearifan lokal masyarakat Toraja terus dijaga
            dari generasi ke generasi — menjadikan Tana Toraja salah satu destinasi budaya paling khas
            di Indonesia.</p>
          <p>Setiap destinasi dalam sistem ini membawa cerita alam dan budaya yang menunggu untuk dijelajahi.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Section 08: CTA -->
<section class="cta-section">
  <div class="cta-media" style="background-image:url('<?php echo $ctaPhoto; ?>');"></div>
  <div class="cta-scrim"></div>
  <div class="cta-inner">
    <div class="ed-eyebrow reveal"><span class="num">08</span></div>
    <h2 class="cta-heading reveal reveal-delay-1">Perjalananmu<br><em>Dimulai di Tana Toraja.</em></h2>
    <div class="cta-actions reveal reveal-delay-2">
      <a href="<?php echo BASE_URL; ?>daftar_wisata.php" class="btn">Jelajahi Destinasi</a>
      <a href="<?php echo BASE_URL; ?>peta.php" class="btn btn-line">Lihat Peta</a>
    </div>
  </div>
</section>

<!-- Kontak -->
<div class="strip strip-white" id="kontak">
  <div class="section">
    <h2 class="section-title reveal">Kontak</h2>
    <p class="section-sub reveal" style="margin-bottom:8px;">Hubungi kami untuk informasi lebih lanjut seputar pariwisata Tana Toraja.</p>
    <p class="reveal" style="font-size:14px;color:#4a473d;line-height:1.9;">
      &#128205; Tana Toraja, Sulawesi Selatan, Indonesia<br>
      &#9993; info@tanatorajapariwisata.go.id
    </p>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
