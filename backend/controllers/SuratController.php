<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Pegawai.php';
require_once __DIR__ . '/../helpers/utils.php';

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;

class SuratController {
    private $db;
    private $pegawaiModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->pegawaiModel = new Pegawai($this->db);
    }

    // Proses form surat
    public function getSuratData() {
        $nipList      = $_POST['pegawai'] ?? [];   
        $acara        = $_POST['acara'] ?? '';  
        $tgl_mulai    = $_POST['tgl_mulai'] ?? '';
        $tgl_selesai  = $_POST['tgl_selesai'] ?? '';
        $lokasi       = $_POST['lokasi'] ?? '';

        // Default DIPA 
        $dipa = !empty($_POST['dipa']) 
            ? $_POST['dipa'] 
            : "SP DIPA-139.05.1.693321/2025 tanggal 2 Desember 2024";

        // Pejabat 
        $pejabatJabatan = $_POST['pejabat'] ?? '';

        // Nama pejabat (nama + NIP)
        $nipPejabat   = $_POST['nama_pejabat'] ?? '';
        $namaPejabat  = '';
        foreach (getNamaPejabatList() as $pj) {
            if ($pj['nip'] == $nipPejabat) {
                $namaPejabat = $pj['nama'];
                break;
            }
        }

        // Tembusan opsional
        $tembusan     = $_POST['tembusan'] ?? '';

        // Ambil data semua pegawai
        $daftarPegawai = [];
        $adaPegawaiLuar = false; 

        foreach ($nipList as $kode) {
            // Pegawai internal (ada di DB)
            $pegawai = $this->pegawaiModel->getByNip($kode);
            if ($pegawai) {
                $daftarPegawai[] = $pegawai;
            } else {
                // Format pegawai luar: "L|Nama|Jabatan"
                if (strpos($kode, "L|") === 0) {
                    $parts = explode("|", $kode);
                    $daftarPegawai[] = [
                        'nama_pegawai' => $parts[1] ?? 'Tanpa Nama',
                        'jabatan'      => $parts[2] ?? '-'
                    ];
                    $adaPegawaiLuar = true;
                }
            }
        }

        // Format tanggal (contoh: Selasa–Rabu, 19–20 Agustus 2025)
        $tanggalFormatted = formatTanggalRange($tgl_mulai, $tgl_selesai);

        return [
            'nipList' => $nipList,
            'acara' => $acara,
            'tgl_mulai' => $tgl_mulai,
            'tgl_selesai' => $tgl_selesai,
            'lokasi' => $lokasi,
            'dipa' => $dipa,
            'pejabatJabatan' => $pejabatJabatan,
            'nipPejabat' => $nipPejabat,
            'namaPejabat' => $namaPejabat,
            'tembusan' => $tembusan,
            'daftarPegawai' => $daftarPegawai,
            'adaPegawaiLuar' => $adaPegawaiLuar,
            'tanggalFormatted' => $tanggalFormatted
        ];
    }

    // Method untuk preview surat (AJAX)
    public function previewSurat() {
        // Set header untuk AJAX response
        header('Content-Type: text/html; charset=utf-8');
        
        // Ambil data surat 
        $data = $this->getSuratData();
        
        // Extract variables untuk template
        extract($data);
        
        // Load template preview 
        include __DIR__ . '/../templates/surat_preview.php';
    }

    // Export ke Word 
    public function exportWord() {
        $data = $this->getSuratData();
        extract($data);

        // Render template HTML surat_tugas.php ke string
        ob_start();
        include __DIR__ . '/../templates/surat_tugas.php';
        $html = ob_get_clean();

        // Buat dokumen Word baru
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Convert HTML template ke Word
        Html::addHtml($section, $html, false, false);

        // Output ke browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="surat_tugas.docx"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save("php://output");
        exit;
    }

    // Method untuk handle request berdasarkan action
    public function handleRequest() {
        $action = $_GET['action'] ?? $_POST['action'] ?? 'export';

        switch ($action) {
            case 'preview':
                $this->previewSurat();
                break;
            case 'export':
            default:
                $this->exportWord();
                break;
        }
    }
}

// Auto-handle request jika dipanggil langsung
if (!empty($_POST) || !empty($_GET)) {
    $controller = new SuratController();
    $controller->handleRequest();
}
