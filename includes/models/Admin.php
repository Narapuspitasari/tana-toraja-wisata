<?php
/**
 * Class Admin
 *
 * Merepresentasikan entitas pengguna admin sistem sesuai Class Diagram pada
 * proposal penelitian, termasuk method login() dan kelolaWisata(). Relasi:
 * satu Admin dapat mengelola banyak Wisata (1 - M), dihubungkan lewat
 * Foreign Key id_admin pada tabel wisata.
 */
class Admin
{
    // ----- Atribut entitas (sesuai Class Diagram) -----
    public ?int $id_admin = null;
    public string $username = '';
    public string $password = '';
    public ?string $nama_lengkap = null;

    private mysqli $db;

    public function __construct(mysqli $db, array $row = [])
    {
        $this->db = $db;
        if (!empty($row)) {
            $this->fill($row);
        }
    }

    private function fill(array $row): void
    {
        $this->id_admin = isset($row['id_admin']) ? (int)$row['id_admin'] : null;
        $this->username = $row['username'] ?? '';
        $this->password = $row['password'] ?? '';
        $this->nama_lengkap = $row['nama_lengkap'] ?? null;
    }

    /**
     * login() — melakukan autentikasi username & password terhadap data admin
     * di database. Mengisi atribut entitas ini ($this) apabila berhasil, dan
     * mengembalikan true/false sebagai hasil autentikasi.
     */
    public function login(string $username, string $plainPassword): bool
    {
        $row = $this->findByUsername($username);

        if ($row && password_verify($plainPassword, $row['password'])) {
            $this->fill($row);
            return true;
        }

        return false;
    }

    /**
     * kelolaWisata() — facade sederhana untuk operasi kelola data wisata
     * (tambah/ubah/hapus) oleh admin yang sedang login, mendelegasikan ke
     * class Wisata sesuai relasi "Admin mengelola Wisata" pada Class Diagram.
     *
     * $aksi: 'tambah' | 'ubah' | 'hapus'
     */
    public function kelolaWisata(Wisata $wisataModel, string $aksi, array $data = [], ?int $idWisata = null)
    {
        switch ($aksi) {
            case 'tambah':
                $data['id_admin'] = $this->id_admin;
                return $wisataModel->create($data);
            case 'ubah':
                return $wisataModel->update($idWisata, $data);
            case 'hapus':
                return $wisataModel->delete($idWisata);
            default:
                return false;
        }
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare("SELECT id_admin, username, password, nama_lengkap FROM admin WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT id_admin, username, password, nama_lengkap FROM admin WHERE id_admin = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    /** Memverifikasi password teks biasa terhadap hash bcrypt yang tersimpan. */
    public function verifyPassword(string $plainPassword, string $hash): bool
    {
        return password_verify($plainPassword, $hash);
    }

    public function updatePassword(int $id, string $newPlainPassword): bool
    {
        $hash = password_hash($newPlainPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE admin SET password = ? WHERE id_admin = ?");
        $stmt->bind_param('si', $hash, $id);
        return $stmt->execute();
    }
}
