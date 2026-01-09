<?php
/**
 * Surat (Letter) Business Logic Service
 * 
 * Handles document generation, data processing, and file operations
 */

class SuratService {
    
    private $db;
    private $tempDir;
    private $validator;
    
    /**
     * Initialize service
     * 
     * @param PDO $db Database connection
     * @param string $tempDir Temporary directory for file processing
     * @param RequestValidator $validator Input validator
     */
    public function __construct($db, $tempDir, $validator = null) {
        $this->db = $db;
        $this->tempDir = rtrim($tempDir, '/');
        $this->validator = $validator;
    }
    
    /**
     * Get all available templates
     * 
     * @return array List of template filenames
     */
    public function getAvailableTemplates() {
        $templateDir = __DIR__ . '/../templates';
        if (!is_dir($templateDir)) {
            return [];
        }
        
        $templates = [];
        foreach (glob($templateDir . '/*.php') as $file) {
            $templates[] = basename($file);
        }
        
        return $templates;
    }
    
    /**
     * Load document template
     * 
     * @param string $templateName Template filename
     * @return string|false Template file path or false
     */
    public function loadTemplate($templateName) {
        $templatePath = __DIR__ . '/../templates/' . basename($templateName);
        
        if (!file_exists($templatePath) || !is_readable($templatePath)) {
            throw new Exception("Template not found or not readable: " . escapeOutput($templateName));
        }
        
        return $templatePath;
    }
    
    /**
     * Get pegawai data from database
     * Retrieves full information for selected employees
     * 
     * @param array $selectedPegawai Array of NIP values
     * @return array Pegawai data with all details
     */
    public function getPegawaiData($selectedPegawai) {
        if (empty($selectedPegawai) || !is_array($selectedPegawai)) {
            return [];
        }
        
        // Filter and validate NIPs
        $nips = array_filter($selectedPegawai, function($nip) {
            return !empty($nip) && is_string($nip);
        });
        
        if (empty($nips)) {
            return [];
        }
        
        try {
            // Batch query - single query for all pegawai
            $placeholders = str_repeat('?,', count($nips) - 1) . '?';
            $query = "SELECT id, nip, nama, jabatan, unit, email, no_hp 
                      FROM pegawai 
                      WHERE nip IN ($placeholders)
                      ORDER BY nama ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($nips);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching pegawai data: " . $e->getMessage());
            throw new Exception("Failed to fetch employee data");
        }
    }
    
    /**
     * Generate document from template
     * Processes template with provided data
     * 
     * @param string $templatePath Path to template file
     * @param array $data Data to populate template
     * @return string Generated document content
     */
    public function generateDocumentContent($templatePath, $data) {
        ob_start();
        
        try {
            // Extract data array for use in template
            extract($data, EXTR_SKIP);
            
            // Include template in output buffer
            include $templatePath;
            
            $content = ob_get_clean();
            return $content;
        } catch (Exception $e) {
            ob_end_clean();
            throw new Exception("Error generating document: " . $e->getMessage());
        }
    }
    
    /**
     * Create temporary directory for document processing
     * 
     * @return string Path to created temp directory
     */
    public function createTempDirectory() {
        $tempPath = $this->tempDir . '/' . uniqid('surat_', true);
        
        if (!@mkdir($tempPath, 0755, true)) {
            throw new Exception("Failed to create temporary directory");
        }
        
        return $tempPath;
    }
    
    /**
     * Cleanup temporary files/directories
     * 
     * @param array|string $paths Path(s) to delete
     * @return bool Success status
     */
    public function cleanupTemporaryFiles($paths) {
        if (is_string($paths)) {
            $paths = [$paths];
        }
        
        if (!is_array($paths)) {
            return false;
        }
        
        foreach ($paths as $path) {
            if (empty($path) || !is_string($path)) {
                continue;
            }
            
            // Prevent deletion of files outside temp directory
            if (strpos(realpath($path), realpath($this->tempDir)) !== 0) {
                continue;
            }
            
            if (is_file($path)) {
                @unlink($path);
            } elseif (is_dir($path)) {
                $this->removeDirectory($path);
            }
        }
        
        return true;
    }
    
    /**
     * Recursively remove directory
     * 
     * @param string $dir Directory path
     * @return bool Success status
     */
    private function removeDirectory($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($files as $file) {
            if ($file->isDir()) {
                @rmdir($file->getRealPath());
            } else {
                @unlink($file->getRealPath());
            }
        }
        
        return @rmdir($dir);
    }
    
    /**
     * Get application settings
     * 
     * @return array Settings array
     */
    public function getSettings() {
        return [
            'dipa_default' => EnvLoader::get('DIPA_DEFAULT', 'DIPA-001'),
            'app_env' => EnvLoader::get('APP_ENV', 'production'),
            'temp_dir' => $this->tempDir,
        ];
    }
    
    /**
     * Validate surat data
     * 
     * @param array $data Data to validate
     * @param string $type Type of surat (tugas, undangan)
     * @return array Validation errors (empty if valid)
     */
    public function validateSuratData($data, $type = 'tugas') {
        if (!$this->validator) {
            return [];
        }
        
        $rules = [];
        
        if ($type === 'tugas') {
            $rules = [
                'nomor_surat' => 'required|string',
                'tgl_mulai' => 'required|date',
                'tgl_selesai' => 'required|date',
                'perihal' => 'required|string',
                'pegawai_terpilih' => 'required|array',
            ];
        } elseif ($type === 'undangan') {
            $rules = [
                'nomor_surat' => 'required|string',
                'tanggal_undangan' => 'required|date',
                'waktu_awal' => 'required|string',
                'tempat' => 'required|string',
                'perihal' => 'required|string',
                'pegawai_terpilih' => 'required|array',
            ];
        }
        
        try {
            $this->validator->validate($data, $rules);
            return [];
        } catch (ValidationException $e) {
            return $e->getErrors();
        }
    }
}
?>
