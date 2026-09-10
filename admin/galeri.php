<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$idWisata = isset($_GET['id_wisata']) ? (int)$_GET['id_wisata'] : 0;

$wisata = $wisataModel->getById($idWisata);

if (!$wisata) {
    header('Location: ' . BASE_URL . 'admin/wisata_list.php');
    exit;
}

$pageTitle = 'Galeri - ' . $wisata['nama_wisata'];
$adminActive = 'wisata';

$errors = [];
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keterangan = trim($_POST['keterangan'] ?? '');

    if (empty($_FILES['foto_galeri']['name'][0])) {
        $errors[] = 'Pilih minimal satu foto untuk diunggah.';
    } else {
        if (!is_dir(GALERI_UPLOAD_DIR)) {
            mkdir(GALERI_UPLOAD_DIR, 0755, true);
        }

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $uploaded = 0;

        foreach ($_FILES['foto_galeri']['name'] as $i => $fileName) {
            if ($_FILES['foto_galeri']['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt)) {
                $errors[] = "File \"$fileName\" dilewati (format tidak didukung).";
                continue;
            }
            // Batas file mentah dinaikkan ke 15MB (foto asli kamera HP) — akan
            // dikompres otomatis ke ukuran web-friendly di bawah ini, jadi
            // ukuran akhir yang tersimpan selalu ringan.
            if ($_FILES['foto_galeri']['size'][$i] > 15 * 1024 * 1024) {
                $errors[] = "File \"$fileName\" dilewati (ukuran lebih dari 15MB).";
                continue;
            }

            $newName = 'galeri_' . $idWisata . '_' . time() . '_' . $i . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '', pathinfo($fileName, PATHINFO_FILENAME)) . '.jpg';

            if (compressUploadedImage($_FILES['foto_galeri']['tmp_name'][$i], GALERI_UPLOAD_DIR . $newName)) {
                $galeriModel->create($idWisata, $newName, $keterangan);
                $uploaded++;
            } else {
                $errors[] = "File \"$fileName\" gagal diproses (format gambar tidak didukung atau rusak).";
            }
        }

        if ($uploaded > 0) {
            $successMsg = "$uploaded foto berhasil ditambahkan ke galeri.";
        }
    }
}

// Ambil semua foto galeri untuk wisata ini lewat class GaleriWisata
$galeriList = array_reverse($galeriModel->getByWisataId($idWisata));

require_once __DIR__ . '/includes/layout_top.php';
?>

<div class="admin-topbar">
  <div>
    <h2>Galeri Foto</h2>
    <p class="sub">Kelola foto galeri untuk <strong><?php echo htmlspecialchars($wisata['nama_wisata']); ?></strong></p>
  </div>
  <a href="<?php echo BASE_URL; ?>admin/wisata_list.php" class="btn btn-sm btn-outline" style="color:var(--toraja-green-dark);border-color:var(--toraja-green-dark);">&larr; Kembali</a>
</div>

<?php if ($successMsg): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($successMsg); ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
  <div class="alert alert-danger"><?php foreach ($errors as $e) echo htmlspecialchars($e) . '<br>'; ?></div>
<?php endif; ?>

<div class="form-box" style="margin-bottom:24px;">
  <form method="post" enctype="multipart/form-data">
    <div class="form-group">
      <label>Pilih Foto (bisa lebih dari satu)</label>
      <input type="file" name="foto_galeri[]" accept=".jpg,.jpeg,.png,.webp" multiple required>
      <p style="font-size:11.5px;color:#999;margin-top:6px;">Foto akan otomatis dikompres, jadi foto asli dari kamera HP (sampai 15MB per foto) tetap bisa diunggah.</p>
    </div>
    <div class="form-group">
      <label>Keterangan <span style="font-weight:400;color:#999;">(opsional, berlaku untuk semua foto yang diunggah)</span></label>
      <input type="text" name="keterangan" placeholder="Contoh: Suasana pagi hari">
    </div>
    <button type="submit" class="btn btn-sm">Unggah Foto</button>
  </form>
</div>

<div class="card-panel">
  <h3>Foto dalam Galeri (<?php echo count($galeriList); ?>)</h3>

  <?php if (!empty($galeriList)): ?>
    <div class="gallery-admin-grid">
      <?php foreach ($galeriList as $g): ?>
        <div class="gallery-admin-item">
          <img src="<?php echo GALERI_UPLOAD_URL . htmlspecialchars($g['nama_foto']); ?>" alt="">
          <?php if ($g['keterangan']): ?>
            <div class="gallery-admin-caption"><?php echo htmlspecialchars($g['keterangan']); ?></div>
          <?php endif; ?>
          <a href="<?php echo BASE_URL; ?>admin/galeri_delete.php?id=<?php echo $g['id_galeri']; ?>&id_wisata=<?php echo $idWisata; ?>"
             class="gallery-admin-delete"
             onclick="return confirm('Hapus foto ini dari galeri?');">🗑️</a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p style="color:#999;font-size:13.5px;">Belum ada foto di galeri. Unggah foto pertama menggunakan form di atas.</p>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/layout_bottom.php'; ?>
