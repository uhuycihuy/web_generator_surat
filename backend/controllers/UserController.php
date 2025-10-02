<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Pegawai.php';
require_once __DIR__ . '/../helpers/utils.php';

class UserController {
    private $db;
    private $pegawaiModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->pegawaiModel = new Pegawai($this->db);
    }

    // === Ambil semua data pegawai ===
    public function getAllPegawai() {
        // return array associative agar mudah dipakai di view
        return $this->pegawaiModel->getAll()->fetchAll(PDO::FETCH_ASSOC);
    }

    // === Ambil pegawai berdasarkan NIP ===
    public function getPegawaiByNip($nip) {
        $stmt = $this->pegawaiModel->getByNip($nip);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
    }
    
    // === Ambil semua data user ===
    public function getAllUsers() {
        $query = "SELECT username, email, role, created_at FROM users ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
