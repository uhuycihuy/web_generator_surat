<?php
/**
 * Authentication Service
 * 
 * Handles user authentication, validation, session management
 */

class AuthService {
    
    private $db;
    private $validator;
    
    /**
     * Initialize service
     * 
     * @param PDO $db Database connection
     * @param RequestValidator $validator Input validator
     */
    public function __construct($db, $validator = null) {
        $this->db = $db;
        $this->validator = $validator;
    }
    
    /**
     * Authenticate user by username and password
     * 
     * @param string $username Username
     * @param string $password Password (plain text)
     * @return array|null User data if authenticated, null otherwise
     */
    public function authenticate($username, $password) {
        if (empty($username) || empty($password)) {
            return null;
        }
        
        try {
            $query = "SELECT id, username, name, email, role, nip 
                      FROM user 
                      WHERE username = ? 
                      LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$username]);
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return null;
            }
            
            // Verify password (assuming password_verify if stored with password_hash)
            // For now, check direct match (update to use password_hash/password_verify in production)
            // TODO: Migrate to proper password hashing
            
            // If you're using password_hash:
            // if (password_verify($password, $user['password'])) {
            
            // For current implementation (direct comparison):
            if ($this->verifyPassword($password, $user)) {
                unset($user['password']); // Don't return password
                return $user;
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Authentication error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Verify password against user account
     * 
     * @param string $password Password to verify
     * @param array $user User data from database
     * @return bool True if password valid
     */
    private function verifyPassword($password, $user) {
        // TODO: Implement proper password verification
        // For now: simple comparison (improve this!)
        
        // Query full password field if needed
        if (isset($user['password'])) {
            return hash_equals($user['password'], md5($password));
        }
        
        // Fallback: query password field explicitly
        try {
            $query = "SELECT password FROM user WHERE id = ? LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$user['id']]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                return false;
            }
            
            // Use hash_equals to prevent timing attacks
            return hash_equals($result['password'], md5($password));
        } catch (PDOException $e) {
            error_log("Error verifying password: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create user session
     * 
     * @param array $user User data
     * @return bool Success status
     */
    public function createSession($user) {
        if (empty($user) || !is_array($user)) {
            return false;
        }
        
        try {
            // Store user data in session
            $_SESSION['user_id'] = $user['id'] ?? null;
            $_SESSION['username'] = $user['username'] ?? null;
            $_SESSION['name'] = $user['name'] ?? null;
            $_SESSION['email'] = $user['email'] ?? null;
            $_SESSION['role'] = $user['role'] ?? null;
            $_SESSION['nip'] = $user['nip'] ?? null;
            
            // Legacy: also set 'user' array for backward compatibility
            $_SESSION['user'] = [
                'id' => $user['id'] ?? null,
                'username' => $user['username'] ?? null,
                'name' => $user['name'] ?? null,
                'email' => $user['email'] ?? null,
                'role' => $user['role'] ?? null,
                'nip' => $user['nip'] ?? null,
            ];
            
            // Set session timestamp for timeout tracking
            $_SESSION['login_time'] = time();
            
            return true;
        } catch (Exception $e) {
            error_log("Error creating session: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Destroy user session (logout)
     * 
     * @return bool Success status
     */
    public function destroySession() {
        try {
            $_SESSION = [];
            session_destroy();
            return true;
        } catch (Exception $e) {
            error_log("Error destroying session: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user is authenticated
     * 
     * @return bool True if authenticated
     */
    public function isAuthenticated() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Check if user has admin role
     * 
     * @return bool True if admin
     */
    public function isAdmin() {
        return $this->isAuthenticated() && 
               isset($_SESSION['role']) && 
               $_SESSION['role'] === 'admin';
    }
    
    /**
     * Get current authenticated user
     * 
     * @return array|null Current user data or null
     */
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'name' => $_SESSION['name'] ?? null,
            'email' => $_SESSION['email'] ?? null,
            'role' => $_SESSION['role'] ?? null,
            'nip' => $_SESSION['nip'] ?? null,
        ];
    }
    
    /**
     * Get user by ID
     * 
     * @param int $id User ID
     * @return array|null User data or null
     */
    public function getUserById($id) {
        try {
            $query = "SELECT id, username, name, email, role, nip 
                      FROM user 
                      WHERE id = ? 
                      LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching user: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all users
     * 
     * @return array List of users
     */
    public function getAllUsers() {
        try {
            $query = "SELECT id, username, name, email, role, nip, created_at 
                      FROM user 
                      ORDER BY username ASC";
            
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching users: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validate credentials format
     * 
     * @param string $username Username to validate
     * @param string $password Password to validate
     * @return array Empty array if valid, array of errors otherwise
     */
    public function validateCredentials($username, $password) {
        $errors = [];
        
        if (empty(trim($username))) {
            $errors['username'] = 'Username diperlukan';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Password diperlukan';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Password minimal 6 karakter';
        }
        
        return $errors;
    }
    
    /**
     * Check if session has expired
     * 
     * @param int $timeout Session timeout in seconds (default: 1 hour)
     * @return bool True if expired
     */
    public function hasSessionExpired($timeout = 3600) {
        if (!isset($_SESSION['login_time'])) {
            return true;
        }
        
        $elapsed = time() - $_SESSION['login_time'];
        return $elapsed > $timeout;
    }
}
?>
