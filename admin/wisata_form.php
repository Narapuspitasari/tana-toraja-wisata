<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;

$pageTitle = $isEdit ? 'Edit Data Wisata' : 'Tambah Data Wisata';
$adminActive = 'wisata';

$data = [
    'nama_wisata' => '', 'id_kategori' => '', 'alamat' => '', 'deskripsi' => '',
    'fasilitas' => '', 'jam_buka' => '', 'tiket_masuk' => '',
    'latitude' => '', 'longitude' => '', 'foto' => ''
];
$errors = [];

if ($isEdit) {
    $found = $wisataModel->getById($id);
    if (!$found) {
        header('Location: ' . BASE_URL . 'admin/wisata_list.php');
        exit;
    }
    $data = $found;
}

$kategoriList = $kategoriModel->getAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['nama_wisata'] = trim($_POST['nama_wisata'] ?? '');
    $data['id_kategori'] = trim($_POST['id_kategori'] ?? '');
    $data['alamat']      = trim($_POST['alamat'] ?? '');
    $data['deskripsi']   = trim($_POST['deskripsi'] ?? '');
    $data['fasilitas']   = trim($_POST['fasilitas'] ?? '');
    $data['jam_buka']    = trim($_POST['jam_buka'] ?? '');
    $data['tiket_masuk'] = trim($_POST['tiket_masuk'] ?? '');
    $data['latitude']    = trim($_POST['latitude'] ?? '');
    $data['longitude']   = trim($_POST['longitude'] ?? '');

    if ($data['nama_wisata'] === '') $errors[] = 'Nama wisata wajib diisi.';
    if ($data['id_kategori'] === '' || !ctype_digit((string)$data['id_kategori'])) $errors[] = 'Kategori wajib dipilih.';
    if ($data['latitude'] === '' || !is_numeric($data['latitude'])) $errors[] = 'Latitude harus berupa angka.';
    if ($data['longitude'] === '' || !is_numeric($data['longitude'])) $errors[] = 'Longitude harus berupa angka.';

    // Upload foto (opsional)
    $fotoName = $data['foto'];
    if (!empty($_FILES['foto']['name'])) {
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            $errors[] = 'Format foto harus JPG, JPEG, PNG, atau WEBP.';
        } elseif ($_FILES['foto']['size'] > 15 * 1024 * 1024) {
            // Batas file mentah dinaikkan ke 15MB (foto asli kamera HP) — akan
            // dikompres otomatis ke ukuran web-friendly di bawah ini.
            $errors[] = 'Ukuran foto maksimal 15MB.';
        } else {
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0755, true);
            }
            $newFotoName = 'wisata_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '', pathinfo($_FILES['foto']['name'], PATHINFO_FILENAME)) . '.jpg';
            if (compressUploadedImage($_FILES['foto']['tmp_name'], UPLOAD_DIR . $newFotoName)) {
                $fotoName = $newFotoName;
            } else {
                $errors[] = 'Gagal memproses foto (format gambar tidak didukung atau rusak).';
            }
        }
    }

    if (empty($errors)) {
        $payload = [
            'nama_wisata' => $data['nama_wisata'],
            'id_kategori' => (int)$data['id_kategori'],
            'alamat' => $data['alamat'],
            'deskripsi' => $data['deskripsi'],
            'fasilitas' => $data['fasilitas'],
            'jam_buka' => $data['jam_buka'],
            'tiket_masuk' => $data['tiket_masuk'],
            'latitude' => (float)$data['latitude'],
            'longitude' => (float)$data['longitude'],
            'foto' => $fotoName,
        ];

        if ($isEdit) {
            $wisataModel->update($id, $payload);
            header('Location: ' . BASE_URL . 'admin/wisata_list.php?msg=updated');
            exit;
        } else {
            // Catat admin yang sedang login sebagai pengelola data ini (relasi Admin - Wisata)
            $payload['id_admin'] = $_SESSION['admin_id'];
            $wisataModel->create($payload);
            header('Location: ' . BASE_URL . 'admin/wisata_list.php?msg=added');
            exit;
        }
    }
}

