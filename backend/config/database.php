<?php
require_once __DIR__ . '/EnvLoader.php';

/**
 * Database Connection Handler
 * 
 * Menggunakan PDO dengan credentials dari .env file
 */
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        // Load environment variables
        EnvLoader::load();

        // Get credentials dari .env
        $this->host = EnvLoader::get('DB_HOST', 'localhost');
        $this->db_name = EnvLoader::get('DB_NAME', 'generator_surat');
        $this->username = EnvLoader::get('DB_USER', 'root');
        $this->password = EnvLoader::get('DB_PASS', '');
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            $this->conn->exec("set names utf8mb4");
        } catch (PDOException $exception) {
            if (EnvLoader::get('APP_DEBUG', false) === 'true') {
                throw $exception;
            }
            error_log('Database Connection Error: ' . $exception->getMessage());
            die('Database connection error. Please contact administrator.');
        }

        return $this->conn;
    }
}
?>
