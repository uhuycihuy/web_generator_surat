<?php
/**
 * Environment Variable Loader
 * 
 * Load .env file dan set environment variables
 * Usage: require_once 'EnvLoader.php';
 */

class EnvLoader {
    private static $loaded = false;

    /**
     * Load .env file
     * 
     * @param string $filePath Path ke .env file
     * @return void
     */
    public static function load($filePath = null) {
        if (self::$loaded) {
            return;
        }

        if ($filePath === null) {
            $filePath = __DIR__ . '/../../.env';
        }

        if (!file_exists($filePath)) {
            throw new Exception(".env file not found: $filePath");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments dan empty lines
            if (strpos(trim($line), '#') === 0 || empty(trim($line))) {
                continue;
            }

            // Parse line
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes if present
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }

                // Set environment variable
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }

        self::$loaded = true;
    }

    /**
     * Get environment variable
     * 
     * @param string $key Variable name
     * @param mixed $default Default value jika key tidak ada
     * @return mixed
     */
    public static function get($key, $default = null) {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    /**
     * Check if variable exists
     * 
     * @param string $key Variable name
     * @return bool
     */
    public static function has($key) {
        return isset($_ENV[$key]) || getenv($key) !== false;
    }
}
?>
