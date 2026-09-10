<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Ganti Password';
$adminActive = 'password';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Ambil data admin yang sedang login lewat class Admin
    $admin = $adminModel->findById($_SESSION['admin_id']);

    if (!$admin || !$adminModel->verifyPassword($currentPassword, $admin['password'])) {
        $errors[] = 'Password saat ini salah.';
    }
    if (strlen($newPassword) < 6) {
        $errors[] = 'Password baru minimal 6 karakter.';
    }
    if ($newPassword !== $confirmPassword) {
        $errors[] = 'Konfirmasi password baru tidak cocok.';
    }

    if (empty($errors)) {
        $adminModel->updatePassword($_SESSION['admin_id'], $newPassword);
        $success = true;
    }
}

require_once __DIR__ . '/includes/layout_top.php';
?>

<h2>Ganti Password</h2>
<p style="color:#777;font-size:13.5px;margin-top:-8px;">
  Password baru akan otomatis di-hash (bcrypt) sebelum disimpan ke database — jangan pernah mengetik password langsung ke tabel lewat HeidiSQL/phpMyAdmin.
</p>

<?php if ($success): ?>
  <div class="alert alert-success">Password berhasil diperbarui. Gunakan password baru pada login berikutnya.</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <?php foreach ($errors as $e) echo htmlspecialchars($e) . '<br>'; ?>
  </div>
<?php endif; ?>

<div class="form-box">
  <form method="post">
    <div class="form-group">
      <label>Password Saat Ini</label>
      <input type="password" name="current_password" required autofocus>
    </div>
    <div class="form-group">
      <label>Password Baru</label>
      <input type="password" name="new_password" required minlength="6">
    </div>
    <div class="form-group">
      <label>Konfirmasi Password Baru</label>
      <input type="password" name="confirm_password" required minlength="6">
    </div>
    <button type="submit" class="btn">Simpan Password Baru</button>
  </form>
</div>

<?php require_once __DIR__ . '/includes/layout_bottom.php'; ?>
