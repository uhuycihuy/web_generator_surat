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

    // Proses login
    public function login($username, $password) {
        $user = $this->userModel->getByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id'       => $user['no_id'],
                'username' => $user['username'],
                'role'     => $user['role']
            ];
            header("Location: dashboard.php");
            exit;
        } else {
            echo "Login gagal! Username atau password salah.";
        }
    }

    // Proses logout
    public function logout() {
        session_destroy();
        header("Location: login.php");
        exit;
    }

    // Untuk handle request action=login / logout
    public function handleRequest() {
        $action = $_GET['action'] ?? $_POST['action'] ?? '';

        switch ($action) {
            case 'login':
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                $this->login($username, $password);
                break;
            case 'logout':
                $this->logout();
                break;
            default:
                echo "Action tidak dikenali.";
        }
    }
}

// Auto-handle request
if (!empty($_POST) || !empty($_GET)) {
    $controller = new AuthController();
    $controller->handleRequest();
}
