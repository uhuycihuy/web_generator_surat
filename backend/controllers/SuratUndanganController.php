<?php
session_start();
require_once __DIR__ . '/AbstractSuratController.php';
require_once __DIR__ . '/../helpers/link_formatter.php';
require_once __DIR__ . '/../config/EnvLoader.php';

/**
 * Surat Undangan Controller
 * 
 * Handle DOCX generation untuk Surat Undangan (Invitation Letter)
 * Extends AbstractSuratController untuk eliminate code duplication
 */
class SuratUndanganController extends AbstractSuratController {

    /**
     * Generate dan export Surat Undangan sebagai DOCX file
     * 
     * @return void
     * @throws Exception
     */
    public function exportWord() {
        try {
            // Validate request
            $this->validateRequest('export_word');

            // Get POST data
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
            $jumlahHalaman = (int)($_POST['jumlah_halaman'] ?? 1);

            // Get pejabat
            $pejabatData = getPejabatByJabatan($jabatanPejabat);
            if (!$pejabatData) {
                throw new Exception('Pejabat tidak ditemukan');
            }

            // Process pegawai (using shared method - REMOVED DUPLICATE CODE)
            $daftarPegawai = $this->processPegawaiData($selectedPegawai);

            // Format dates
            $tanggalFormatted = formatTanggalIndonesia($tanggal);
            $waktuFormatted = formatWaktuUndangan($waktuAwal, $waktuAkhir);

            // Select template
            if ($jenisUndangan === 'online') {
                $templatePath = 'template_surat_undangan_online';
            } else {
                $templatePath = 'template_surat_undangan_offline';
            }

            // Load template (using shared method)
            $templateProcessor = $this->loadTemplate($templatePath);

            // Validate required placeholders
            $variables = $templateProcessor->getVariables();
            $requiredPlaceholders = ['ACARA', 'TANGGAL', 'AGENDA', 'NAMA_PEJABAT'];
            $missingPlaceholders = array_diff($requiredPlaceholders, $variables);
            
            if (!empty($missingPlaceholders)) {
                throw new Exception('Template tidak valid. Missing: ' . implode(', ', $missingPlaceholders));
            }

            // Replace common placeholders
            $templateProcessor->setValue('ACARA', $acara);
            $templateProcessor->setValue('TANGGAL', $tanggalFormatted);
            $templateProcessor->setValue('WAKTU_AWAL', $waktuAwal);

            if (in_array('WAKTU_AKHIR', $variables)) {
                $templateProcessor->setValue('WAKTU_AKHIR', $waktuFormatted);
            } elseif (in_array('WKTU_AKHIR', $variables)) {
                $templateProcessor->setValue('WKTU_AKHIR', $waktuFormatted);
            }

            $templateProcessor->setValue('AGENDA', $agenda);
            $templateProcessor->setValue('NAMA_PEJABAT', $pejabatData['nama']);
            $templateProcessor->setValue('NIP_PEJABAT', $pejabatData['nip']);
            $templateProcessor->setValue('TEMBUSAN', $tembusan ?: '');
            $templateProcessor->setValue('JABATAN_PEJABAT', $jabatanPejabat);
            $templateProcessor->setValue('KALIMAT_OPSIONAL', $kalimatOpsional ?: '');
            $templateProcessor->setValue('NARAHUBUNG', $narahubung ?: '');
            $templateProcessor->setValue('NO_NARAHUBUNG', $noNarahubung ?: '');
            $templateProcessor->setValue('GENDER', $gender);

            // Set jumlah lampiran
            $jumlahLampiranKata = formatJumlahLampiran($jumlahHalaman);
            $templateProcessor->setValue('JUMLAH_LAMPIRAN', $jumlahLampiranKata);

            // Template-specific placeholders
            if ($jenisUndangan === 'online') {
                $templateProcessor->setValue('MEDIA', $media);
                $templateProcessor->setValue('RAPAT_ID', $rapatId);
                $templateProcessor->setValue('KATA_SANDI', $kataSandi);
                $templateProcessor->setValue('TAUTAN', formatTautanOnline($tautan));
            } else {
                $templateProcessor->setValue('LOKASI', $lokasi);
            }

            // Process pegawai list (clone blocks)
            if (count($daftarPegawai) > 0) {
                try {
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
                    // Fallback untuk template yang tidak support cloneBlock
                    $daftarPegawaiArray = [];
                    foreach ($daftarPegawai as $pegawai) {
                        $namaJabatan = $pegawai['nama_pegawai'];
                        if (!empty($pegawai['jabatan'])) {
                            $namaJabatan .= ', ' . $pegawai['jabatan'];
                        }
                        $daftarPegawaiArray[] = $namaJabatan;
                    }

                    $templateProcessor->setValue('daftar_pegawai', implode('|||BREAK|||', $daftarPegawaiArray));
                }
            } else {
                $templateProcessor->setValue('daftar_pegawai', 'Tidak ada peserta');
            }

            // Save output file
            $jenisUndanganSafe = preg_replace('/[^a-zA-Z0-9_-]/', '', $jenisUndangan);
            $filename = 'surat_undangan_' . $jenisUndanganSafe . '_' . date('Y-m-d') . '.docx';
            $outputFile = $this->tempDir . '/' . uniqid('output_') . '.docx';
            $templateProcessor->saveAs($outputFile);

            // Post-process XML untuk line breaks
            $zip = new \ZipArchive();
            if ($zip->open($outputFile) === true) {
                $documentXml = $zip->getFromName('word/document.xml');

                if (strpos($documentXml, '|||BREAK|||') !== false) {
                    $lineBreak = '</w:t></w:r></w:p><w:p><w:pPr><w:numPr><w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr></w:pPr><w:r><w:t xml:space="preserve">';
                    $documentXml = str_replace('|||BREAK|||', $lineBreak, $documentXml);

                    $zip->deleteName('word/document.xml');
                    $zip->addFromString('word/document.xml', $documentXml);
                }

                $zip->close();
            }

            // Download file
            $this->downloadDocxFile($outputFile, $filename);

        } catch (Exception $e) {
            error_log('SuratUndanganController Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            die('Error: ' . htmlspecialchars($e->getMessage()));
        }
    }
}

// Handle request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new SuratUndanganController();
    $controller->exportWord();
}
?>

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
            
            // Set jumlah lampiran (halaman) ke template dengan format kata
            $jumlahLampiranKata = formatJumlahLampiran($jumlahHalaman);
            error_log('DEBUG SuratUndangan - Jumlah halaman: ' . $jumlahHalaman);
            error_log('DEBUG SuratUndangan - Format kata: ' . $jumlahLampiranKata);
            $templateProcessor->setValue('JUMLAH_LAMPIRAN', $jumlahLampiranKata);

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
            error_log('SuratUndanganController Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            die('Error: ' . $e->getMessage());
        }
    }
}

// Handle request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new SuratUndanganController();
    $controller->exportWord();
}
?>