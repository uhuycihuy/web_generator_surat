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



class SuratTugasController extends BaseController {
    public function exportWord() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'export_word') {
            return $this->jsonResponse(['error' => 'Invalid request'], 400);
        }
    
        try {
            // Get database connection
            $database = new Database();
            $db = $database->getConnection();
            
            // Get POST data
            $selectedPegawai = $_POST['pegawai'] ?? [];
            $tglMulai = $_POST['tgl_mulai'] ?? '';
            $tglSelesai = $_POST['tgl_selesai'] ?? '';
            $acara = $_POST['acara'] ?? '';
            $lokasi = $_POST['lokasi'] ?? '';
            $dipa = $_POST['dipa'] ?? 'SP DIPA-139.05.1.693321/2025 tanggal 2 Desember 2024';
            $tembusan = $_POST['tembusan'] ?? '';
            $nip_pejabat = $_POST['nama_pejabat'] ?? '';
            
            //get jabatan pejabat
            $pejabatJabatanList = getPejabatJabatanList();
            $jabatanPejabat = $_POST['jabatan_pejabat'] ?? '';

            // Get pejabat data using utils function
            $pejabatList = array_column(getNamaPejabatList(), 'nama', 'nip');
            $namaPejabat = $pejabatList[$nip_pejabat] ?? '';
            $nipPejabat = $nip_pejabat;
            
            // Get pegawai data
            $daftarPegawai = [];
            foreach ($selectedPegawai as $nipPegawai) {
                if (strpos($nipPegawai, 'L|') === 0) {
                    // Pegawai eksternal
                    $parts = explode('|', $nipPegawai);
                    $daftarPegawai[] = [
                        'nama_pegawai' => $parts[1] ?? 'Nama Eksternal',
                        'nip' => '-',
                        'pangkat' => '-',
                        'golongan' => '-',
                        'jabatan' => $parts[2] ?? 'Jabatan Eksternal',
                        'is_external' => true
                    ];
                } else {
                    // Pegawai internal
                    $stmt = $db->prepare("SELECT * FROM pegawai WHERE nip = ?");
                    $stmt->execute([$nipPegawai]);
                    $pegawai = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($pegawai) {
                        $pegawai['is_external'] = false;
                        $daftarPegawai[] = $pegawai;
                    }
                }
            }
            
            // Format tanggal using utils function

            $tanggalFormatted = formatTanggalRange($tglMulai, $tglSelesai);
            
            // Load template
            $templatePath = __DIR__ . '/../templates/template_surat_tugas.docx';
            if (!file_exists($templatePath)) {
                throw new Exception('Template file not found: ' . $templatePath);
            }
            
            // Create writable copy of template
            $tempDir = __DIR__ . '/../temp';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            
            // Set PHPWord temp directory
            Settings::setTempDir($tempDir);
            
            $workingTemplate = $tempDir . '/' . uniqid('template_') . '.docx';
            copy($templatePath, $workingTemplate);
            
            $templateProcessor = new TemplateProcessor($workingTemplate);
            
            // Replace simple placeholders
            $templateProcessor->setValue('ACARA', $acara);
            $templateProcessor->setValue('hari_tanggal', $tanggalFormatted);
            $templateProcessor->setValue('LOKASI', $lokasi);
            $templateProcessor->setValue('DIPA', $dipa);
            $templateProcessor->setValue('NAMA_PEJABAT', $namaPejabat);
            $templateProcessor->setValue('NIP_PEJABAT', $nipPejabat);
            $templateProcessor->setValue('TEMBUSAN', $tembusan ?: '');
            $templateProcessor->setValue('JABATAN_PEJABAT', $jabatanPejabat);
            
            // Clone table rows for pegawai (requires template to have table with ${no}, ${nama_nip}, ${jabatan})
            if (count($daftarPegawai) > 0) {
                $templateProcessor->cloneRow('no', count($daftarPegawai));
                
                // Fill table data
                foreach ($daftarPegawai as $index => $pegawai) {
                    $no = $index + 1;
                    $templateProcessor->setValue('no#' . $no, $no . '.');
                    
                    if ($pegawai['is_external']) {
                        $nama_nip = $pegawai['nama_pegawai'];
                    } else {
                        $nama_nip = $pegawai['nama_pegawai'] . "\nNIP " . $pegawai['nip'] . "\n" . 
                                   $pegawai['pangkat'] . ", " . $pegawai['golongan'];
                    }
                    
                    $templateProcessor->setValue('nama_nip#' . $no, $nama_nip);
                    $templateProcessor->setValue('jabatan#' . $no, $pegawai['jabatan']);
                }
            }
            
            // Generate filename
            $filename = 'surat_tugas_' . date('Y-m-d') . '.docx';
            
            // Save to temp file first
            $outputFile = $tempDir . '/' . uniqid('output_') . '.docx';
            $templateProcessor->saveAs($outputFile);
            
            // Output file to browser
            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($outputFile));
            header('Cache-Control: max-age=0');
            
            // Clear any previous output
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Read and output file
            readfile($outputFile);
            
            // Clean up temp files
            if (file_exists($workingTemplate)) {
                unlink($workingTemplate);
            }
            if (file_exists($outputFile)) {
                unlink($outputFile);
            }
            
            exit;
            
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    private function downloadDocxFile($filePath, $filename) {
        if (!file_exists($filePath)) {
            throw new Exception('File not found for download');
        }
        
        // Set headers for DOCX download
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        
        // Clear any previous output
        ob_clean();
        flush();
        
        // Read and output file
        readfile($filePath);
        exit;
    }
}

// Handle request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new SuratTugasController();
    $controller->exportWord();
}