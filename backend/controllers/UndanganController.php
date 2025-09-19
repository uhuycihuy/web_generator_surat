<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Pegawai.php';
require_once __DIR__ . '/../helpers/utils.php';

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;

class UndanganController {
    private $db;
    private $pegawaiModel;

    public function __construct() {
        checkLogin();
        $database = new Database();
        $this->db = $database->getConnection();
        $this->pegawaiModel = new Pegawai($this->db);
    }

    // Proses form undangan
    public function getUndanganData() {
        // Data utama
        $rangka        = $_POST['rangka'] ?? '';
        $hariTanggal   = $_POST['hari_tanggal'] ?? ''; 
        $waktu         = $_POST['waktu'] ?? '';
        $jenisAcara    = $_POST['jenis_acara'] ?? 'offline'; // Opsi online/offline acara undangan

        // Input Pilihan Online 
        $media   = $jenisAcara === 'online' ? ($_POST['media'] ?? '') : null;
        $rapat_id= $jenisAcara === 'online' ? ($_POST['rapat_id'] ?? '') : null;
        $sandi   = $jenisAcara === 'online' ? ($_POST['sandi'] ?? '') : null;
        $tautan  = $jenisAcara === 'online' ? ($_POST['tautan'] ?? '') : null;

        // Input Opsi Offline 
        $tempat  = $jenisAcara === 'offline' ? ($_POST['tempat'] ?? '') : null;

        // Informasi tambahan
        $agenda        = $_POST['agenda'] ?? '';
        $opsional      = $_POST['opsional'] ?? '';
        $narahubung    = $_POST['narahubung'] ?? '';
        $no_narahubung = $_POST['no_narahubung'] ?? '';

        // Pejabat penandatangan 
        $pejabatJabatan = $_POST['pejabat'] ?? '';
        $nipPejabat     = $_POST['nama_pejabat'] ?? '';
        $namaPejabat    = '';
        foreach (getNamaPejabatList() as $pj) {
            if ($pj['nip'] == $nipPejabat) {
                $namaPejabat = $pj['nama'];
                break;
            }
        }

        // Tembusan opsional
        $tembusan = $_POST['tembusan'] ?? '';

        // Daftar pegawai untuk “Yth”
        $nipList = $_POST['pegawai'] ?? [];
        $daftarPegawai = [];
        $adaPegawaiLuar = false;

        foreach ($nipList as $kode) {
            $pegawai = $this->pegawaiModel->getByNip($kode);
            if ($pegawai) {
                $daftarPegawai[] = [
                    'nama_pegawai' => $pegawai['nama_pegawai'],
                    'jabatan'      => $pegawai['jabatan']
                ];
            } else {
                // Pegawai luar format: L|Nama|Jabatan
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

        return [
            'rangka'        => $rangka,
            'hariTanggal'   => $hariTanggal,
            'waktu'         => $waktu,
            'jenisAcara'    => $jenisAcara,

            // Opsi Online 
            'media'         => $media,
            'rapat_id'      => $rapat_id,
            'sandi'         => $sandi,
            'tautan'        => $tautan,

            // Opsi Offline 
            'tempat'        => $tempat,

            'agenda'        => $agenda,
            'opsional'      => $opsional,
            'narahubung'    => $narahubung,
            'no_narahubung' => $no_narahubung,

            'pejabatJabatan'=> $pejabatJabatan,
            'nipPejabat'    => $nipPejabat,
            'namaPejabat'   => $namaPejabat,
            'tembusan'      => $tembusan,

            'daftarPegawai' => $daftarPegawai,
            'adaPegawaiLuar'=> $adaPegawaiLuar
        ];
    }

    // Preview undangan
    public function previewUndangan() {
        header('Content-Type: text/html; charset=utf-8');
        $data = $this->getUndanganData();
        extract($data);
        include __DIR__ . '/../templates/undangan_preview.php';
    }

    // Export undangan ke Word
    public function exportWord() {
        $data = $this->getUndanganData();
        extract($data);

        ob_start();
        include __DIR__ . '/../templates/undangan.php';
        $html = ob_get_clean();

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        Html::addHtml($section, $html, false, false);

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="undangan.docx"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save("php://output");
        exit;
    }

    // Handle request
    public function handleRequest() {
        $action = $_GET['action'] ?? $_POST['action'] ?? 'export';
        switch ($action) {
            case 'preview':
                $this->previewUndangan();
                break;
            case 'export':
            default:
                $this->exportWord();
                break;
        }
    }
}

// Auto-handle request
if (!empty($_POST) || !empty($_GET)) {
    $controller = new UndanganController();
    $controller->handleRequest();
}
