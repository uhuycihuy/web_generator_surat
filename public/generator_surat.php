<?php
// Integrasi dengan database real
require_once '../backend/config/database.php';
require_once '../backend/models/Pegawai.php';
require_once '../backend/helpers/utils.php';
require_once '../backend/helpers/PegawaiHelper.php';

session_start();
// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Initialize database dan models
try {
    $database = new Database();
    $db = $database->getConnection();
    $pegawaiModel = new Pegawai($db);

    // Get data from database
    $result = $pegawaiModel->getAll();
    $daftarPegawai = $result->fetchAll(PDO::FETCH_ASSOC);
    $pejabatList = getNamaPejabatList();
    
    $databaseStatus = 'connected';
    $statusMessage = 'Database terhubung successfully. Data pegawai diambil dari database.';
    
} catch (Exception $e) {
    // Fallback ke data dummy jika database error
    $databaseStatus = 'error';
    $statusMessage = 'Database error: ' . $e->getMessage() . '. Menggunakan data dummy.';
    
    // Data pegawai dummy sebagai fallback
    $daftarPegawai = [
        [
            'nip' => '197901142003121001',
            'nama_pegawai' => 'M. Samsuri',
            'jabatan' => 'Sekretaris Direktorat Jenderal Sains dan Teknologi',
            'pangkat' => 'Pembina Utama Muda',
            'golongan' => 'IV/c'
        ],
        [
            'nip' => '197607292010122001',
            'nama_pegawai' => 'Russy Arumsari',
            'jabatan' => 'Kepala Bagian Program dan Pelaporan',
            'pangkat' => 'Penata Tk.I',
            'golongan' => 'III/d'
        ],
        [
            'nip' => '197702112008011007',
            'nama_pegawai' => 'Arief Sanjaya',
            'jabatan' => 'Kepala Bagian Keuangan dan Umum',
            'pangkat' => 'Penata Tk.I',
            'golongan' => 'III/d'
        ]
    ];

    // Data pejabat dummy sebagai fallback
    $pejabatList = [
        [
            'nip'   => '197901142003121001',
            'nama'  => 'M. Samsuri'
        ],
        [
            'nip'   => '197604272005021001',
            'nama'  => 'Ahmad Najib Burhani'
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generator Surat - Kementerian Pendidikan Tinggi, Sains, dan Teknologi</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="generator-surat">
   <?php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $role = $_SESSION['user']['role'] ?? 'guest';

        if ($role === 'admin') {
            include "sidebar.php";
        } else {
            include "navbar.php";
        }
    ?>

    <div class="container">
        <div class="form-section">
            <div class="form-header">
                <div class="form-icon generator-surat">📄</div>
                <div class="form-title">
                    <h2>Generator Surat</h2>
                    <p>Pilih jenis surat dan lengkapi data</p>
                </div>
            </div>

            <!-- Jenis Surat Selector -->
            <div class="jenis-selector">
                <div class="jenis-option active" data-jenis="tugas">
                    📝 Surat Tugas
                </div>
                <div class="jenis-option" data-jenis="undangan">
                    ✉️ Surat Undangan
                </div>
            </div>

            <form id="generatorSuratForm" method="POST" action="../backend/controllers/SuratTugasController.php">
                <input type="hidden" name="jenis_surat" id="jenis_surat" value="tugas">
                <input type="hidden" name="jenis_undangan" id="jenis_undangan" value="offline">

                <div class="form-group">
                    <label class="form-label required" id="label-acara">Kegiatan/Acara</label>
                    <textarea id="acara" name="acara" class="form-textarea" placeholder="Contoh: Rekonsiliasi Kebutuhan dan Penyusunan Prognosis Anggaran" required></textarea>
                </div>

                <!-- Fields untuk Surat Tugas -->
                <div class="tugas-fields">
                    <div class="form-group">
                        <label class="form-label required">Tanggal Awal Kegiatan</label>
                        <input type="date" id="tgl_mulai" name="tgl_mulai" class="form-input" value="2025-08-19" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tanggal Akhir Kegiatan</label>
                        <input type="date" id="tgl_selesai" name="tgl_selesai" class="form-input" value="2025-08-20">
                        <small style="color: #6b7280; font-size: 12px;">Opsional jika satu hari</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Tempat Kegiatan</label>
                        <textarea id="lokasi_tugas" name="lokasi" class="form-textarea" placeholder="Contoh: Ruang Rapat Rektorat Lantai 9, Kampus Baru Depok" required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Nomor dan Tanggal DIPA</label>
                        <input type="text" id="dipa" name="dipa" class="form-input" placeholder="SP DIPA-139.05.1.693321/2025 tanggal 2 Desember 2024" value="SP DIPA-139.05.1.693321/2025 tanggal 2 Desember 2024">
                    </div>
                </div>

                <!-- Fields untuk Surat Undangan -->
                <div class="undangan-fields" style="display: none;">
                    <!-- Jenis Undangan Selector -->
                    <div class="jenis-selector-undangan">
                        <div class="jenis-option-undangan active" data-jenis="offline">
                             Offline (Tatap Muka)
                        </div>
                        <div class="jenis-option-undangan" data-jenis="online">
                             Online (Virtual)
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Tanggal</label>
                        <input type="date" id="tanggal" name="tanggal" class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Waktu</label>
                        <div class="time-inputs">
                            <div>
                                <input type="time" id="waktu_awal" name="waktu_awal" class="form-input">
                                <small style="color: #6b7280; font-size: 12px;">Waktu mulai</small>
                            </div>
                            <div>
                                <input type="time" id="waktu_akhir" name="waktu_akhir" class="form-input">
                                <small style="color: #6b7280; font-size: 12px;">Waktu selesai (opsional)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Fields untuk Offline -->
                    <div class="form-group offline-fields">
                        <label class="form-label required">Tempat</label>
                        <input type="text" id="lokasi" name="lokasi" class="form-input" placeholder="Contoh: Ruang Rapat Direktorat Jenderal Sains dan Teknologi">
                    </div>

                    <!-- Fields untuk Online -->
                    <div class="form-group online-fields" style="display: none;">
                        <label class="form-label required">Media</label>
                        <input type="text" id="media" name="media" class="form-input" placeholder="Contoh: Zoom, Google Meet">
                    </div>

                    <div class="form-group online-fields" style="display: none;">
                        <label class="form-label">Meeting ID</label>
                        <input type="text" id="rapat_id" name="rapat_id" class="form-input" placeholder="Contoh: 123 456 7890">
                    </div>

                    <div class="form-group online-fields" style="display: none;">
                        <label class="form-label">Kata Sandi</label>
                        <input type="text" id="kata_sandi" name="kata_sandi" class="form-input" placeholder="Contoh: abc123">
                    </div>

                    <div class="form-group online-fields" style="display: none;">
                        <label class="form-label">Tautan</label>
                        <input type="url" id="tautan" name="tautan" class="form-input" placeholder="https://zoom.us/j/123456789">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Agenda</label>
                        <textarea id="agenda" name="agenda" class="form-textarea" placeholder="Contoh: Konsolidasi dan Asistensi Penyusunan KAK"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kalimat Opsional</label>
                        <textarea id="kalimat_opsional" name="kalimat_opsional" class="form-textarea" placeholder="Contoh: Besar harapan kami Bapak/Ibu dapat hadir tepat waktu."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Narahubung</label>
                        <input type="text" id="narahubung" name="narahubung" class="form-input" placeholder="Contoh: Altist Ibnu Hajar">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Nomor Narahubung</label>
                        <input type="tel" id="no_narahubung" name="no_narahubung" class="form-input" placeholder="Contoh: 08123456789">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Sapaan</label>
                        <select id="gender" name="gender" class="form-select">
                            <option value="Saudara">Saudara</option>
                            <option value="Saudari">Saudari</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required" id="label-pegawai">Daftar Pegawai</label>
                    <select id="pegawai" name="pegawai[]" multiple="multiple" required style="display: none;">
                        <?php foreach ($daftarPegawai as $pegawai): ?>
                            <option value="<?= htmlspecialchars($pegawai['nip']) ?>">
                                <?= htmlspecialchars($pegawai['nama_pegawai']) ?> - <?= htmlspecialchars($pegawai['jabatan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <div class="employee-selector">
                        <div class="employee-selector-header">
                            <span>Daftar Pegawai</span>
                            <span class="employee-count">(0)</span>
                        </div>
                        <div class="employee-search">
                            <input type="text" id="employee-search" placeholder="Cari nama pegawai..." autocomplete="off">
                        </div>
                        <div class="employee-list" id="employee-list">
                            <?php foreach ($daftarPegawai as $pegawai): ?>
                                <div class="employee-item" data-nip="<?= htmlspecialchars($pegawai['nip']) ?>">
                                    <input type="checkbox" class="employee-checkbox" id="emp_<?= htmlspecialchars($pegawai['nip']) ?>">
                                    <label for="emp_<?= htmlspecialchars($pegawai['nip']) ?>" class="employee-name">
                                        <?= htmlspecialchars($pegawai['nama_pegawai']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="selected-employees" id="selected-employees"></div>
                    
                    <div style="margin-top: 10px;">
                        <input type="checkbox" id="tambah_pegawai_luar" style="margin-right: 8px;">
                        <label for="tambah_pegawai_luar" style="font-size: 14px; color: #374151;">Tambah pegawai eksternal</label>
                    </div>
                    
                    <div class="pegawai-luar-form" id="pegawai_luar_form">
                        <div class="tugas-external-fields">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                                <input type="text" class="form-input" id="nama_luar" placeholder="Nama Lengkap">
                                <input type="text" class="form-input" id="nip_luar" placeholder="NIP (opsional)">
                                <input type="text" class="form-input" id="pangkat_luar" placeholder="Pangkat (opsional)">
                                <input type="text" class="form-input" id="golongan_luar" placeholder="Golongan (opsional)">
                            </div>
                            <div style="margin-bottom: 10px;">
                                <input type="text" class="form-input" id="jabatan_luar" placeholder="Jabatan/Instansi">
                            </div>
                        </div>
                        <div class="undangan-external-fields" style="display: none;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                                <input type="text" class="form-input" id="nama_luar_undangan" placeholder="Nama Lengkap">
                                <input type="text" class="form-input" id="jabatan_luar_undangan" placeholder="Jabatan/Instansi">
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary" id="tambah_luar" style="font-size: 12px; padding: 6px 12px;">
                            ➕ Tambah
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Jabatan Pejabat</label>
                    <select id="jabatan_pejabat" name="jabatan_pejabat" class="form-select" required>
                        <option value="">Pilih Jabatan</option>
                        <?php foreach (getPejabatJabatanList() as $jabatan): ?>
                            <option value="<?= htmlspecialchars($jabatan) ?>">
                                <?= htmlspecialchars($jabatan) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label required">Nama Pejabat</label>
                    <select id="nama_pejabat" name="nama_pejabat" class="form-select" required>
                        <option value="">Pilih Nama Pejabat</option>
                        <?php foreach ($pejabatList as $pejabat): ?>
                            <option value="<?= htmlspecialchars($pejabat['nip']) ?>">
                                <?= htmlspecialchars($pejabat['nama']) ?>
                            </option>
                        <?php endforeach; ?> 
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Tembusan (Opsional)</label>
                    <textarea id="tembusan" name="tembusan" class="form-textarea" placeholder="Direktur Jenderal Sains dan Teknologi"></textarea>
                </div>

                <div class="form-buttons">
                    <?php if ($databaseStatus === 'connected'): ?>
                    <button type="submit" class="btn btn-success" name="action" value="export_word"> Download </button>
                    <?php else: ?>
                    <button type="button" class="btn btn-secondary" onclick="alert('Database tidak terhubung. Mohon perbaiki koneksi database untuk download DOCX.')"> Database Required</button>
                    <?php endif; ?>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </div>
            </form>
        </div>

        <div class="preview-section generator-surat">
            <div class="preview-header generator-surat">
                <h3 id="preview-title">Preview Surat Tugas</h3>
            </div>
            <div class="preview-content" id="previewContent">
                <div class="preview-placeholder generator-surat">
                    <p>Preview surat akan muncul di sini setelah form diisi</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Update indicator -->
    <div class="update-indicator" id="updateIndicator">
        ✓ Preview diperbarui
    </div>

    <!-- Success message -->
    <div class="success-message" id="successMessage"></div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Global function untuk mencari data pegawai berdasarkan NIP
        window.findPegawaiByNip = function(nip) {
            const pegawaiData = <?= json_encode($daftarPegawai) ?>;
            return pegawaiData.find(p => p.nip === nip) || {
                nama_pegawai: 'Data tidak ditemukan',
                nip: nip,
                pangkat: '-',
                golongan: '-',
                jabatan: '-'
            };
        };
    </script>
    <script src="assets/generator_surat.js"></script>
</body>
</html>