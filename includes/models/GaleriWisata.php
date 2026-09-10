<?php
/**
 * Class GaleriWisata
 *
 * Merepresentasikan entitas galeri foto untuk setiap objek wisata sesuai
 * Class Diagram pada proposal penelitian. Relasi: satu Wisata memiliki
 * banyak GaleriWisata (1 - M), dihubungkan lewat Foreign Key id_wisata
 * dengan ON DELETE CASCADE.
 */
class GaleriWisata
{
    // ----- Atribut entitas (sesuai Class Diagram) -----
    public ?int $id_galeri = null;
    public ?int $id_wisata = null;
    public ?string $nama_foto = null;
    public ?string $keterangan = null;

    private mysqli $db;

    public function __construct(mysqli $db, array $row = [])
    {
        $this->db = $db;
        if (!empty($row)) {
            $this->id_galeri = isset($row['id_galeri']) ? (int)$row['id_galeri'] : null;
            $this->id_wisata = isset($row['id_wisata']) ? (int)$row['id_wisata'] : null;
            $this->nama_foto = $row['nama_foto'] ?? null;
            $this->keterangan = $row['keterangan'] ?? null;
        }
    }

    public function getByWisataId(int $idWisata): array
    {
        $stmt = $this->db->prepare(
            "SELECT id_galeri, id_wisata, nama_foto, keterangan
             FROM galeri_wisata WHERE id_wisata = ? ORDER BY id_galeri ASC"
        );
        $stmt->bind_param('i', $idWisata);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** tambahFoto() — menambahkan satu foto baru ke galeri sebuah wisata. */
    public function tambahFoto(int $idWisata, string $namaFoto, string $keterangan): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO galeri_wisata (id_wisata, nama_foto, keterangan) VALUES (?, ?, ?)"
        );
        $stmt->bind_param('iss', $idWisata, $namaFoto, $keterangan);
        $stmt->execute();
        return $this->db->insert_id;
    }

    /** Alias dari tambahFoto() dengan urutan parameter lama, tetap disediakan untuk kompatibilitas. */
    public function create(int $idWisata, string $namaFoto, string $keterangan): int
    {
        return $this->tambahFoto($idWisata, $namaFoto, $keterangan);
    }

    /** hapusFoto() — menghapus satu foto galeri, mengembalikan nama file-nya untuk dibersihkan di sisi pemanggil. */
    public function hapusFoto(int $idGaleri): ?string
    {
        $stmt = $this->db->prepare("SELECT nama_foto FROM galeri_wisata WHERE id_galeri = ?");
        $stmt->bind_param('i', $idGaleri);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        $stmt = $this->db->prepare("DELETE FROM galeri_wisata WHERE id_galeri = ?");
        $stmt->bind_param('i', $idGaleri);
        $stmt->execute();

        return $row['nama_foto'] ?? null;
    }

    /** Alias dari hapusFoto(), tetap disediakan untuk kompatibilitas. */
    public function deleteById(int $idGaleri): ?string
    {
        return $this->hapusFoto($idGaleri);
    }

    /** Mengambil semua nama file foto milik satu wisata (dipakai sebelum wisata induk dihapus). */
    public function getFileNamesByWisataId(int $idWisata): array
    {
        $stmt = $this->db->prepare("SELECT nama_foto FROM galeri_wisata WHERE id_wisata = ?");
        $stmt->bind_param('i', $idWisata);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        return array_column($rows, 'nama_foto');
    }
}
