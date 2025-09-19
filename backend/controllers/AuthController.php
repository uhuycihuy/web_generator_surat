<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $db;
    private $userModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new User($this->db);
    }

    public function login($username, $password) {
        $user = $this->userModel->getByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['no_id'],
                'username' => $user['username'],
                'role' => $user['role']   
            ];
            header("Location: dashboard.php");
            exit;
        } else {
            echo "Login gagal!";
        }
    }
}

?>