<?php
/**
 * Class Wisata
 *
 * Merepresentasikan entitas objek wisata sesuai Class Diagram pada proposal
 * penelitian (Gambar 3.4): memiliki atribut data (id_wisata, nama_wisata,
 * latitude, longitude, deskripsi, dst.) sekaligus method hitungJarak() yang
 * menjalankan Proximity Analysis (Haversine Formula) dan getDetail().
 *
 * Selain sebagai entitas, class ini juga menyediakan operasi akses data
 * (CRUD) sehingga dapat dipakai langsung oleh seluruh halaman sistem.
 */
class Wisata
{
    // ----- Atribut entitas (sesuai Class Diagram) -----
    public ?int $id_wisata = null;
    public string $nama_wisata = '';
    public ?int $id_kategori = null;
    public ?string $kategori = null;   // nama kategori (hasil JOIN, untuk kemudahan tampilan)
    public ?int $id_admin = null;
    public string $alamat = '';
    public string $deskripsi = '';
    public string $fasilitas = '';
    public string $jam_buka = '';
    public string $tiket_masuk = '';
    public float $latitude = 0.0;
    public float $longitude = 0.0;
    public ?string $foto = null;

    private mysqli $db;

    public function __construct(mysqli $db, array $row = [])
    {
        $this->db = $db;
        if (!empty($row)) {
            $this->fill($row);
        }
    }

    /** Mengisi atribut entitas ini dari satu baris hasil query (array asosiatif). */
    private function fill(array $row): void
    {
        $this->id_wisata   = isset($row['id_wisata']) ? (int)$row['id_wisata'] : null;
        $this->nama_wisata = $row['nama_wisata'] ?? '';
        $this->id_kategori = isset($row['id_kategori']) ? (int)$row['id_kategori'] : null;
        $this->kategori     = $row['kategori'] ?? null;
        $this->id_admin     = isset($row['id_admin']) ? (int)$row['id_admin'] : null;
        $this->alamat       = $row['alamat'] ?? '';
        $this->deskripsi    = $row['deskripsi'] ?? '';
        $this->fasilitas    = $row['fasilitas'] ?? '';
        $this->jam_buka     = $row['jam_buka'] ?? '';
        $this->tiket_masuk  = $row['tiket_masuk'] ?? '';
        $this->latitude     = isset($row['latitude']) ? (float)$row['latitude'] : 0.0;
        $this->longitude    = isset($row['longitude']) ? (float)$row['longitude'] : 0.0;
        $this->foto         = $row['foto'] ?? null;
    }

    /**
     * getDetail() — mengembalikan detail lengkap wisata ini dalam bentuk array,
     * siap dipakai untuk ditampilkan pada halaman Detail Wisata.
     */
    public function getDetail(): array
    {
        return [
            'id_wisata' => $this->id_wisata,
            'nama_wisata' => $this->nama_wisata,
            'id_kategori' => $this->id_kategori,
            'kategori' => $this->kategori,
            'id_admin' => $this->id_admin,
            'alamat' => $this->alamat,
            'deskripsi' => $this->deskripsi,
            'fasilitas' => $this->fasilitas,
            'jam_buka' => $this->jam_buka,
            'tiket_masuk' => $this->tiket_masuk,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'foto' => $this->foto,
        ];
    }

    /**
     * hitungJarak() — method inti Proximity Analysis.
     * Menghitung jarak lurus (great-circle distance) dari koordinat wisata INI
     * (properti $this->latitude/$this->longitude) ke satu titik koordinat
     * pengguna, dalam satuan kilometer, menggunakan Haversine Formula.
     */
    public function hitungJarak(float $userLat, float $userLon): float
    {
        return self::haversine($userLat, $userLon, $this->latitude, $this->longitude);
    }

