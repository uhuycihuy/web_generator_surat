<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/utils.php';

// Load Composer autoloader
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    throw new Exception('Composer autoload not found. Run: composer install');
}
require_once $autoloadPath;

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Settings;

/**
 * Abstract Surat Controller
 * 
 * Parent class yang mengkonsolidasikan shared methods dari SuratTugasController 
 * dan SuratUndanganController untuk menghilangkan code duplication (~60%)
 * 
 * Shared Responsibilities:
 * - Template loading dan management
 * - Temp directory setup
 * - Pegawai data processing
 * - File download operations
 * - Error handling
 */
abstract class AbstractSuratController extends BaseController {
    protected $db;
    protected $tempDir;

    public function __construct() {
        checkLogin();
        $database = new Database();
        $this->db = $database->getConnection();
        $this->setupTempDirectory();
    }

    /**
     * Setup temp directory untuk DOCX processing
     * 
     * @return void
     */
    protected function setupTempDirectory() {
        $this->tempDir = __DIR__ . '/../temp';
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }
        Settings::setTempDir($this->tempDir);
    }

    /**
     * Load template DOCX file
     * 
     * @param string $templateName Nama template (e.g., 'template_surat_tugas')
     * @return TemplateProcessor
     * @throws Exception
     */
    protected function loadTemplate($templateName) {
        $templatePath = __DIR__ . "/../templates/{$templateName}.docx";
        
        if (!file_exists($templatePath)) {
            throw new Exception("Template file not found: $templatePath");
        }

        // Create writable copy of template
        $workingTemplate = $this->tempDir . '/' . uniqid('template_') . '.docx';
        copy($templatePath, $workingTemplate);

        return new TemplateProcessor($workingTemplate);
    }

    /**
     * Process pegawai data (internal & external)
     * Menggunakan batch query untuk menghindari N+1 problem
     * 
     * @param array $selectedPegawai Array of NIP/eksternal identifiers
     * @return array Array of processed pegawai data
     * @throws Exception
     */
    protected function processPegawaiData($selectedPegawai) {
        $daftarPegawai = [];

        if (empty($selectedPegawai)) {
            return $daftarPegawai;
        }

        // Separate internal dan external pegawai
        $internalNips = [];
        $externalPegawai = [];

        foreach ($selectedPegawai as $nipPegawai) {
            if (strpos($nipPegawai, 'L|') === 0) {
                // External employee: L|nama|nip/jabatan|...
                $externalPegawai[] = $nipPegawai;
            } else {
                $internalNips[] = $nipPegawai;
            }
        }

        // ✅ OPTIMIZATION: Batch query untuk internal pegawai (N+1 fix)
        if (!empty($internalNips)) {
            $placeholders = implode(',', array_fill(0, count($internalNips), '?'));
            $stmt = $this->db->prepare("
                SELECT nip, nama_pegawai, 
                       COALESCE(pangkat, '') AS pangkat, 
                       COALESCE(golongan, '') AS golongan, 
                       COALESCE(jabatan, '') AS jabatan
                FROM pegawai 
                WHERE nip IN ($placeholders)
            ");
            $stmt->execute($internalNips);
            
            // Fetch and index by NIP for quick lookup
            $allInternalPegawai = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $pegawai) {
                $allInternalPegawai[$pegawai['nip']] = $pegawai;
            }

            // Add ke daftar dalam urutan original
            foreach ($internalNips as $nip) {
                if (isset($allInternalPegawai[$nip])) {
                    $pegawai = $allInternalPegawai[$nip];
                    $pegawai['is_external'] = false;
                    $daftarPegawai[] = $pegawai;
                }
            }
        }

        // Process external pegawai
        foreach ($externalPegawai as $nipPegawai) {
            $parts = explode('|', $nipPegawai);
            $pegawaiData = [
                'nama_pegawai' => $this->cleanField($parts[1] ?? 'Nama Eksternal'),
                'nip' => $this->cleanField($parts[2] ?? ''),
                'pangkat' => $this->cleanField($parts[3] ?? ''),
                'golongan' => $this->cleanField($parts[4] ?? ''),
                'jabatan' => $this->cleanField($parts[5] ?? 'Jabatan Eksternal'),
                'is_external' => true
            ];
            $daftarPegawai[] = $pegawaiData;
        }

        return $daftarPegawai;
    }

    /**
     * Helper: Bersihkan field value
     * Wrapper untuk CommonHelper::cleanField()
     * 
     * @param string $value
     * @return string
     */
    protected function cleanField($value) {
        return cleanField($value);
    }

    /**
     * Validate template memiliki required placeholders
     * 
     * @param TemplateProcessor $template Template yang akan divalidasi
     * @param array $requiredPlaceholders List placeholder yang harus ada
     * @return void
     * @throws Exception Jika ada placeholder yang hilang
     */
    protected function validateTemplateVariables($template, $requiredPlaceholders = []) {
        if (empty($requiredPlaceholders)) {
            return; // Skip validation jika tidak ada requirement
        }

        $variables = $template->getVariables();
        $missingPlaceholders = array_diff($requiredPlaceholders, $variables);
        
        if (!empty($missingPlaceholders)) {
            throw new Exception(
                'Template tidak valid. Missing placeholders: ' . implode(', ', $missingPlaceholders)
            );
        }
    }

    /**
     * Download file DOCX
     * 
     * @param string $outputFile Path ke output file
     * @param string $filename Nama file untuk download
     * @return void
     */
    protected function downloadDocxFile($outputFile, $filename) {
        if (!file_exists($outputFile)) {
            throw new Exception('Output file not found for download');
        }

        // Clear any previous output
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Set headers for DOCX download
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($outputFile));
        header('Cache-Control: max-age=0');
        header('Pragma: no-cache');

        // Output file
        readfile($outputFile);
        exit;
    }

    /**
     * Cleanup temp files
     * 
     * @param array $files Array of file paths to delete
     * @return void
     */
    protected function cleanupTempFiles($files) {
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Get DIPA default value dari .env
     * 
     * @return string
     */
    protected function getDipaDefault() {
        return EnvLoader::get('DIPA_DEFAULT', 'SP DIPA-139.05.1.693321/2025 tanggal 2 Desember 2024');
    }

    /**
     * Validate request method dan action
     * 
     * @param string $expectedAction
     * @return void
     * @throws Exception
     */
    protected function validateRequest($expectedAction) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request method. Only POST requests are allowed.');
        }

        if (!isset($_POST['action']) || $_POST['action'] !== $expectedAction) {
            throw new Exception('Invalid action. Expected "' . $expectedAction . '"');
        }
    }

    /**
     * Abstract method: Child class must implement
     * 
     * @return void
     */
    abstract public function exportWord();
}
?>
