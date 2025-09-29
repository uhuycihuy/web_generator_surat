<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/utils.php';
require_once __DIR__ . '/../helpers/link_formatter.php';

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    throw new Exception('Composer autoload not found. Run: composer install');
}
require_once $autoloadPath;

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Settings;

class SuratUndanganController extends BaseController {
    public function exportWord() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'export_word') {
            return $this->jsonResponse(['error' => 'Invalid request'], 400);
        }

        try {
            $database = new Database();
            $db = $database->getConnection();

            error_log('POST data: ' . print_r($_POST, true));

            $selectedPegawai = $_POST['pegawai'] ?? [];
            
            if (empty($selectedPegawai)) {
                throw new Exception('Tidak ada peserta yang dipilih');
            }

            $acara = $_POST['acara'] ?? '';
            $tanggal = $_POST['tanggal'] ?? '';
            $waktuAwal = $_POST['waktu_awal'] ?? '';
            $waktuAkhir = $_POST['waktu_akhir'] ?? '';
            $lokasi = $_POST['lokasi'] ?? '';
            $agenda = $_POST['agenda'] ?? '';
            $tembusan = $_POST['tembusan'] ?? '';
            $nip_pejabat = $_POST['nama_pejabat'] ?? '';
            $jabatanPejabat = $_POST['jabatan_pejabat'] ?? '';
            $jenisUndangan = $_POST['jenis_undangan'] ?? 'offline'; 
            $media = $_POST['media'] ?? '';
            $rapatId = $_POST['rapat_id'] ?? '';
            $kataSandi = $_POST['kata_sandi'] ?? '';
            $tautan = $_POST['tautan'] ?? '';
            $narahubung = $_POST['narahubung'] ?? '';
            $noNarahubung = $_POST['no_narahubung'] ?? '';
            $gender = $_POST['gender'] ?? 'Saudara'; 
            $kalimatOpsional = $_POST['kalimat_opsional'] ?? '';

            $pejabatList = array_column(getNamaPejabatList(), 'nama', 'nip');
            $namaPejabat = $pejabatList[$nip_pejabat] ?? '';
            $nipPejabat = $nip_pejabat;
            
            $tanggalFormatted = formatTanggalIndonesia($tanggal);
            $waktuFormatted = formatWaktuUndangan($waktuAwal, $waktuAkhir);

            // Ambil pegawai
            $daftarPegawai = [];
            foreach ($selectedPegawai as $nipPegawai) {
                if (strpos($nipPegawai, 'L|') === 0) {
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
                    $stmt = $db->prepare("SELECT * FROM pegawai WHERE nip = ?");
                    $stmt->execute([$nipPegawai]);
                    $pegawai = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($pegawai) {
                        $pegawai['is_external'] = false;
                        $daftarPegawai[] = $pegawai;
                    }
                }
            }

            // Pilih template
            if ($jenisUndangan === 'online') {
                $templatePath = __DIR__ . '/../templates/template_surat_undangan_online.docx';
            } else {
                $templatePath = __DIR__ . '/../templates/template_surat_undangan_offline.docx';
            }

            if (!file_exists($templatePath)) {
                throw new Exception('Template file not found: ' . $templatePath);
            }

            $tempDir = __DIR__ . '/../temp';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            Settings::setTempDir($tempDir);

            $workingTemplate = $tempDir . '/' . uniqid('template_') . '.docx';
            copy($templatePath, $workingTemplate);

            $templateProcessor = new TemplateProcessor($workingTemplate);

            $variables = $templateProcessor->getVariables();
            
            // Validasi template
            $requiredPlaceholders = ['ACARA', 'TANGGAL', 'AGENDA', 'NAMA_PEJABAT'];
            $missingPlaceholders = array_diff($requiredPlaceholders, $variables);
            if (!empty($missingPlaceholders)) {
                throw new Exception('Template tidak valid. Missing placeholders: ' . implode(', ', $missingPlaceholders));
            }

            // Isi placeholder umum
            $templateProcessor->setValue('ACARA', $acara);
            $templateProcessor->setValue('TANGGAL', $tanggalFormatted);
            $templateProcessor->setValue('WAKTU_AWAL', $waktuAwal);
            
            if (in_array('WAKTU_AKHIR', $variables)) {
                $templateProcessor->setValue('WAKTU_AKHIR', $waktuFormatted);
            } elseif (in_array('WKTU_AKHIR', $variables)) {
                $templateProcessor->setValue('WKTU_AKHIR', $waktuFormatted);
            }
            
            $templateProcessor->setValue('AGENDA', $agenda);
            $templateProcessor->setValue('NAMA_PEJABAT', $namaPejabat);
            $templateProcessor->setValue('NIP_PEJABAT', $nipPejabat);
            $templateProcessor->setValue('TEMBUSAN', $tembusan ?: '');
            $templateProcessor->setValue('JABATAN_PEJABAT', $jabatanPejabat);
            $templateProcessor->setValue('KALIMAT_OPSIONAL', $kalimatOpsional ?: '');
            $templateProcessor->setValue('NARAHUBUNG', $narahubung ?: '');
            $templateProcessor->setValue('NO_NARAHUBUNG', $noNarahubung ?: '');
            $templateProcessor->setValue('GENDER', $gender);

            // Placeholder khusus
            if ($jenisUndangan === 'online') {
                $templateProcessor->setValue('MEDIA', $media);
                $templateProcessor->setValue('RAPAT_ID', $rapatId);
                $templateProcessor->setValue('KATA_SANDI', $kataSandi);
                $templateProcessor->setValue('TAUTAN', formatTautanOnline($tautan));
            } else {
                $templateProcessor->setValue('LOKASI', $lokasi);
            }

            // SOLUSI TERBAIK: Clone list item untuk setiap pegawai
            if (count($daftarPegawai) > 0) {
                // Cek apakah template menggunakan block clone
                try {
                    // Coba clone block jika ada ${pegawai_list} ... ${/pegawai_list}
                    $templateProcessor->cloneBlock('pegawai_list', count($daftarPegawai), true, true);
                    
                    foreach ($daftarPegawai as $index => $pegawai) {
                        $namaJabatan = $pegawai['nama_pegawai'];
                        if (!empty($pegawai['jabatan'])) {
                            $namaJabatan .= ', ' . $pegawai['jabatan'];
                        }
                        $blockIndex = $index + 1;
                        $templateProcessor->setValue('nama_jabatan#' . $blockIndex, $namaJabatan);
                    }
                } catch (Exception $e) {
                    // Fallback: gunakan setValue dengan manipulasi XML manual
                    $daftarPegawaiArray = [];
                    foreach ($daftarPegawai as $pegawai) {
                        $namaJabatan = $pegawai['nama_pegawai'];
                        if (!empty($pegawai['jabatan'])) {
                            $namaJabatan .= ', ' . $pegawai['jabatan'];
                        }
                        $daftarPegawaiArray[] = $namaJabatan;
                    }
                    
                    // Gunakan separator khusus
                    $templateProcessor->setValue('daftar_pegawai', implode('|||BREAK|||', $daftarPegawaiArray));
                }
            } else {
                $templateProcessor->setValue('daftar_pegawai', 'Tidak ada peserta');
            }

            // Nama file output
            $jenisUndanganSafe = preg_replace('/[^a-zA-Z0-9_-]/', '', $jenisUndangan);
            $filename = 'surat_undangan_' . $jenisUndanganSafe . '_' . date('Y-m-d') . '.docx';
            $outputFile = $tempDir . '/' . uniqid('output_') . '.docx';
            $templateProcessor->saveAs($outputFile);

            // Post-process: Manipulasi XML untuk mengganti separator dengan line break proper
            $zip = new \ZipArchive();
            if ($zip->open($outputFile) === true) {
                $documentXml = $zip->getFromName('word/document.xml');
                
                if (strpos($documentXml, '|||BREAK|||') !== false) {
                    // Replace dengan line break yang mempertahankan numbered list
                    // Ini akan membuat setiap item menjadi list item baru
                    $lineBreak = '</w:t></w:r></w:p><w:p><w:pPr><w:numPr><w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr></w:pPr><w:r><w:t xml:space="preserve">';
                    $documentXml = str_replace('|||BREAK|||', $lineBreak, $documentXml);
                    
                    $zip->deleteName('word/document.xml');
                    $zip->addFromString('word/document.xml', $documentXml);
                }
                
                $zip->close();
            }

            // Download
            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($outputFile));
            header('Cache-Control: max-age=0');

            while (ob_get_level()) {
                ob_end_clean();
            }
            
            readfile($outputFile);

            // Cleanup
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
}

// Handle request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new SuratUndanganController();
    $controller->exportWord();
}
?>