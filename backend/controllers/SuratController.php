<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Pegawai.php';

class SuratController {
    private $db;
    private $pegawaiModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->pegawaiModel = new Pegawai($this->db);
    }
}
?>  