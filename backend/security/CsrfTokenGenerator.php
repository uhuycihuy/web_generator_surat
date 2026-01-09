<?php
/**
 * CSRF Token Generator and Validator
 * 
 * Provides token generation, storage, and validation for Cross-Site Request Forgery protection
 */

class CsrfTokenGenerator {
    
    const TOKEN_NAME = 'csrf_token';
    const TOKEN_TTL = 3600; // 1 hour
    
    /**
     * Initialize CSRF protection
     * Must be called early in request lifecycle
     */
    public static function initialize() {
        if (!isset($_SESSION)) {
            session_start();
        }
    }
    
    /**
     * Generate a new CSRF token
     * 
     * @return string Generated token
     */
    public static function generate() {
        self::initialize();
        
        // Generate random bytes
        $token = bin2hex(random_bytes(32));
        
        // Store in session with timestamp
        $_SESSION[self::TOKEN_NAME] = [
            'token' => $token,
            'created_at' => time(),
            'ip' => self::getClientIP(),
        ];
        
        return $token;
    }
    
    /**
     * Get current CSRF token (generate if not exists)
     * 
     * @return string Current token
     */
    public static function get() {
        self::initialize();
        
        if (!isset($_SESSION[self::TOKEN_NAME]) || self::isExpired()) {
            return self::generate();
        }
        
        return $_SESSION[self::TOKEN_NAME]['token'];
    }
    
    /**
     * Validate CSRF token from request
     * Checks token value, expiration, and IP address
     * 
     * @param string $token Token to validate (from POST data)
     * @param bool $checkIp Whether to verify client IP (optional)
     * @return bool True if valid
     */
    public static function validate($token, $checkIp = true) {
        self::initialize();
        
        // Check if token exists in session
        if (!isset($_SESSION[self::TOKEN_NAME])) {
            return false;
        }
        
        $stored = $_SESSION[self::TOKEN_NAME];
        
        // Verify token value using hash_equals to prevent timing attacks
        if (!hash_equals($stored['token'], $token)) {
            return false;
        }
        
        // Check expiration
        if (self::isExpired()) {
            return false;
        }
        
        // Optionally verify IP address
        if ($checkIp && isset($stored['ip'])) {
            if ($stored['ip'] !== self::getClientIP()) {
                // Log potential CSRF attempt
                error_log("CSRF: IP mismatch. Stored: {$stored['ip']}, Current: " . self::getClientIP());
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validate and consume token
     * After validation, token is regenerated for next request
     * 
     * @param string $token Token to validate
     * @param bool $checkIp Whether to verify client IP
     * @return bool True if valid
     */
    public static function validateAndRefresh($token, $checkIp = true) {
        if (!self::validate($token, $checkIp)) {
            return false;
        }
        
        // Generate new token for next request
        self::generate();
        
        return true;
    }
    
    /**
     * Check if stored token has expired
     * 
     * @return bool True if expired
     */
    public static function isExpired() {
        if (!isset($_SESSION[self::TOKEN_NAME]['created_at'])) {
            return true;
        }
        
        $age = time() - $_SESSION[self::TOKEN_NAME]['created_at'];
        return $age > self::TOKEN_TTL;
    }
    
    /**
     * Clear CSRF token from session
     * 
     * @return void
     */
    public static function clear() {
        self::initialize();
        unset($_SESSION[self::TOKEN_NAME]);
    }
    
    /**
     * Get client IP address
     * Handles various proxy scenarios
     * 
     * @return string Client IP
     */
    private static function getClientIP() {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            // Cloudflare
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Proxy (get first IP)
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        
        return '0.0.0.0';
    }
    
    /**
     * Regenerate all session tokens
     * Call after successful login for security
     * 
     * @return string New CSRF token
     */
    public static function regenerate() {
        self::initialize();
        
        // Regenerate PHP session ID
        session_regenerate_id(true);
        
        // Generate new CSRF token
        return self::generate();
    }
    
    /**
     * Get token value from POST data
     * Checks multiple possible locations (POST, JSON body, custom header)
     * 
     * @return string|null Token or null if not found
     */
    public static function getFromRequest() {
        // Check POST data
        if (!empty($_POST[self::TOKEN_NAME])) {
            return $_POST[self::TOKEN_NAME];
        }
        
        // Check JSON body (for AJAX requests)
        if (!empty($_POST['_token'])) {
            return $_POST['_token'];
        }
        
        // Check custom header (X-CSRF-Token)
        if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            return $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
        
        return null;
    }
    
    /**
     * Middleware function to validate CSRF token on POST requests
     * Return true if valid or if request method doesn't require validation
     * 
     * @param string $method Request method to check
     * @return bool True if valid or bypass conditions met
     */
    public static function checkRequest($method = null) {
        $method = $method ?? $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Only validate on unsafe methods
        if (!in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return true;
        }
        
        // Skip CSRF for API endpoints (check Accept header)
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            // Could implement token-based API auth here
        }
        
        $token = self::getFromRequest();
        if (empty($token)) {
            return false;
        }
        
        return self::validate($token);
    }
}
?>
