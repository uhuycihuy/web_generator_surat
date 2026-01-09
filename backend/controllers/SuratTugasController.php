<?php
session_start();
require_once __DIR__ . '/AbstractSuratController.php';
require_once __DIR__ . '/../config/EnvLoader.php';

/**
 * Surat Tugas Controller
 * 
 * Handle DOCX generation untuk Surat Tugas (Assignment Letter)
 * Extends AbstractSuratController untuk eliminate code duplication
 */
class SuratTugasController extends AbstractSuratController {

    /**
     * Generate dan export Surat Tugas sebagai DOCX file
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
            $tglMulai = $_POST['tgl_mulai'] ?? '';
            $tglSelesai = $_POST['tgl_selesai'] ?? '';
            $acara = $_POST['acara'] ?? '';
            $lokasi = $_POST['lokasi_tugas'] ?? $_POST['lokasi'] ?? '';
            $dipa = $_POST['dipa'] ?? $this->getDipaDefault();
            $tembusan = $_POST['tembusan'] ?? '';
            $jabatanPejabat = $_POST['jabatan_pejabat'] ?? '';
            $jumlahHalaman = (int)($_POST['jumlah_halaman'] ?? 1);

            // Validate pejabat
            $pejabatData = getPejabatByJabatan($jabatanPejabat);
            if (!$pejabatData) {
                throw new Exception('Pejabat tidak ditemukan');
            }

            // Process pegawai (using shared method - REMOVED DUPLICATE CODE)
            $daftarPegawai = $this->processPegawaiData($selectedPegawai);

            // Format tanggal
            $tanggalFormatted = formatTanggalRange($tglMulai, $tglSelesai);

            // Load template (using shared method)
            $templateProcessor = $this->loadTemplate('template_surat_tugas');

            // Replace placeholders
            $templateProcessor->setValue('ACARA', $acara);
            $templateProcessor->setValue('hari_tanggal', $tanggalFormatted);
            $templateProcessor->setValue('LOKASI', $lokasi);
            $templateProcessor->setValue('DIPA', $dipa);
            $templateProcessor->setValue('NAMA_PEJABAT', $pejabatData['nama']);
            $templateProcessor->setValue('NIP_PEJABAT', $pejabatData['nip']);
            $templateProcessor->setValue('TEMBUSAN', $tembusan ?: '');
            $templateProcessor->setValue('JABATAN_PEJABAT', $jabatanPejabat);

            // Clone table rows for pegawai
            if (count($daftarPegawai) > 0) {
                $templateProcessor->cloneRow('no', count($daftarPegawai));

                foreach ($daftarPegawai as $index => $pegawai) {
                    $no = $index + 1;
                    $templateProcessor->setValue('no#' . $no, $no . '.');

                    // Build nama, NIP, pangkat/golongan
                    $nipText = !empty($pegawai['nip']) ? $pegawai['nip'] : '';
                    $pangkatGolongan = '';
                    
                    if (!empty($pegawai['pangkat']) && !empty($pegawai['golongan'])) {
                        $pangkatGolongan = $pegawai['pangkat'] . ', ' . $pegawai['golongan'];
                    } elseif (!empty($pegawai['pangkat'])) {
                        $pangkatGolongan = $pegawai['pangkat'];
                    } elseif (!empty($pegawai['golongan'])) {
                        $pangkatGolongan = $pegawai['golongan'];
                    }

                    $nama_nip = $pegawai['nama_pegawai'];
                    if ($nipText) {
                        $nama_nip .= "\n" . $nipText;
                    }
                    if ($pangkatGolongan) {
                        $nama_nip .= "\n" . $pangkatGolongan;
                    }

                    $templateProcessor->setValue('nama_nip#' . $no, $nama_nip);
                    $templateProcessor->setValue('jabatan#' . $no, $pegawai['jabatan']);
                }
            }

            // Set jumlah lampiran
            $jumlahLampiranKata = formatJumlahLampiran($jumlahHalaman);
            $templateProcessor->setValue('JUMLAH_LAMPIRAN', $jumlahLampiranKata);

            // Save to temp file
            $outputFile = $this->tempDir . '/' . uniqid('output_') . '.docx';
            $templateProcessor->saveAs($outputFile);

            // Download file
            $filename = 'surat_tugas_' . date('Y-m-d') . '.docx';
            $this->downloadDocxFile($outputFile, $filename);

        } catch (Exception $e) {
            error_log('SuratTugasController Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            die('Error: ' . htmlspecialchars($e->getMessage()));
        }
    }
}

// Handle request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new SuratTugasController();
    $controller->exportWord();
}
?>
