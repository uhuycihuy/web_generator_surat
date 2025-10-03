<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/utils.php';

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
            redirectTo('generator_surat');
            return;
        } else {
            $_SESSION['error'] = "Login gagal! Username atau password salah.";
            redirectTo('login');
            return;
        }
    }

    // Proses logout
    public function logout() {
        session_unset();
        session_destroy();
        redirectTo('login?status=logout');
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
if (isset($_GET['action']) || isset($_POST['action'])) {
    $controller = new AuthController();
    $controller->handleRequest();
}

