-- =========================================================
-- Database: tana_toraja_wisata
-- Sistem Informasi Pemetaan Pariwisata Kabupaten Tana Toraja
-- Menggunakan Analisis Kedekatan Lokasi (Proximity Analysis)
-- berbasis Location Based Service (LBS)
--
-- Skema ini sudah dinormalisasi sesuai ERD pada proposal revisi:
--   Admin (1) -- (M) Wisata            [id_admin FK di tabel wisata]
--   Kategori_Wisata (1) -- (M) Wisata  [id_kategori FK di tabel wisata]
--   Wisata (1) -- (M) Galeri_Wisata    [id_wisata FK di tabel galeri_wisata]
-- =========================================================

CREATE DATABASE IF NOT EXISTS tana_toraja_wisata
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE tana_toraja_wisata;

-- Hapus tabel lama jika ada (supaya file ini aman dijalankan ulang / re-import)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS galeri_wisata;
DROP TABLE IF EXISTS wisata;
DROP TABLE IF EXISTS kategori_wisata;
DROP TABLE IF EXISTS admin;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------
-- Tabel admin
-- ---------------------------------------------------------
CREATE TABLE admin (
  id_admin     INT AUTO_INCREMENT PRIMARY KEY,
  username     VARCHAR(50) NOT NULL UNIQUE,
  password     VARCHAR(255) NOT NULL,
  nama_lengkap VARCHAR(100) DEFAULT NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Password default: admin123  (sudah di-hash dengan password_hash PHP - bcrypt)
-- Silakan login lalu ganti password lewat menu "Ganti Password".
INSERT INTO admin (username, password, nama_lengkap) VALUES
('admin', '$2b$10$2278aNZk.6Y/LgWITNAG9eKaSSSyrSr9qtti76ljbiKC6C2JlFqkC', 'Administrator');

-- ---------------------------------------------------------
-- Tabel kategori_wisata
-- ---------------------------------------------------------
CREATE TABLE kategori_wisata (
  id_kategori   INT AUTO_INCREMENT PRIMARY KEY,
  nama_kategori VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO kategori_wisata (id_kategori, nama_kategori) VALUES
(1, 'Wisata Budaya'),
(2, 'Wisata Alam'),
(3, 'Wisata Religi'),
(4, 'Wisata Lainnya');

-- ---------------------------------------------------------
-- Tabel wisata
-- id_kategori  : Foreign Key -> kategori_wisata (relasi 1 kategori - M wisata)
-- id_admin     : Foreign Key -> admin (relasi 1 admin mengelola - M wisata)
-- latitude/longitude : DECIMAL agar dapat langsung dipakai pada
--                       perhitungan Haversine Formula (Proximity Analysis)
--                       tanpa perlu konversi tipe data.
-- ---------------------------------------------------------
CREATE TABLE wisata (
  id_wisata    INT AUTO_INCREMENT PRIMARY KEY,
  nama_wisata  VARCHAR(100) NOT NULL,
  id_kategori  INT NOT NULL,
  id_admin     INT DEFAULT NULL,
  alamat       TEXT,
  deskripsi    TEXT,
  fasilitas    VARCHAR(255) DEFAULT NULL,
  jam_buka     VARCHAR(100) DEFAULT NULL,
  tiket_masuk  VARCHAR(100) DEFAULT NULL,
  latitude     DECIMAL(10,8) NOT NULL,
  longitude    DECIMAL(11,8) NOT NULL,
  foto         VARCHAR(255) DEFAULT NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_wisata_kategori FOREIGN KEY (id_kategori)
    REFERENCES kategori_wisata(id_kategori) ON DELETE RESTRICT,
  CONSTRAINT fk_wisata_admin FOREIGN KEY (id_admin)
    REFERENCES admin(id_admin) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Data contoh (silakan diedit / ditambah lewat halaman admin)
-- id_kategori: 1=Wisata Budaya, 2=Wisata Alam, 3=Wisata Religi, 4=Wisata Lainnya
INSERT INTO wisata
  (nama_wisata, id_kategori, id_admin, alamat, deskripsi, fasilitas, jam_buka, tiket_masuk, latitude, longitude, foto)
VALUES
('Ke''te Kesu', 1, 1,
 'Kec. Kesu, Kab. Tana Toraja, Sulawesi Selatan',
 'Ke''te Kesu merupakan desa wisata bertema di Tana Toraja yang memiliki tatanan rumah adat Tongkonan dan lumbung padi berusia ratusan tahun, serta situs kuburan gantung di tebing batu.',
 'Parkir,Toilet,Musholla,Kuliner,Souvenir',
 '08.00 - 17.00',
 'Rp 15.000 / orang',
 -3.070441, 119.864728, NULL),

('Londa', 1, 1,
 'Kec. Sanggalangi, Kab. Tana Toraja, Sulawesi Selatan',
 'Londa adalah situs kuburan gua dan gantung khas Toraja lengkap dengan patung Tau-tau (patung kayu menyerupai jenazah) di depan gua.',
 'Parkir,Guide Lokal,Senter Sewa',
 '08.00 - 17.00',
 'Rp 15.000 / orang',
 -3.055278, 119.822500, NULL),

('Negeri di Atas Awan Lolai', 2, 1,
 'Kec. Kapala Pitu, Kab. Tana Toraja, Sulawesi Selatan',
 'Lolai dikenal sebagai negeri di atas awan karena pemandangan lautan awan yang dapat dinikmati pada pagi hari sebelum matahari terbit.',
 'Parkir,Gazebo,Kafe,Spot Foto',
 '05.00 - 18.00',
 'Rp 15.000 / orang',
 -2.937500, 119.826900, NULL),

('Batutumonga', 2, 1,
 'Kec. Sesean, Kab. Tana Toraja, Sulawesi Selatan',
 'Batutumonga menawarkan pemandangan hamparan sawah, perbukitan, dan kompleks Tongkonan dari ketinggian.',
 'Parkir,Penginapan,Kafe',
 '00.00 - 24.00',
 'Gratis',
 -2.958600, 119.829400, NULL),

('Air Terjun Sarambu Assing', 2, 1,
 'Kec. Makale Selatan, Kab. Tana Toraja, Sulawesi Selatan',
 'Air terjun dengan suasana alam yang masih asri, cocok untuk wisata keluarga dan pecinta alam.',
 'Parkir,Toilet,Warung',
 '08.00 - 17.00',
 'Rp 10.000 / orang',
 -3.116700, 119.833300, NULL),

('Buntu Burake', 3, 1,
 'Kec. Makale, Kab. Tana Toraja, Sulawesi Selatan',
 'Kawasan wisata religi dengan patung Yesus Kristus setinggi puluhan meter dan pemandangan Kota Makale dari puncak bukit.',
 'Parkir,Musholla,Kafe,Spot Foto',
 '08.00 - 18.00',
 'Rp 20.000 / orang',
 -3.088900, 119.856400, NULL),

('Wisata Alam To''pinus Burasia', 2, 1,
 'Burasia, Kec. Bittuang, Kabupaten Tana Toraja, Sulawesi Selatan 91856',
 'Kawasan wisata alam dengan hamparan pohon pinus yang asri, cocok untuk kegiatan berkemah dan menikmati udara sejuk pegunungan Bittuang.',
 'Parkir,Area Camping,Spot Foto',
 '08.00 - 17.00',
 'Rp 10.000 / orang',
 -3.033463, 119.692484, NULL),

('Air Terjun Sarambu Bittuang', 2, 1,
 'Patongloan, Kec. Bittuang, Kabupaten Tana Toraja, Sulawesi Selatan 91856',
 'Air terjun alami di wilayah Bittuang dengan pemandangan pegunungan yang masih asri, menjadi salah satu destinasi wisata alam favorit di kawasan barat Tana Toraja.',
 'Parkir,Jalur Trekking',
 '08.00 - 17.00',
 'Rp 10.000 / orang',
 -2.948913, 119.652953, NULL),

('Pango-Pango Makale', 2, 1,
 'Kelurahan Pasang, Kec. Makale Selatan, Kabupaten Tana Toraja, Sulawesi Selatan 91815',
 'Kawasan agrowisata dan bumi perkemahan di ketinggian dengan pemandangan perkebunan serta udara pegunungan yang sejuk, populer untuk wisata keluarga dan camping.',
 'Parkir,Area Camping,Kafe,Spot Foto',
 '08.00 - 18.00',
 'Rp 15.000 / orang',
 -3.145487, 119.829984, NULL),

('Objek Wisata Buntu Sarira', 2, 1,
 'Sarira, Kec. Makale Utara, Kabupaten Tana Toraja, Sulawesi Selatan 91852',
 'Bukit dengan pemandangan panorama Kota Makale dan sekitarnya dari ketinggian, cocok untuk menikmati matahari terbit maupun terbenam.',
 'Parkir,Spot Foto',
 '00.00 - 24.00',
 'Gratis',
 -3.033488, 119.891484, NULL),

('Kolam Renang Alam Tilanga', 2, 1,
 'Sarira, Kec. Makale Utara, Kabupaten Tana Toraja, Sulawesi Selatan 91852',
 'Kolam renang alami yang terbentuk dari sumber mata air pegunungan, dikelilingi bebatuan dan pepohonan rindang, menjadi tempat wisata air favorit warga lokal maupun wisatawan.',
 'Parkir,Toilet,Ruang Ganti,Warung',
 '08.00 - 17.30',
 'Rp 15.000 / orang',
 -3.035238, 119.887266, NULL),

('Kuburan Batu Lemo', 1, 1,
 'Lemo, Kec. Makale Utara, Kabupaten Tana Toraja, Sulawesi Selatan 92119',
 'Situs kuburan batu (liang) khas Toraja yang dipahat langsung pada dinding tebing, dilengkapi patung Tau-tau yang berjejer merepresentasikan arwah leluhur.',
 'Parkir,Guide Lokal,Souvenir',
 '08.00 - 17.00',
 'Rp 15.000 / orang',
 -3.042512, 119.877328, NULL),

('Sa''pak Bayobayo Sangalla', 1, 1,
 'Sallualo, Kec. Sangalla Utara, Kabupaten Tana Toraja, Sulawesi Selatan 91817',
 'Situs budaya bersejarah di wilayah Sangalla yang berkaitan dengan tradisi dan silsilah adat masyarakat Toraja setempat.',
 'Parkir,Guide Lokal',
 '08.00 - 17.00',
 'Rp 10.000 / orang',
 -3.074788, 119.902422, NULL),

('Objek Wisata Ollon Toraja', 2, 1,
 'Ollon, Bau, Kec. Bonggakaradeng, Kabupaten Tana Toraja, Sulawesi Selatan 91872',
 'Kawasan wisata alam pegunungan di Kecamatan Bonggakaradeng dengan pemandangan lembah dan perbukitan yang masih asri.',
 'Parkir,Spot Foto',
 '08.00 - 17.00',
 'Rp 10.000 / orang',
 -3.229413, 119.667203, NULL),

('Tebing Romantis Ollon', 2, 1,
 'Ollon, Bau, Kec. Bonggakaradeng, Kabupaten Tana Toraja, Sulawesi Selatan 91872',
 'Formasi tebing alami dengan panorama lembah yang indah, menjadi spot favorit untuk fotografi dan menikmati matahari terbenam di kawasan Bonggakaradeng.',
 'Parkir,Spot Foto',
 '08.00 - 18.00',
 'Rp 10.000 / orang',
 -3.229413, 119.667203, NULL);

-- ---------------------------------------------------------
-- Tabel galeri_wisata
-- ---------------------------------------------------------
CREATE TABLE galeri_wisata (
  id_galeri  INT AUTO_INCREMENT PRIMARY KEY,
  id_wisata  INT NOT NULL,
  nama_foto  VARCHAR(255) NOT NULL,
  keterangan TEXT,
  CONSTRAINT fk_galeri_wisata FOREIGN KEY (id_wisata)
    REFERENCES wisata(id_wisata) ON DELETE CASCADE
) ENGINE=InnoDB;
