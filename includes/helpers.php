<?php
/**
 * Mengembalikan ikon (emoji) yang sesuai untuk nama fasilitas tertentu.
 * Pencocokan bersifat "contains" dan tidak case-sensitive, jadi fasilitas seperti
 * "Parkir Motor & Mobil" tetap cocok dengan kata kunci "parkir".
 */
function facilityIcon(string $name): string
{
    $n = strtolower(trim($name));

    $map = [
        'parkir'      => '🅿️',
        'toilet'      => '🚻',
        'musholla'    => '🕌',
        'mushola'     => '🕌',
        'masjid'      => '🕌',
        'kuliner'     => '🍽️',
        'restoran'    => '🍽️',
        'warung'      => '🍽️',
        'souvenir'    => '🎁',
        'oleh-oleh'   => '🎁',
        'guide'       => '🧭',
        'pemandu'     => '🧭',
        'senter'      => '🔦',
        'gazebo'      => '⛺',
        'kafe'        => '☕',
        'cafe'        => '☕',
        'spot foto'   => '📸',
        'foto'        => '📸',
        'penginapan'  => '🏨',
        'hotel'       => '🏨',
        'homestay'    => '🏨',
        'atm'         => '🏧',
        'wifi'        => '📶',
        'listrik'     => '🔌',
        'p3k'         => '🩹',
        'kesehatan'   => '🩹',
        'anak'        => '🧸',
        'bermain'     => '🧸',
        'kolam'       => '🏊',
        'air'         => '🚰',
    ];

    foreach ($map as $keyword => $icon) {
        if (strpos($n, $keyword) !== false) {
            return $icon;
        }
    }

    return '📌'; // ikon default jika tidak ada kata kunci yang cocok
}

/**
 * Mengompres & mengubah ukuran gambar hasil upload agar ringan untuk web,
 * tanpa menolak foto berukuran besar dari kamera HP (yang sering 4-8MB).
 *
 * - Lebar/tinggi maksimum dibatasi (default 1600px), foto lebih besar akan
 *   diperkecil secara proporsional.
 * - Disimpan ulang sebagai JPEG dengan kualitas terkompresi (default 80),
 *   biasanya menghasilkan file di bawah 500KB meski aslinya beberapa MB.
 * - Mendukung file sumber JPEG, PNG, dan WEBP.
 *
 * @param string $sourcePath Path file sementara hasil upload ($_FILES[...]['tmp_name'])
 * @param string $destPath   Path tujuan penyimpanan (harus berekstensi .jpg)
 * @return bool True jika berhasil, false jika gagal (mis. format tidak didukung GD)
 */
function compressUploadedImage(string $sourcePath, string $destPath, int $maxDimension = 1600, int $quality = 80): bool
{
    $info = @getimagesize($sourcePath);
    if ($info === false) {
        return false;
    }

    [$width, $height, $type] = $info;

    switch ($type) {
        case IMAGETYPE_JPEG:
            $srcImage = @imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $srcImage = @imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_WEBP:
            $srcImage = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false;
            break;
        default:
            return false;
    }

    if (!$srcImage) {
        return false;
    }

    // Hitung ukuran baru (proporsional), hanya diperkecil jika melebihi batas maksimum
    $ratio = min(1, $maxDimension / max($width, $height));
    $newWidth = (int) round($width * $ratio);
    $newHeight = (int) round($height * $ratio);

    $destImage = imagecreatetruecolor($newWidth, $newHeight);

    // Latar putih untuk PNG transparan, supaya tidak jadi hitam saat disimpan sebagai JPEG
    $white = imagecolorallocate($destImage, 255, 255, 255);
    imagefill($destImage, 0, 0, $white);

    imagecopyresampled($destImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    $result = imagejpeg($destImage, $destPath, $quality);

    return $result;
}
