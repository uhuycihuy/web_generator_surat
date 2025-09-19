<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/utils.php';

class UserController {
    private $db;
    private $userModel;

    public function __construct() {
        checkLogin(); 
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new User($this->db);
    }

    // Update user (user hanya bisa update akunnya sendiri)
    public function updateUser($no_id, $username, $password = null) {
        $currentUser = currentUser();

        if ($currentUser['role'] !== 'admin' && $currentUser['id'] != $no_id) {
            echo "Akses ditolak!";
            exit;
        }

        return $this->userModel->updateUser($no_id, $username, $password);
    }
}
