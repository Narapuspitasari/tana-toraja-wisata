<?php
require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$idWisata = isset($_GET['id_wisata']) ? (int)$_GET['id_wisata'] : 0;

if ($id > 0) {
    $namaFoto = $galeriModel->deleteById($id);
    if ($namaFoto && file_exists(GALERI_UPLOAD_DIR . $namaFoto)) {
        unlink(GALERI_UPLOAD_DIR . $namaFoto);
    }
}

header('Location: ' . BASE_URL . 'admin/galeri.php?id_wisata=' . $idWisata);
exit;
