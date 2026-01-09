<?php
/**
 * Authentication Helper Functions
 * 
 * Session-based auth checks and user management
 */

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in
 */
function checkLogin() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if logged-in user is Admin
 * 
 * @return bool True if admin
 */
function checkAdmin() {
    return checkLogin() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Get currently logged-in user
 * 
 * @return array|null User array or null if not logged in
 */
function currentUser() {
    if (!checkLogin()) {
        return null;
    }

    return [
        'user_id'  => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'name'     => $_SESSION['name'] ?? null,
        'email'    => $_SESSION['email'] ?? null,
        'role'     => $_SESSION['role'] ?? null,
        'nip'      => $_SESSION['nip'] ?? null,
    ];
}

/**
 * Require login; redirect if not
 * 
 * @param string $redirectPath Where to redirect if not logged in
 * @return void
 */
function requireLogin($redirectPath = 'login.php') {
    if (!checkLogin()) {
        redirectTo($redirectPath);
    }
}

/**
 * Require admin role; redirect/403 if not
 * 
 * @param string $redirectPath Where to redirect if not admin
 * @return void
 */
function requireAdmin($redirectPath = 'login.php') {
    if (!checkAdmin()) {
        header("HTTP/1.1 403 Forbidden");
        exit('Akses ditolak.');
    }
}

/**
 * Destroy session and logout user
 * 
 * @return void
 */
function logoutUser() {
    $_SESSION = [];
    session_destroy();
}
?>
