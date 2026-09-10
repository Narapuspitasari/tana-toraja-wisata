<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        // Memanggil method login() pada class Admin (sesuai Class Diagram) —
        // method ini mengisi atribut $adminModel (id_admin, username, dst.)
        // apabila autentikasi berhasil.
        if ($adminModel->login($username, $password)) {
            $_SESSION['admin_id'] = $adminModel->id_admin;
            $_SESSION['admin_username'] = $adminModel->username;
            $_SESSION['admin_nama'] = $adminModel->nama_lengkap;
            header('Location: ' . BASE_URL . 'admin/dashboard.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Admin - SIG Pariwisata Tana Toraja</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo @filemtime(__DIR__ . '/../assets/css/style.css') ?: time(); ?>">
</head>
<body>
<div class="login-wrapper">
  <div class="login-box">
    <h2>Login Admin</h2>
    <p>Silakan masuk untuk mengelola halaman admin.</p>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" required autofocus>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn" style="width:100%;">Login</button>
    </form>
    <p style="margin-top:16px;"><a href="<?php echo BASE_URL; ?>index.php" style="color:#8c1c13;font-size:13px;">&larr; Kembali ke Beranda</a></p>
  </div>
</div>
</body>
</html>
