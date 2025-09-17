<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Pegawai.php';
require_once __DIR__ . '/../helpers/utils.php';

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

    // Method untuk generate surat final (dengan layout penuh)
    public function generateSurat() {
        $data = $this->getSuratData();
        
        // Validasi data wajib
        if (empty($data['nipList'])) {
            die("Tidak ada pegawai dipilih!");
        }
        
        // Extract variables untuk template
        extract($data);

        // Mengirim data ke template surat lengkap
        include __DIR__ . '/../templates/surat_tugas.php';
    }

    // Method untuk handle request berdasarkan action
    public function handleRequest() {
        $action = $_GET['action'] ?? $_POST['action'] ?? 'generate';
        
        switch ($action) {
            case 'preview':
                $this->previewSurat();
                break;
            case 'generate':
            default:
                $this->generateSurat();
                break;
        }
    }
}

// Auto-handle request jika dipanggil langsung
if (!empty($_POST) || !empty($_GET)) {
    $controller = new SuratController();
    $controller->handleRequest();
}
