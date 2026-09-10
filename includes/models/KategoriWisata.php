<?php
/**
 * Class KategoriWisata
 *
 * Merepresentasikan entitas kategori wisata (Wisata Budaya, Wisata Alam,
 * Wisata Religi, dst.) sesuai Class Diagram pada proposal penelitian.
 * Relasi: satu KategoriWisata memiliki banyak Wisata (1 - M).
 */
class KategoriWisata
{
    // ----- Atribut entitas (sesuai Class Diagram) -----
    public ?int $id_kategori = null;
    public string $nama_kategori = '';

    private mysqli $db;

    public function __construct(mysqli $db, array $row = [])
    {
        $this->db = $db;
        if (!empty($row)) {
            $this->id_kategori = isset($row['id_kategori']) ? (int)$row['id_kategori'] : null;
            $this->nama_kategori = $row['nama_kategori'] ?? '';
        }
    }

    public function getAll(): array
    {
        $result = $this->db->query("SELECT id_kategori, nama_kategori FROM kategori_wisata ORDER BY nama_kategori ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT id_kategori, nama_kategori FROM kategori_wisata WHERE id_kategori = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function countAll(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) c FROM kategori_wisata")->fetch_assoc()['c'];
    }

    /** tambahKategori() — menambahkan kategori wisata baru, mengembalikan id_kategori yang baru dibuat. */
    public function tambahKategori(string $namaKategori): int
    {
        $stmt = $this->db->prepare("INSERT INTO kategori_wisata (nama_kategori) VALUES (?)");
        $stmt->bind_param('s', $namaKategori);
        $stmt->execute();
        return $this->db->insert_id;
    }

    /**
     * hapusKategori() — menghapus kategori wisata. Menolak penghapusan apabila
     * kategori tersebut masih dipakai oleh data wisata (dijaga oleh Foreign Key
     * ON DELETE RESTRICT pada tabel wisata), agar integritas data tetap terjaga.
     */
    public function hapusKategori(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) c FROM wisata WHERE id_kategori = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $masihDipakai = (int)$stmt->get_result()->fetch_assoc()['c'] > 0;

        if ($masihDipakai) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM kategori_wisata WHERE id_kategori = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
