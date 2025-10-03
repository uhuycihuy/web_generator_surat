<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table = "user";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getByUsername($username) {
        $query = "SELECT no_id, username, password, role 
                FROM " . $this->table . " 
                WHERE username = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getById($no_id) {
        $query = "SELECT no_id, username, role, password FROM " . $this->table . " WHERE no_id = :no_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':no_id', $no_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAdminCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE role = 'admin'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    // Update data user (ubah username / password / role)
    public function updateUser($no_id, $username, $role, $password = null) {
        $allowedRoles = ['admin', 'user'];
        if (!in_array($role, $allowedRoles, true)) {
            throw new InvalidArgumentException('Role tidak valid');
        }

        if ($password !== null && $password !== '') {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE " . $this->table . " 
                      SET username = :username, role = :role, password = :password 
                      WHERE no_id = :no_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password', $hashedPassword);
        } else {
            $query = "UPDATE " . $this->table . " 
                      SET username = :username, role = :role 
                      WHERE no_id = :no_id";
            $stmt = $this->conn->prepare($query);
        }

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':no_id', $no_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Ambil semua user (khusus admin)
    public function getAll() {
        $query = "SELECT no_id, username, role FROM " . $this->table . " ORDER BY role DESC, username ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
 
    // Tambah user baru (hanya dipanggil oleh AdminController)
    public function addUser($username, $password, $role = 'user') {
        $allowedRoles = ['admin', 'user'];
        if (!in_array($role, $allowedRoles, true)) {
            throw new InvalidArgumentException('Role tidak valid');
        }

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
        $stmt->bindParam(':no_id', $no_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>  