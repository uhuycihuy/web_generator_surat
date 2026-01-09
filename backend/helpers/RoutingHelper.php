<?php
/**
 * Routing Helper Functions
 * 
 * URL generation, routing, redirects
 */

/**
 * Get base path untuk aplikasi
 * Handles CLI dan HTTP environments
 * 
 * @return string Base path
 */
function appBasePath() {
    if (PHP_SAPI === 'cli') {
        return '';
    }

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $directory  = str_replace('\\', '/', dirname($scriptName));
    if ($directory === '/' || $directory === '\\') {
        return '';
    }

    return rtrim($directory, '/');
}

/**
 * Generate base URL
 * 
 * @param string $path Optional path to append
 * @return string Full URL
 */
function baseUrl($path = '') {
    if (PHP_SAPI === 'cli') {
        return $path;
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base   = appBasePath();

    $url = $scheme . '://' . $host;
    if (!empty($base)) {
        $url .= $base;
    }
    $url .= '/';

    if (!empty($path)) {
        $url .= ltrim($path, '/');
    }

    return $url;
}

/**
 * Generate route URL
 * 
 * @param string $path Route path
 * @param string $query Optional query string
 * @return string Full URL
 */
function routeUrl($path = '', $query = '') {
    $url = baseUrl($path);
    if (!empty($query)) {
        $url .= (strpos($url, '?') === false ? '?' : '&') . ltrim($query, '?');
    }
    return $url;
}

/**
 * Generate asset URL
 * 
 * @param string $path Asset path relative to /assets/
 * @return string Full asset URL
 */
function assetUrl($path) {
    $normalized = ltrim($path, '/');
    return baseUrl('assets/' . $normalized);
}

/**
 * Redirect to URL or path
 * 
 * @param string $path URL or route path
 * @param int $status HTTP status code
 * @return void (exits execution)
 */
function redirectTo($path, $status = 302) {
    $destination = filter_var($path, FILTER_VALIDATE_URL) ? $path : baseUrl($path);
    header("Location: {$destination}", true, $status);
    exit;
}

/**
 * Get current route path
 * 
 * @return string|false Current route path or false if cannot determine
 */
function currentRoutePath() {
    if (PHP_SAPI === 'cli') {
        return '';
    }

    $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base       = appBasePath();

    if (!empty($base) && strpos($requestUri, $base) === 0) {
        $requestUri = substr($requestUri, strlen($base));
    }

    return trim($requestUri, '/');
}
?>
