<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table = "user";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($username, $password) {
        $query = "SELECT no_id, username, password, role 
                  FROM " . $this->table . " 
                  WHERE username = ? 
                  LIMIT 1";        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }

        return false;
    }

    // Update data user (ubah username / password)
    public function updateUser($no_id, $username, $password = null) {
        if ($password) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE " . $this->table . " 
                      SET username = :username, password = :password 
                      WHERE no_id = :no_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password', $hashedPassword);
        } else {
            $query = "UPDATE " . $this->table . " 
                      SET username = :username 
                      WHERE no_id = :no_id";
            $stmt = $this->conn->prepare($query);
        }

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':no_id', $no_id);
        return $stmt->execute();
    }

    // Ambil semua user (khusus admin)
    public function getAll() {
        $query = "SELECT no_id, username, role FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
 
    // Tambah user baru (hanya dipanggil oleh AdminController)
    public function addUser($username, $password, $role = 'user') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO " . $this->table . " (username, password, role) 
                  VALUES (:username, :password, :role)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);

        return $stmt->execute();
    }

    // Hapus user (khusus admin)
    public function deleteUser($no_id) {
        $query = "DELETE FROM " . $this->table . " WHERE no_id = :no_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':no_id', $no_id);
        return $stmt->execute();
    }
}
?>  