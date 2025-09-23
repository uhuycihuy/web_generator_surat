<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $db;
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/public/login.php', 'error', 'Invalid request method');
            return;
        }
        
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $this->redirect('/public/login.php', 'error', 'Username dan password harus diisi');
            return;
        }
        
        // Get user from database
        $user = $this->userModel->getByUsername($username);
        
        if (!$user || !password_verify($password, $user['password'])) {
            $this->redirect('/public/login.php', 'error', 'Username atau password salah');
            return;
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Redirect based on role
        if ($user['role'] === 'admin') {
            $this->redirect('/public/admin/dashboard.php', 'success', 'Login berhasil! Selamat datang Admin.');
        } else {
            $this->redirect('/public/staff/dashboard.php', 'success', 'Login berhasil! Selamat datang ' . $user['username'] . '.');
        }
    }
    
    public function logout() {
        // Destroy session
        session_destroy();
        $this->redirect('/public/login.php', 'success', 'Logout berhasil');
    }
    
    public function checkLogin() {
        return isset($_SESSION['user']);
    }
    
    public function requireLogin() {
        if (!$this->checkLogin()) {
            $this->redirect('/public/login.php', 'error', 'Silakan login terlebih dahulu');
            exit();
        }
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['username']);
    }
    
    public function redirectToLogin() {
        $baseUrl = $this->getBaseUrl();
        header("Location: " . $baseUrl . "/public/login.php");
        exit;
    }
    
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            $this->redirectToLogin();
        }
    }
    
    public function requireAdmin() {
        $this->requireAuth();
        if ($_SESSION['role'] !== 'admin') {
            $this->redirect('/public/staff/dashboard.php', 'error', 'Akses ditolak. Hanya admin yang diizinkan.');
            exit();
        }
    }
    
    public function requireStaff() {
        $this->requireAuth();
        if ($_SESSION['role'] !== 'staff') {
            $this->redirect('/public/admin/dashboard.php', 'error', 'Akses ditolak. Halaman ini khusus staff.');
            exit();
        }
    }
    
    private function redirect($path, $type = null, $message = null) {
        if ($type && $message) {
            $_SESSION[$type] = $message;
        }
        
        $baseUrl = $this->getBaseUrl();
        header('Location: ' . $baseUrl . $path);
        exit();
    }
    
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        
        // Extract base path up to /public directory
        $scriptPath = $_SERVER['SCRIPT_NAME'];
        $pathParts = explode('/', $scriptPath);
        
        // Find the index of 'public' in the path
        $publicIndex = array_search('public', $pathParts);
        if ($publicIndex !== false) {
            // Include 'public' in the base path
            $baseParts = array_slice($pathParts, 0, $publicIndex + 1);
            $basePath = implode('/', $baseParts);
        } else {
            // Fallback: assume we're already in the right directory
            $basePath = '/magang/web_generator_surat/public';
        }
        
        return $protocol . '://' . $host . $basePath;
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController();
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'login':
            $auth->login();
            break;
        case 'logout':
            $auth->logout();
            break;
        default:
            header('Location: ../public/login.php');
            break;
    }
} else {
    header('Location: ../public/login.php');
}
?>