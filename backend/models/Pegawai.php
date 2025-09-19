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

    //Tambah Pegawai (Khusu admin)
    public function addPegawai($nip, $nama, $pangkat, $golongan, $jabatan) {
        $query = "INSERT INTO " . $this->table . " 
                  (nip, nama_pegawai, pangkat, golongan, jabatan) 
                  VALUES (:nip, :nama, :pangkat, :golongan, :jabatan)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nip', $nip);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':pangkat', $pangkat);
        $stmt->bindParam(':golongan', $golongan);
        $stmt->bindParam(':jabatan', $jabatan);

        return $stmt->execute();
    }

    //Update data Pegawai (Khusus Admin)
    public function updatePegawai($nip, $nama, $pangkat, $golongan, $jabatan) {
        $query = "UPDATE " . $this->table . " 
                  SET nama_pegawai = :nama, pangkat = :pangkat, 
                      golongan = :golongan, jabatan = :jabatan
                  WHERE nip = :nip";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nip', $nip);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':pangkat', $pangkat);
        $stmt->bindParam(':golongan', $golongan);
        $stmt->bindParam(':jabatan', $jabatan);

        return $stmt->execute();
    }

    //Delete Data Pegawai (Kusus Admin)
    public function deletePegawai($nip) {
        $query = "DELETE FROM " . $this->table . " WHERE nip = :nip";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nip', $nip);

        return $stmt->execute();
    }
}
?>  