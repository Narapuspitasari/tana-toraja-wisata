<?php
require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Hapus semua file foto galeri terkait (baris galeri_wisata akan otomatis
    // terhapus lewat ON DELETE CASCADE saat wisata dihapus, tapi file fisiknya
    // perlu dihapus manual)
    $namaFotoGaleri = $galeriModel->getFileNamesByWisataId($id);
    foreach ($namaFotoGaleri as $namaFoto) {
        if ($namaFoto && file_exists(GALERI_UPLOAD_DIR . $namaFoto)) {
            unlink(GALERI_UPLOAD_DIR . $namaFoto);
        }
    }

    // Hapus data wisata (mengembalikan nama file foto sampul untuk dibersihkan)
    $fotoSampul = $wisataModel->delete($id);
    if ($fotoSampul && file_exists(UPLOAD_DIR . $fotoSampul)) {
        unlink(UPLOAD_DIR . $fotoSampul);
    }
}

header('Location: ' . BASE_URL . 'admin/wisata_list.php?msg=deleted');
exit;
