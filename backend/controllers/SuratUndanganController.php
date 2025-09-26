<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/utils.php';
require_once __DIR__ . '/../helpers/link_formatter.php';

// Load Composer autoloader
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

            // Debug: Log data POST
            error_log('POST data: ' . print_r($_POST, true));

            // Ambil data dari POST
            $selectedPegawai = $_POST['pegawai'] ?? [];
            
            // Validasi input
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

            // Jenis undangan
            $jenisUndangan = $_POST['jenis_undangan'] ?? 'offline'; 

            // Tambahan khusus online
            $media = $_POST['media'] ?? '';
            $rapatId = $_POST['rapat_id'] ?? '';
            $kataSandi = $_POST['kata_sandi'] ?? '';
            $tautan = $_POST['tautan'] ?? '';

            // Narahubung dan data opsional
            $narahubung = $_POST['narahubung'] ?? '';
            $noNarahubung = $_POST['no_narahubung'] ?? '';
            $gender = $_POST['gender'] ?? 'Saudara'; 
            $kalimatOpsional = $_POST['kalimat_opsional'] ?? '';

            // Ambil pejabat
            $pejabatList = array_column(getNamaPejabatList(), 'nama', 'nip');
            $namaPejabat = $pejabatList[$nip_pejabat] ?? '';
            $nipPejabat = $nip_pejabat;
            
            // Format tanggal Indonesia
            $tanggalFormatted = formatTanggalIndonesia($tanggal);
            
            // Format waktu dengan default "selesai"
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

            // Temp dir
            $tempDir = __DIR__ . '/../temp';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            Settings::setTempDir($tempDir);

            $workingTemplate = $tempDir . '/' . uniqid('template_') . '.docx';
            copy($templatePath, $workingTemplate);

            $templateProcessor = new TemplateProcessor($workingTemplate);

            // Ambil daftar placeholder yang tersedia
            $variables = $templateProcessor->getVariables();
            
            // Validasi template memiliki placeholder yang diperlukan
            $requiredPlaceholders = ['ACARA', 'TANGGAL', 'AGENDA', 'NAMA_PEJABAT'];
            
            // Cek format pegawai - harus ada salah satu
            $hasPegawaiPlaceholder = in_array('DATA_PEGAWAI', $variables) || 
                                   (in_array('no', $variables) && in_array('nama_jabatan', $variables));
            
            if (!$hasPegawaiPlaceholder) {
                $requiredPlaceholders[] = 'DATA_PEGAWAI atau (no + nama_jabatan)';
            }
            
            $missingPlaceholders = array_diff($requiredPlaceholders, $variables);
            if (!empty($missingPlaceholders) && !$hasPegawaiPlaceholder) {
                throw new Exception('Template tidak valid. Missing placeholders: ' . implode(', ', $missingPlaceholders));
            }

            // Isi placeholder umum
            $templateProcessor->setValue('ACARA', $acara);
            $templateProcessor->setValue('TANGGAL', $tanggalFormatted);
            $templateProcessor->setValue('WAKTU_AWAL', $waktuAwal);
            // Cek placeholder waktu akhir (ada typo di beberapa template)
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

            // Placeholder khusus berdasarkan jenis
            if ($jenisUndangan === 'online') {
                $templateProcessor->setValue('MEDIA', $media);
                $templateProcessor->setValue('RAPAT_ID', $rapatId);
                $templateProcessor->setValue('KATA_SANDI', $kataSandi);
                $templateProcessor->setValue('TAUTAN', formatTautanOnline($tautan));
            } else {
                $templateProcessor->setValue('LOKASI', $lokasi);
            }

            // Debug: Cek format template
            error_log('Available variables: ' . print_r($variables, true));
            error_log('DATA_PEGAWAI exists: ' . (in_array('DATA_PEGAWAI', $variables) ? 'YES' : 'NO'));
            error_log('Old format (no+nama_jabatan) exists: ' . ((in_array('no', $variables) && in_array('nama_jabatan', $variables)) ? 'YES' : 'NO'));
            error_log('Daftar pegawai count: ' . count($daftarPegawai));
            
            // Isi daftar pegawai - cek format template
            if (count($daftarPegawai) > 0) {
                // Cek apakah template menggunakan format lama atau baru
                if (in_array('DATA_PEGAWAI', $variables)) {
                    // Format baru: satu placeholder untuk semua data
                    $daftarPegawaiText = "";
                    foreach ($daftarPegawai as $index => $pegawai) {
                        $no = $index + 1;
                        $namaJabatan = $pegawai['nama_pegawai'];
                        if (!empty($pegawai['jabatan'])) {
                            $namaJabatan .= ', ' . $pegawai['jabatan'];
                        }
                        $daftarPegawaiText .= $no . ". " . $namaJabatan . "\r\n";
                    }
                    
                    error_log('Data pegawai text: ' . $daftarPegawaiText);
                    $templateProcessor->setValue('DATA_PEGAWAI', $daftarPegawaiText);
                    
                } elseif (in_array('no', $variables) && in_array('nama_jabatan', $variables)) {
                    // Format lama: clone untuk setiap pegawai
                    $templateProcessor->cloneRow('no', count($daftarPegawai));
                    
                    foreach ($daftarPegawai as $index => $pegawai) {
                        $rowIndex = $index + 1;
                        $templateProcessor->setValue('no#' . $rowIndex, $rowIndex);
                        
                        $namaJabatan = $pegawai['nama_pegawai'];
                        if (!empty($pegawai['jabatan'])) {
                            $namaJabatan .= ', ' . $pegawai['jabatan'];
                        }
                        $templateProcessor->setValue('nama_jabatan#' . $rowIndex, $namaJabatan);
                    }
                }
            } else {
                // Tidak ada peserta
                if (in_array('DATA_PEGAWAI', $variables)) {
                    $templateProcessor->setValue('DATA_PEGAWAI', 'Tidak ada peserta');
                } elseif (in_array('no', $variables)) {
                    $templateProcessor->setValue('no', '1');
                    $templateProcessor->setValue('nama_jabatan', 'Tidak ada peserta');
                }
            }

            // Nama file output
            $filename = 'surat_undangan_' . $jenisUndangan . '_' . date('Y-m-d') . '.docx';
            $outputFile = $tempDir . '/' . uniqid('output_') . '.docx';
            $templateProcessor->saveAs($outputFile);

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