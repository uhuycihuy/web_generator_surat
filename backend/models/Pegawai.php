<?php
require_once __DIR__ . '/../config/database.php';

class Pegawai {
    private $conn;
    private $table = "pegawai";

    public function  __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT nip, nama_pegawai, pangkat, golongan, jabatan 
                  FROM " . $this->table . " 
                  ORDER BY nama_pegawai ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getByNip($nip) {
        $query = "SELECT nip, nama_pegawai, pangkat, golongan, jabatan 
                  FROM " . $this->table . " 
                  WHERE nip = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $nip);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    } 
}
?>  