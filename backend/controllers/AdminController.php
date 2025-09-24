<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Pegawai.php';
require_once __DIR__ . '/../helpers/utils.php';

class AdminController {
    private $db;
    private $userModel;
    private $pegawaiModel;

    public function __construct() {
        checkLogin();   // pastikan login dulu
        checkAdmin();   // pastikan role = admin

        $database = new Database();
        $this->db = $database->getConnection();

        $this->userModel = new User($this->db);
        $this->pegawaiModel = new Pegawai($this->db);
    }

    // === Manajemen User ===
    public function getAllUsers() {
        return $this->userModel->getAll();
    }

    public function addUser($username, $password, $role = 'user') {
        return $this->userModel->addUser($username, $password, $role);
    }

    public function deleteUser($no_id) {
        return $this->userModel->deleteUser($no_id);
    }

    // === Manajemen Pegawai ===
    public function getAllPegawai() {
        return $this->pegawaiModel->getAll()->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addPegawai($nip, $nama, $pangkat, $golongan, $jabatan) {
        return $this->pegawaiModel->addPegawai($nip, $nama, $pangkat, $golongan, $jabatan);
    }

    public function updatePegawai($nip, $nama, $pangkat, $golongan, $jabatan) {
        return $this->pegawaiModel->updatePegawai($nip, $nama, $pangkat, $golongan, $jabatan);
    }

    public function deletePegawai($nip) {
        return $this->pegawaiModel->deletePegawai($nip);
    }
}