require_once __DIR__ . '/includes/layout_top.php';
?>

<h2><?php echo $isEdit ? 'Edit Data Wisata' : 'Tambah Data Wisata'; ?></h2>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <?php foreach ($errors as $e) echo htmlspecialchars($e) . '<br>'; ?>
  </div>
<?php endif; ?>

<div class="form-box">
  <form method="post" enctype="multipart/form-data">
    <div class="form-group">
      <label>Nama Wisata</label>
      <input type="text" name="nama_wisata" value="<?php echo htmlspecialchars($data['nama_wisata']); ?>" required>
    </div>

    <div class="form-group">
      <label>Kategori</label>
      <select name="id_kategori" required>
        <option value="">-- Pilih Kategori --</option>
        <?php foreach ($kategoriList as $k): ?>
          <option value="<?php echo $k['id_kategori']; ?>"
            <?php echo (string)$data['id_kategori'] === (string)$k['id_kategori'] ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($k['nama_kategori']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Alamat</label>
      <input type="text" name="alamat" value="<?php echo htmlspecialchars($data['alamat']); ?>">
    </div>

    <div class="form-group">
      <label>Deskripsi</label>
      <textarea name="deskripsi" rows="4"><?php echo htmlspecialchars($data['deskripsi']); ?></textarea>
    </div>

    <div class="form-group">
      <label>Fasilitas <span style="font-weight:400;color:#999;">(pisahkan dengan koma, contoh: Parkir,Toilet,Kuliner)</span></label>
      <input type="text" name="fasilitas" value="<?php echo htmlspecialchars($data['fasilitas']); ?>">
    </div>

    <div class="form-group">
      <label>Jam Buka</label>
      <input type="text" name="jam_buka" value="<?php echo htmlspecialchars($data['jam_buka']); ?>" placeholder="Contoh: 08.00 - 17.00">
    </div>

    <div class="form-group">
      <label>Tiket Masuk</label>
      <input type="text" name="tiket_masuk" value="<?php echo htmlspecialchars($data['tiket_masuk']); ?>" placeholder="Contoh: Rp 15.000 / orang">
    </div>

    <div class="form-group" style="display:flex;gap:16px;">
      <div style="flex:1;">
        <label>Latitude</label>
        <input type="text" name="latitude" value="<?php echo htmlspecialchars($data['latitude']); ?>" placeholder="-3.070441" required>
      </div>
      <div style="flex:1;">
        <label>Longitude</label>
        <input type="text" name="longitude" value="<?php echo htmlspecialchars($data['longitude']); ?>" placeholder="119.864728" required>
      </div>
    </div>
    <p style="font-size:12px;color:#888;margin-top:-10px;">
      💡 Tips: klik kanan lokasi di <a href="https://www.google.com/maps" target="_blank" style="color:#8c1c13;">Google Maps</a>, lalu salin koordinat yang muncul (format: latitude, longitude).
    </p>

    <div class="form-group">
      <label>Foto Wisata</label>
      <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp">
      <p style="font-size:11.5px;color:#999;margin-top:6px;">Foto akan otomatis dikompres, jadi foto asli dari kamera HP (sampai 15MB) tetap bisa diunggah.</p>
      <?php if ($data['foto']): ?>
        <img src="<?php echo UPLOAD_URL . htmlspecialchars($data['foto']); ?>" style="width:140px;margin-top:10px;border-radius:6px;">
      <?php endif; ?>
    </div>

    <button type="submit" class="btn"><?php echo $isEdit ? 'Simpan Perubahan' : 'Tambah Wisata'; ?></button>
    <a href="<?php echo BASE_URL; ?>admin/wisata_list.php" class="btn btn-outline" style="color:#333;border-color:#ccc;">Batal</a>
  </form>
</div>

<?php require_once __DIR__ . '/includes/layout_bottom.php'; ?>
