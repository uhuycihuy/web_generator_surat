<?php
/**
 * Common Helper Functions
 * 
 * Miscellaneous utilities, link formatting
 */

/**
 * Sanitize and escape string for output
 * 
 * @param string $text Text to escape
 * @return string Escaped text
 */
function escapeOutput($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize email address
 * 
 * @param string $email Email to sanitize
 * @return string|false Sanitized email or false
 */
function sanitizeEmail($email) {
    return filter_var($email, FILTER_SANITIZE_EMAIL);
}

/**
 * Validate email format
 * 
 * @param string $email Email to validate
 * @return bool True if valid
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitize phone number (remove non-digits)
 * 
 * @param string $phone Phone number
 * @return string Digits only
 */
function sanitizePhoneNumber($phone) {
    return preg_replace('/[^0-9+\-()]/', '', $phone ?? '');
}

/**
 * Convert URLs in text to clickable links
 * Handles various URL formats
 * 
 * @param string $text Input text
 * @return string Text with URLs converted to <a> tags
 */
function linkify($text) {
    if (empty($text)) {
        return '';
    }

    // Match HTTP(S) URLs
    $text = preg_replace_callback(
        '/(https?:\/\/[^\s<>"\)]+)/i',
        function ($matches) {
            $url = $matches[1];
            // Remove trailing punctuation
            $url = rtrim($url, '.,;:!?\'")');
            return '<a href="' . escapeOutput($url) . '" target="_blank" rel="noopener noreferrer">' . escapeOutput($url) . '</a>';
        },
        $text
    );

    // Match www. URLs
    $text = preg_replace_callback(
        '/(www\.[^\s<>"\)]+)/i',
        function ($matches) {
            $url = $matches[1];
            $url = rtrim($url, '.,;:!?\'")');
            return '<a href="http://' . escapeOutput($url) . '" target="_blank" rel="noopener noreferrer">' . escapeOutput($url) . '</a>';
        },
        $text
    );

    // Match email addresses
    $text = preg_replace_callback(
        '/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i',
        function ($matches) {
            $email = $matches[1];
            return '<a href="mailto:' . escapeOutput($email) . '">' . escapeOutput($email) . '</a>';
        },
        $text
    );

    return $text;
}

/**
 * Generate debug log message
 * 
 * @param string $message Message to log
 * @param mixed $data Optional data to dump
 * @return void
 */
function debugLog($message, $data = null) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry  = "[{$timestamp}] {$message}";
    
    if ($data !== null) {
        $logEntry .= PHP_EOL . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    
    error_log($logEntry);
}

/**
 * Get application version
 * 
 * @return string Version string
 */
function appVersion() {
    return '1.0.0';
}

/**
 * Get user agent information
 * 
 * @return string User agent
 */
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
}

/**
 * Check if request is AJAX
 * 
 * @return bool True if AJAX request
 */
function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get client IP address
 * 
 * @return string Client IP
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        // Cloudflare
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Proxy headers (take first IP)
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    }
    return 'Unknown';
}
?>
