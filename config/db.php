<?php
/**
 * Koneksi database - disesuaikan untuk Laragon.
 * Default Laragon: host=localhost, user=root, password=''(kosong), port=3306
 * Jika Anda mengubah port MySQL di Laragon, sesuaikan DB_HOST di bawah (contoh: 'localhost;port=3307').
 */
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tana_toraja_wisata');

$koneksi = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($koneksi->connect_error) {
    die('Koneksi database gagal: ' . $koneksi->connect_error .
        '<br>Pastikan Laragon (Apache + MySQL) sudah dijalankan dan database "tana_toraja_wisata" sudah diimport.');
}

$koneksi->set_charset('utf8mb4');
