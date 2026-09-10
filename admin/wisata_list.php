<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Kelola Data Wisata';
$adminActive = 'wisata';

$keyword = trim($_GET['q'] ?? '');

// Ambil data lewat class Wisata (bukan query manual lagi)
$wisataResult = $wisataModel->getAll(['keyword' => $keyword]);

require_once __DIR__ . '/includes/layout_top.php';
?>

<div class="admin-topbar">
  <h2 style="margin:0;">Kelola Data Wisata</h2>
  <a href="<?php echo BASE_URL; ?>admin/wisata_form.php" class="btn btn-sm">+ Tambah Data Wisata</a>
</div>

<?php if (isset($_GET['msg'])): ?>
  <div class="alert alert-success">
    <?php
      echo $_GET['msg'] === 'added' ? 'Data wisata berhasil ditambahkan.' :
           ($_GET['msg'] === 'updated' ? 'Data wisata berhasil diperbarui.' :
           ($_GET['msg'] === 'deleted' ? 'Data wisata berhasil dihapus.' : ''));
    ?>
  </div>
<?php endif; ?>

<form method="get" style="margin-bottom:16px;">
  <input type="text" name="q" placeholder="Cari nama wisata..." value="<?php echo htmlspecialchars($keyword); ?>"
         style="padding:9px 12px;border:1px solid #ddd;border-radius:6px;font-size:13.5px;width:280px;">
  <button class="btn btn-sm" type="submit">Cari</button>
</form>

<table class="data-table">
  <thead>
    <tr>
      <th>No</th><th>Nama Wisata</th><th>Kategori</th><th>Lokasi</th><th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($wisataResult)): $no = 1; ?>
      <?php foreach ($wisataResult as $row): ?>
        <tr>
          <td><?php echo $no++; ?></td>
          <td><?php echo htmlspecialchars($row['nama_wisata']); ?></td>
          <td><?php echo htmlspecialchars($row['kategori']); ?></td>
          <td><?php echo htmlspecialchars($row['alamat']); ?></td>
          <td>
            <a href="<?php echo BASE_URL; ?>admin/wisata_form.php?id=<?php echo $row['id_wisata']; ?>" class="btn btn-sm">✏️ Edit</a>
            <a href="<?php echo BASE_URL; ?>admin/galeri.php?id_wisata=<?php echo $row['id_wisata']; ?>" class="btn btn-sm btn-outline" style="color:var(--toraja-green-dark);border-color:var(--toraja-green-dark);">🖼️ Galeri</a>
            <a href="<?php echo BASE_URL; ?>admin/wisata_delete.php?id=<?php echo $row['id_wisata']; ?>"
               class="btn btn-sm btn-danger"
               onclick="return confirm('Yakin ingin menghapus data ini?');">🗑️ Hapus</a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="5">Tidak ada data ditemukan.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php require_once __DIR__ . '/includes/layout_bottom.php'; ?>