    /**
     * haversine() — implementasi murni rumus Haversine antara dua titik
     * koordinat bebas. Dipakai secara internal oleh hitungJarak() (instance)
     * maupun getTerdekat() (saat memproses banyak baris data sekaligus).
     */
    private static function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371; // jari-jari bumi dalam km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2 +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $R * $c;
    }

    /**
     * Query dasar dengan JOIN ke kategori_wisata, supaya hasilnya tetap
     * menyertakan nama kategori (field "kategori") meski di database
     * yang disimpan adalah id_kategori (Foreign Key).
     */
    private function baseSelect(): string
    {
        return "SELECT w.id_wisata, w.nama_wisata, w.id_kategori, k.nama_kategori AS kategori,
                        w.id_admin, w.alamat, w.deskripsi, w.fasilitas, w.jam_buka, w.tiket_masuk,
                        w.latitude, w.longitude, w.foto, w.created_at, w.updated_at
                 FROM wisata w
                 INNER JOIN kategori_wisata k ON w.id_kategori = k.id_kategori";
    }

    /**
     * Mengambil seluruh data wisata, dengan filter opsional (kategori/keyword)
     * dan limit opsional. Mengembalikan array asosiatif (dipakai oleh
     * halaman-halaman yang menampilkan banyak wisata sekaligus).
     */
    public function getAll(array $filters = []): array
    {
        $sql = $this->baseSelect() . " WHERE 1=1";
        $params = [];
        $types = '';

        if (!empty($filters['kategori'])) {
            $sql .= " AND k.nama_kategori = ?";
            $params[] = $filters['kategori'];
            $types .= 's';
        }
        if (!empty($filters['keyword'])) {
            $sql .= " AND w.nama_wisata LIKE ?";
            $params[] = '%' . $filters['keyword'] . '%';
            $types .= 's';
        }

        $sql .= isset($filters['order_by_terbaru']) && $filters['order_by_terbaru']
            ? " ORDER BY w.created_at DESC"
            : " ORDER BY w.nama_wisata ASC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        $stmt = $this->db->prepare($sql);
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Mengambil satu data wisata sebagai array asosiatif (dipakai oleh form edit, dsb). */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare($this->baseSelect() . " WHERE w.id_wisata = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    /**
     * Mengambil satu data wisata sebagai OBJECT Wisata yang sudah terisi
     * (entity instance sesungguhnya) — dipakai pada halaman Detail Wisata
     * agar dapat langsung memanggil $wisata->hitungJarak() / $wisata->getDetail().
     */
    public function getByIdAsObject(int $id): ?Wisata
    {
        $row = $this->getById($id);
        return $row ? new Wisata($this->db, $row) : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO wisata
                (nama_wisata, id_kategori, id_admin, alamat, deskripsi, fasilitas, jam_buka, tiket_masuk, latitude, longitude, foto)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)"
        );
        $stmt->bind_param(
            'siisssssdds',
            $data['nama_wisata'], $data['id_kategori'], $data['id_admin'], $data['alamat'], $data['deskripsi'],
            $data['fasilitas'], $data['jam_buka'], $data['tiket_masuk'], $data['latitude'], $data['longitude'], $data['foto']
        );
        $stmt->execute();
        return $this->db->insert_id;
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE wisata SET nama_wisata=?, id_kategori=?, alamat=?, deskripsi=?, fasilitas=?,
                jam_buka=?, tiket_masuk=?, latitude=?, longitude=?, foto=?
             WHERE id_wisata=?"
        );
        $stmt->bind_param(
            'sisssssddsi',
            $data['nama_wisata'], $data['id_kategori'], $data['alamat'], $data['deskripsi'], $data['fasilitas'],
            $data['jam_buka'], $data['tiket_masuk'], $data['latitude'], $data['longitude'], $data['foto'], $id
        );
        return $stmt->execute();
    }

    /** Menghapus data wisata, mengembalikan nama file foto sampul (untuk dibersihkan di sisi pemanggil). */
    public function delete(int $id): ?string
    {
        $stmt = $this->db->prepare("SELECT foto FROM wisata WHERE id_wisata = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        $stmt = $this->db->prepare("DELETE FROM wisata WHERE id_wisata = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        return $row['foto'] ?? null;
    }

    public function countAll(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) c FROM wisata")->fetch_assoc()['c'];
    }

    /** Statistik jumlah wisata per kategori, dipakai untuk donut chart dashboard admin. */
    public function countByKategori(): array
    {
        $sql = "SELECT k.nama_kategori AS kategori, COUNT(w.id_wisata) AS jumlah
                FROM kategori_wisata k
                LEFT JOIN wisata w ON w.id_kategori = k.id_kategori
                GROUP BY k.id_kategori, k.nama_kategori
                HAVING jumlah > 0
                ORDER BY jumlah DESC";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * getTerdekat() — menjalankan Proximity Analysis dari satu titik koordinat pengguna
     * terhadap seluruh data wisata di database, mengembalikan array terurut dari
     * yang terdekat, masing-masing disertai field "jarak" (km).
     * Apabila $radiusKm diisi, hanya wisata dalam radius tersebut yang dikembalikan
     * (Buffer Analysis).
     */
    public function getTerdekat(float $userLat, float $userLon, ?float $radiusKm = null): array
    {
        $semuaWisata = $this->getAll();

        foreach ($semuaWisata as &$w) {
            $w['jarak'] = self::haversine($userLat, $userLon, (float)$w['latitude'], (float)$w['longitude']);
        }
        unset($w);

        if ($radiusKm !== null) {
            $semuaWisata = array_values(array_filter($semuaWisata, function ($w) use ($radiusKm) {
                return $w['jarak'] <= $radiusKm;
            }));
        }

        usort($semuaWisata, function ($a, $b) {
            return $a['jarak'] <=> $b['jarak'];
        });

        return $semuaWisata;
    }
}
