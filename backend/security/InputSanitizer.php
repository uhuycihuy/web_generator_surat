<?php
/**
 * Input Sanitization and Security Utilities
 * 
 * Provides multi-layered input sanitization and output encoding
 */

class InputSanitizer {
    
    /**
     * Sanitize string input
     * Removes HTML tags, trims whitespace
     * 
     * @param string $input Input to sanitize
     * @param bool $allowHtml Allow some safe HTML tags
     * @return string Sanitized string
     */
    public static function string($input, $allowHtml = false) {
        if (!is_string($input)) {
            return '';
        }
        
        $input = trim($input);
        
        if (!$allowHtml) {
            // Remove all HTML tags
            $input = strip_tags($input);
        } else {
            // Allow only safe tags
            $allowedTags = '<b><i><u><br><p><strong><em><span>';
            $input = strip_tags($input, $allowedTags);
        }
        
        return $input;
    }
    
    /**
     * Sanitize integer input
     * 
     * @param mixed $input Input to sanitize
     * @return int Integer value
     */
    public static function integer($input) {
        return (int)filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }
    
    /**
     * Sanitize email address
     * 
     * @param string $input Email to sanitize
     * @return string Sanitized email
     */
    public static function email($input) {
        return filter_var($input, FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Sanitize URL
     * 
     * @param string $input URL to sanitize
     * @return string Sanitized URL
     */
    public static function url($input) {
        return filter_var($input, FILTER_SANITIZE_URL);
    }
    
    /**
     * Sanitize phone number (keep digits and +)
     * 
     * @param string $input Phone to sanitize
     * @return string Sanitized phone
     */
    public static function phone($input) {
        return preg_replace('/[^0-9+\-().\s]/', '', $input);
    }
    
    /**
     * Sanitize date input (must be valid format)
     * 
     * @param string $input Date to sanitize
     * @param string $format Expected format (default: Y-m-d)
     * @return string|null Sanitized date or null if invalid
     */
    public static function date($input, $format = 'Y-m-d') {
        $date = DateTime::createFromFormat($format, $input);
        
        if ($date === false) {
            return null;
        }
        
        return $date->format($format);
    }
    
    /**
     * Sanitize array input recursively
     * 
     * @param array $input Array to sanitize
     * @param array $rules Rules for each key (optional)
     * @return array Sanitized array
     */
    public static function array($input, $rules = []) {
        if (!is_array($input)) {
            return [];
        }
        
        $sanitized = [];
        
        foreach ($input as $key => $value) {
            $key = self::string($key); // Sanitize key
            
            // Apply specific rule if exists
            if (isset($rules[$key])) {
                $rule = $rules[$key];
                
                switch ($rule) {
                    case 'int':
                    case 'integer':
                        $sanitized[$key] = self::integer($value);
                        break;
                    case 'email':
                        $sanitized[$key] = self::email($value);
                        break;
                    case 'url':
                        $sanitized[$key] = self::url($value);
                        break;
                    case 'phone':
                        $sanitized[$key] = self::phone($value);
                        break;
                    case 'array':
                        $sanitized[$key] = self::array($value);
                        break;
                    default:
                        $sanitized[$key] = self::string($value);
                }
            } elseif (is_array($value)) {
                // Recursively sanitize nested arrays
                $sanitized[$key] = self::array($value, $rules);
            } else {
                // Default: sanitize as string
                $sanitized[$key] = self::string($value);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Escape output for safe HTML display
     * Prevents XSS attacks
     * 
     * @param string $output Output to escape
     * @param string $context Context (html, attr, url, js)
     * @return string Escaped output
     */
    public static function escape($output, $context = 'html') {
        if (empty($output)) {
            return '';
        }
        
        switch ($context) {
            case 'html':
                return htmlspecialchars($output, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            
            case 'attr':
                // Escape for HTML attributes
                return htmlspecialchars($output, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            
            case 'url':
                // Escape for URLs
                return rawurlencode($output);
            
            case 'js':
                // Escape for JavaScript (simple implementation)
                return addslashes(json_encode($output));
            
            default:
                return htmlspecialchars($output, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }
    
    /**
     * Validate email format
     * 
     * @param string $email Email to validate
     * @return bool True if valid
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate URL format
     * 
     * @param string $url URL to validate
     * @return bool True if valid
     */
    public static function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Validate IP address
     * 
     * @param string $ip IP to validate
     * @return bool True if valid
     */
    public static function validateIP($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }
    
    /**
     * Check for SQL injection patterns
     * (Basic check - use prepared statements primarily)
     * 
     * @param string $input Input to check
     * @return bool True if suspicious patterns found
     */
    public static function hasSqlInjectionPatterns($input) {
        $patterns = [
            '/(\bOR\b|\bAND\b).*=.*["\'].*["\']/',
            '/.*;\s*DROP\s+TABLE\b/',
            '/.*;\s*DELETE\s+FROM\b/',
            '/.*UNION.*SELECT\b/',
            '/xp_|sp_/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input, matches: $matches, flags: PREG_PATTERN_ORDER)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check for XSS patterns
     * (Basic check - use proper output encoding)
     * 
     * @param string $input Input to check
     * @return bool True if suspicious patterns found
     */
    public static function hasXssPatterns($input) {
        $patterns = [
            '/<script[^>]*>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get sanitized POST data
     * 
     * @param string $key Key to retrieve (or null for all)
     * @param string|null $type Type of sanitization
     * @return mixed Sanitized value or array
     */
    public static function getPost($key = null, $type = null) {
        if ($key === null) {
            return self::array($_POST);
        }
        
        if (!isset($_POST[$key])) {
            return null;
        }
        
        $value = $_POST[$key];
        
        if ($type) {
            switch ($type) {
                case 'int':
                case 'integer':
                    return self::integer($value);
                case 'email':
                    return self::email($value);
                case 'url':
                    return self::url($value);
                case 'phone':
                    return self::phone($value);
                case 'date':
                    return self::date($value);
                default:
                    return self::string($value);
            }
        }
        
        return self::string($value);
    }
    
    /**
     * Get sanitized GET data
     * 
     * @param string $key Key to retrieve (or null for all)
     * @param string|null $type Type of sanitization
     * @return mixed Sanitized value or array
     */
    public static function getQuery($key = null, $type = null) {
        if ($key === null) {
            return self::array($_GET);
        }
        
        if (!isset($_GET[$key])) {
            return null;
        }
        
        $value = $_GET[$key];
        
        if ($type) {
            switch ($type) {
                case 'int':
                case 'integer':
                    return self::integer($value);
                case 'email':
                    return self::email($value);
                case 'url':
                    return self::url($value);
                case 'phone':
                    return self::phone($value);
                case 'date':
                    return self::date($value);
                default:
                    return self::string($value);
            }
        }
        
        return self::string($value);
    }
}
?>
