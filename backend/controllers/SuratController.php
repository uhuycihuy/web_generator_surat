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
    public function generateSurat() {
        $nipList      = $_POST['pegawai'] ?? [];   
        $acara        = $_POST['acara'] ?? '';
        $tgl_mulai    = $_POST['tgl_mulai'] ?? '';
        $tgl_selesai  = $_POST['tgl_selesai'] ?? '';
        $lokasi       = $_POST['lokasi'] ?? '';
        $dipa         = $_POST['dipa'] ?? '';
        $pejabat      = $_POST['pejabat'] ?? '';
        $namaPejabat  = $_POST['nama_pejabat'] ?? '';
        $tembusan     = $_POST['tembusan'] ?? '';

        if (empty($nipList)) {
            die("❌ Tidak ada pegawai dipilih!");
        }

        // Ambil data semua pegawai
        $daftarPegawai = [];
        $adaPegawaiLuar = false; // flag kalau ada pegawai luar

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

        // Kirim ke template surat
        include __DIR__ . '/../templates/surat_tugas.php';
    }
}
