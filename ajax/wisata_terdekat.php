<?php
/**
 * Endpoint AJAX: Proximity Analysis (Location Based Service)
 *
 * Menerima koordinat lokasi pengguna (lat, lon) dari browser via Geolocation API,
 * lalu di SISI SERVER menghitung jarak ke seluruh data wisata di database
 * menggunakan Wisata::hitungJarak() (Haversine Formula), mengurutkannya dari
 * yang terdekat, dan mengembalikan hasilnya sebagai JSON.
 *
 * Alur ini sesuai Sequence Diagram pada proposal (Gambar 3.3):
 * Wisatawan -> Browser (Geolocation API) -> Server -> Database -> Server (hitung Haversine) -> Browser
 *
 * Parameter GET:
 *   lat     (wajib)  float  - latitude posisi pengguna
 *   lon     (wajib)  float  - longitude posisi pengguna
 *   radius  (opsional) float - radius pencarian dalam km (Buffer Analysis); kosongkan untuk semua data
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

$lat = isset($_GET['lat']) ? (float)$_GET['lat'] : null;
$lon = isset($_GET['lon']) ? (float)$_GET['lon'] : null;
$radius = (isset($_GET['radius']) && $_GET['radius'] !== '') ? (float)$_GET['radius'] : null;

if ($lat === null || $lon === null || $lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
    http_response_code(400);
    echo json_encode(['error' => 'Parameter lat/lon tidak valid.']);
    exit;
}

// Proximity Analysis dijalankan di sini, di sisi server, lewat class Wisata
$hasil = $wisataModel->getTerdekat($lat, $lon, $radius);

// Sertakan URL foto lengkap supaya bisa langsung dipakai oleh JavaScript di sisi client
$hasil = array_map(function ($w) {
    $w['foto_url'] = $w['foto'] ? UPLOAD_URL . $w['foto'] : null;
    $w['jarak'] = round($w['jarak'], 2);
    return $w;
}, $hasil);

echo json_encode([
    'user_location' => ['lat' => $lat, 'lon' => $lon],
    'radius_km' => $radius,
    'total' => count($hasil),
    'data' => $hasil,
], JSON_UNESCAPED_UNICODE);
