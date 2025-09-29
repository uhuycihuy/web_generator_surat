<?php
// Integrasi dengan database real
require_once '../backend/config/database.php';
require_once '../backend/models/Pegawai.php';
require_once '../backend/helpers/utils.php';
require_once '../backend/helpers/PegawaiHelper.php';

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
    <title>Generator Surat Undangan - Kementerian Pendidikan Tinggi, Sains, dan Teknologi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            line-height: 1.6;
        }

        .header {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo::before {
            content: "🏛️";
            font-size: 20px;
        }

        .header-title {
            font-size: 16px;
            font-weight: 500;
        }

        .header-buttons {
            display: flex;
            gap: 10px;
        }

        .header-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .header-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-1px);
            color: white;
            text-decoration: none;
        }

        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
            height: calc(100vh - 80px);
        }

        .form-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow-y: auto;
        }

        .form-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
        }

        .form-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .form-title {
            flex: 1;
        }

        .form-title h2 {
            color: #374151;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .form-title p {
            color: #6b7280;
            font-size: 14px;
        }

        .jenis-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
        }

        .jenis-option {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            font-weight: 500;
            color: #6b7280;
        }

        .jenis-option.active {
            border-color: #10b981;
            background: #10b981;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .jenis-option:hover:not(.active) {
            border-color: #10b981;
            color: #10b981;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
            transition: transform 0.2s ease;
        }

        .form-group:hover {
            transform: translateY(-1px);
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
            font-size: 14px;
        }

        .required::after {
            content: " *";
            color: #ef4444;
        }

        .form-input, .form-textarea, .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #fafbfc;
        }

        .form-input:focus, .form-textarea:focus, .form-select:focus {
            outline: none;
            border-color: #10b981;
            background: white;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
            transform: translateY(-1px);
        }

        .form-textarea {
            min-height: 80px;
            resize: vertical;
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
        }

        .time-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .form-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-success {
            background: #10b981;
            color: white;
            border: 1px solid #059669;
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-1px);
            color: white;
            text-decoration: none;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
            transform: translateY(-1px);
            color: #374151;
            text-decoration: none;
        }

        .preview-section {
            background: #059669;
            border-radius: 12px;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(5, 150, 105, 0.3);
        }

        .preview-header {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            padding: 20px 25px;
            text-align: center;
        }

        .preview-header h3 {
            font-size: 22px;
            margin-bottom: 5px;
        }

        .preview-content {
            background: #f8fafc;
            height: calc(100% - 70px);
            margin: 15px;
            border-radius: 8px;
            overflow-y: auto;
            padding: 20px;
            padding-bottom: 50px;
            scroll-behavior: smooth;
        }

        .preview-content::-webkit-scrollbar {
            width: 8px;
        }

        .preview-content::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .preview-content::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .preview-content::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .preview-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-style: italic;
            height: 100%;
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(16, 185, 129, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(52, 211, 153, 0.1) 0%, transparent 50%);
        }

        .update-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            z-index: 1000;
            transform: translateY(-100px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .update-indicator.show {
            transform: translateY(0);
            opacity: 1;
        }

        .success-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            z-index: 1001;
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .success-message.show {
            transform: translateX(0);
            opacity: 1;
        }

        .success-message.info {
            background: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                height: auto;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .header-buttons {
                justify-content: center;
            }

            .time-inputs {
                grid-template-columns: 1fr;
            }
        }

        .select2-container--default .select2-selection--multiple {
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
            min-height: 45px !important;
            background: #fafbfc !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
        }

        .pegawai-luar-form {
            display: none;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            border: 1px solid #e5e7eb;
        }

        .select2-selection__choice {
            animation: slideInRight 0.3s ease-out;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #10b981 !important;
            border-color: #059669 !important;
            color: white !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            background-color: rgba(255,255,255,0.2) !important;
            color: white !important;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(20px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .btn.btn-success {
            animation: pulse 0.6s ease-in-out;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-left">
            <div class="logo"></div>
            <div class="header-title">Kementerian Pendidikan Tinggi, Sains, dan Teknologi</div>
        </div>
        <div class="header-buttons">
            <a href="../index.php" class="header-btn">📄 Generator Surat</a>
            <a href="surat_tugas.php" class="header-btn">📝 Surat Tugas</a>
        </div>
    </header>

    <div class="container">
        <div class="form-section">
            <div class="form-header">
                <div class="form-icon">✉️</div>
                <div class="form-title">
                    <h2>Generator Surat Undangan</h2>
                    <p>Lengkapi data untuk membuat undangan</p>
                </div>
            </div>

            <!-- Jenis Undangan Selector -->
            <div class="jenis-selector">
                <div class="jenis-option active" data-jenis="offline">
                    📍 Offline (Tatap Muka)
                </div>
                <div class="jenis-option" data-jenis="online">
                    💻 Online (Virtual)
                </div>
            </div>

            <form id="suratUndanganForm" method="POST" action="../backend/controllers/SuratUndanganController.php">
                <input type="hidden" name="jenis_undangan" id="jenis_undangan" value="offline">

                <div class="form-group">
                    <label class="form-label required">Acara</label>
                    <textarea id="acara" name="acara" class="form-textarea" placeholder="Contoh: Rapat Koordinasi Penyusunan Program Kerja Tahun 2025" required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label required">Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label required">Waktu</label>
                    <div class="time-inputs">
                        <div>
                            <input type="time" id="waktu_awal" name="waktu_awal" class="form-input" required>
                            <small style="color: #6b7280; font-size: 12px;">Waktu mulai</small>
                        </div>
                        <div>
                            <input type="time" id="waktu_akhir" name="waktu_akhir" class="form-input" placeholder="selesai">
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
                    <input type="text" id="media" name="media" class="form-input" placeholder="Contoh: Zoom, Microsoft Teams, Google Meet">
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
                    <textarea id="agenda" name="agenda" class="form-textarea" placeholder="Contoh: 1. Pembukaan&#10;2. Laporan kegiatan periode sebelumnya&#10;3. Pembahasan program kerja baru&#10;4. Penutup" required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Kalimat Opsional</label>
                    <textarea id="kalimat_opsional" name="kalimat_opsional" class="form-textarea" placeholder="Contoh: Besar harapan kami Bapak/Ibu dapat hadir tepat waktu."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Narahubung</label>
                    <input type="text" id="narahubung" name="narahubung" class="form-input" placeholder="Contoh: Budi Santoso">
                </div>

                <div class="form-group">
                    <label class="form-label">Nomor Narahubung</label>
                    <input type="tel" id="no_narahubung" name="no_narahubung" class="form-input" placeholder="Contoh: 08123456789">
                </div>

                <div class="form-group">
                    <label class="form-label">Sapaan</label>
                    <select id="gender" name="gender" class="form-select">
                        <option value="Saudara">Saudara</option>
                        <option value="Saudari">Saudari</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label required">Peserta Undangan</label>
                    <select id="pegawai" name="pegawai[]" multiple="multiple" required>
                        <?php foreach ($daftarPegawai as $p): ?>
                            <option value="<?= htmlspecialchars($p['nip']) ?>"><?= htmlspecialchars($p['nama_pegawai']) ?> - <?= htmlspecialchars($p['jabatan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <div style="margin-top: 10px;">
                        <input type="checkbox" id="tambah_pegawai_luar" style="margin-right: 8px;">
                        <label for="tambah_pegawai_luar" style="font-size: 14px; color: #374151;">Tambah peserta eksternal</label>
                    </div>
                    
                    <div class="pegawai-luar-form" id="pegawai_luar_form">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                            <input type="text" class="form-input" id="nama_luar" placeholder="Nama Lengkap">
                            <input type="text" class="form-input" id="nip_luar" placeholder="NIP (opsional)">
                            <input type="text" class="form-input" id="pangkat_luar" placeholder="Pangkat (opsional)">
                            <input type="text" class="form-input" id="golongan_luar" placeholder="Golongan (opsional)">
                        </div>
                        <div style="margin-bottom: 10px;">
                            <input type="text" class="form-input" id="jabatan_luar" placeholder="Jabatan/Instansi">
                        </div>
                        <button type="button" class="btn btn-primary" id="tambah_luar" style="font-size: 12px; padding: 6px 12px;">
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
                    <label class="form-label required">Pejabat Penandatangan</label>
                    <select id="nama_pejabat" name="nama_pejabat" class="form-select" required>
                        <option value="">Pilih Pejabat</option>
                        <?php foreach ($pejabatList as $pj): ?>
                            <option value="<?= htmlspecialchars($pj['nip']) ?>"><?= htmlspecialchars($pj['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Tembusan</label>
                    <textarea id="tembusan" name="tembusan" class="form-textarea" placeholder="Contoh:&#10;Arsip&#10;Direktur Jenderal Sains dan Teknologi"></textarea>
                </div>

                <div class="form-buttons">
                    <?php if ($databaseStatus === 'connected'): ?>
                    <button type="submit" name="action" value="export_word" class="btn btn-success">📄 Download DOCX</button>
                    <?php else: ?>
                    <button type="button" class="btn btn-secondary" onclick="alert('Database tidak terhubung. Mohon perbaiki koneksi database untuk download DOCX.')">Database Required</button>
                    <?php endif; ?>
                    <button type="reset" class="btn btn-secondary">🔄 Reset</button>
                </div>
            </form>
            
        </div>

        <div class="preview-section">
            <div class="preview-header">
                <h3>Preview Surat Undangan</h3>
            </div>
            <div class="preview-content" id="previewContent">
                <div class="preview-placeholder">
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('#pegawai').select2({
                placeholder: 'Pilih peserta undangan...',
                allowClear: true,
                width: '100%'
            }).on('select2:select', function(e) {
                // Show success message when selecting from database
                const selectedText = e.params.data.text;
                if (!selectedText.includes('(Eksternal)')) {
                    showSuccessMessage('✓ Peserta berhasil dipilih');
                }
            }).on('select2:unselect', function(e) {
                // Show info when removing selection
                showSuccessMessage('ℹ Peserta dihapus dari daftar');
            });

            // Jenis undangan selector
            $('.jenis-option').click(function() {
                $('.jenis-option').removeClass('active');
                $(this).addClass('active');
                
                const jenis = $(this).data('jenis');
                $('#jenis_undangan').val(jenis);
                
                if (jenis === 'online') {
                    $('.offline-fields').hide();
                    $('.online-fields').show();
                    $('#lokasi').removeAttr('required');
                    $('#media').attr('required', 'required');
                } else {
                    $('.online-fields').hide();
                    $('.offline-fields').show();
                    $('#media').removeAttr('required');
                    $('#lokasi').attr('required', 'required');
                }
                
                // Update preview
                generatePreview();
            });

            // Toggle pegawai luar form
            $('#tambah_pegawai_luar').change(function() {
                if ($(this).is(':checked')) {
                    $('#pegawai_luar_form').slideDown();
                } else {
                    $('#pegawai_luar_form').slideUp();
                }
            });

            // Add external participant
            $('#tambah_luar').click(function() {
                const nama = $('#nama_luar').val().trim();
                const nip = $('#nip_luar').val().trim();
                const pangkat = $('#pangkat_luar').val().trim();
                const golongan = $('#golongan_luar').val().trim();
                const jabatan = $('#jabatan_luar').val().trim();
                
                if (nama && jabatan) {
                    const value = `L|${nama}|${nip || '-'}|${pangkat || '-'}|${golongan || '-'}|${jabatan}`;
                    const text = `${nama} - ${jabatan} (Eksternal)`;
                    
                    // Add to select2
                    const newOption = new Option(text, value, true, true);
                    $('#pegawai').append(newOption).trigger('change');
                    
                    // Show success feedback
                    showSuccessMessage('✓ Peserta eksternal berhasil ditambahkan');
                    
                    // Button animation
                    const btn = $(this);
                    btn.html('✓ Ditambahkan').addClass('btn-success').removeClass('btn-primary');
                    setTimeout(() => {
                        btn.html('➕ Tambah').removeClass('btn-success').addClass('btn-primary');
                    }, 2000);
                    
                    // Clear inputs
                    $('#nama_luar, #nip_luar, #pangkat_luar, #golongan_luar, #jabatan_luar').val('');
                } else {
                    alert('Mohon isi nama dan jabatan peserta eksternal');
                }
            });

            // Generate preview function
            function generatePreview() {
                const jenisUndangan = $('#jenis_undangan').val();
                const acara = $('#acara').val() || '[Acara belum diisi]';
                const tanggal = $('#tanggal').val();
                const waktuAwal = $('#waktu_awal').val();
                const waktuAkhir = $('#waktu_akhir').val();
                const lokasi = $('#lokasi').val() || '[Tempat belum diisi]';
                const media = $('#media').val() || '[Media belum diisi]';
                const rapatId = $('#rapat_id').val() || '';
                const kataSandi = $('#kata_sandi').val() || '';
                const tautan = $('#tautan').val() || '';
                const agenda = $('#agenda').val() || '[Agenda belum diisi]';
                const kalimatOpsional = $('#kalimat_opsional').val();
                const narahubung = $('#narahubung').val() || '';
                const noNarahubung = $('#no_narahubung').val() || '';
                const gender = $('#gender').val() || 'Saudara';
                const tembusan = $('#tembusan').val();
                const nipPejabat = $('#nama_pejabat').val() || '';
                const namaPejabat = nipPejabat ? $('#nama_pejabat option:selected').text() : '[Pejabat belum dipilih]';
                const jabatanPejabat = $('#jabatan_pejabat option:selected').text() || '[Jabatan belum dipilih]';

                // Format tanggal Indonesia
                let tanggalFormatted = '[Tanggal belum diisi]';
                if (tanggal) {
                    const date = new Date(tanggal);
                    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    tanggalFormatted = days[date.getDay()] + ', ' + date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear();
                }

                // Format waktu
                let waktuFormatted = '[Waktu belum diisi]';
                if (waktuAwal) {
                    waktuFormatted = waktuAwal;
                    if (waktuAkhir) {
                        waktuFormatted += ' - ' + waktuAkhir + ' WIB';
                    } else {
                        waktuFormatted += ' - selesai WIB';
                    }
                }

                // Get selected pegawai
                const selectedPegawai = $('#pegawai').val() || [];
                let undanganRows = '';
                
                if (selectedPegawai.length > 0) {
                    let currentNo = 1;
                    selectedPegawai.forEach((nip) => {
                        if (nip.startsWith('L|')) {
                            // Peserta eksternal
                            const parts = nip.split('|');
                            let namaJabatan = `${parts[1] || 'Nama Eksternal'}`;
                            
                            // Tambahkan NIP jika ada dan tidak kosong
                            if (parts[2] && parts[2].trim() !== '' && parts[2] !== '-') {
                                namaJabatan += `<br>NIP ${parts[2]}`;
                            }
                            
                            // Tambahkan pangkat dan golongan jika ada dan tidak kosong
                            const pangkat = parts[3] && parts[3].trim() !== '' && parts[3] !== '-' ? parts[3] : '';
                            const golongan = parts[4] && parts[4].trim() !== '' && parts[4] !== '-' ? parts[4] : '';
                            
                            if (pangkat || golongan) {
                                const pangkatGolongan = [pangkat, golongan].filter(item => item).join(', ');
                                if (pangkatGolongan) {
                                    namaJabatan += `<br>${pangkatGolongan}`;
                                }
                            }
                            
                            namaJabatan += `<br>${parts[5] || 'Jabatan Eksternal'}`;
                            
                            undanganRows += `
                                <tr>
                                    <td style="text-align: center; border: 1px solid #000; padding: 8px; width: 30px;">${currentNo}.</td>
                                    <td style="border: 1px solid #000; padding: 8px;">${namaJabatan}</td>
                                </tr>
                            `;
                        } else {
                            // Peserta internal
                            const pegawaiData = findPegawaiByNip(nip);
                            const namaJabatan = `${pegawaiData.nama_pegawai}<br>NIP ${pegawaiData.nip}<br>${pegawaiData.pangkat}, ${pegawaiData.golongan}<br>${pegawaiData.jabatan}`;
                            undanganRows += `
                                <tr>
                                    <td style="text-align: center; border: 1px solid #000; padding: 8px; width: 30px;">${currentNo}.</td>
                                    <td style="border: 1px solid #000; padding: 8px;">${namaJabatan}</td>
                                </tr>
                            `;
                        }
                        currentNo++;
                    });
                } else {
                    undanganRows = `
                        <tr>
                            <td colspan="2" style="text-align: center; font-style: italic; color: #666; border: 1px solid #000; padding: 8px;">
                                Belum ada peserta yang dipilih
                            </td>
                        </tr>
                    `;
                }

                // Template preview
                const previewHTML = `
                    <div style="font-family: 'Times New Roman', serif; font-size: 11pt; line-height: 1.4; color: #000;">
                        <!-- Header -->
                        <div style="text-align: center; margin-bottom: 25px; border-bottom: 2px solid #000; padding-bottom: 12px;">
                            <table style="width: 100%; border: none;">
                                <tr>
                                    <td style="width: 70px; border: none; text-align: center; vertical-align: middle;">
                                        <div style="width: 60px; height: 60px; border: 1px solid #ddd; display: flex; align-items: center; justify-content: center; font-size: 7pt; color: #666;">
                                            <img src="assets/dikti.png" alt="LOGO KEMENDIKTI" style="max-width: 100%; max-height: 100%;">
                                        </div>
                                    </td>
                                    <td style="border: none; text-align: center; vertical-align: middle;">
                                        <div style="font-size: 11pt; font-weight: bold; line-height: 1.2;">
                                            KEMENTERIAN PENDIDIKAN TINGGI, SAINS,<br>
                                            DAN TEKNOLOGI<br>
                                            <strong>DIREKTORAT JENDERAL SAINS DAN TEKNOLOGI</strong>
                                        </div>
                                        <div style="font-size: 9pt; margin-top: 6px; line-height: 1.3;">
                                            Jalan Jenderal Sudirman, Senayan, Jakarta 10270<br>
                                            Telepon (021) 57946104, Pusat Panggilan ULT DIKTI 126<br>
                                            Laman <span style="text-decoration: underline;">www.kemdiktisaintek.go.id</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Nomor, Lampiran, Hal -->
                        <div style="margin: 20px 0; font-size: 11pt;">
                            <div><strong>Nomor</strong>: ___________________</div>
                            <div><strong>Lampiran</strong>: satu lembar</div>
                            <div><strong>Hal</strong>: Undangan</div>
                        </div>

                        <!-- Yth. -->
                        <div style="margin: 20px 0; font-size: 11pt;">
                            <div style="font-weight: bold;">Yth. Peserta Kegiatan</div>
                            <div>(daftar terlampir)</div>
                        </div>

                        <!-- Isi -->
                        <div style="text-align: justify; line-height: 1.5; margin: 15px 0;">
                            <p>Dalam rangka kegiatan ${acara}, Sehubungan dengan hal tersebut, kami mengundang ${gender} untuk berkenan hadir dan berpartisipasi dalam rapat yang akan dilaksanakan pada:</p>
                        </div>

                        <!-- Detail Acara -->
                        <table style="width: 100%; margin: 10px 0; font-size: 11pt;">
                            <tr>
                                <td style="width: 120px; vertical-align: top;">hari, tanggal</td>
                                <td style="width: 10px; vertical-align: top;">:</td>
                                <td style="vertical-align: top;">${tanggalFormatted}</td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top;">waktu</td>
                                <td style="vertical-align: top;">:</td>
                                <td style="vertical-align: top;">${waktuFormatted}</td>
                            </tr>
                            ${jenisUndangan === 'online' ? `
                            <tr>
                                <td style="vertical-align: top;">media</td>
                                <td style="vertical-align: top;">:</td>
                                <td style="vertical-align: top;">${media}</td>
                            </tr>
                            ${rapatId ? `
                            <tr>
                                <td style="vertical-align: top;">rapat id</td>
                                <td style="vertical-align: top;">:</td>
                                <td style="vertical-align: top;">${rapatId}</td>
                            </tr>
                            ` : ''}
                            ${kataSandi ? `
                            <tr>
                                <td style="vertical-align: top;">kata sandi</td>
                                <td style="vertical-align: top;">:</td>
                                <td style="vertical-align: top;">${kataSandi}</td>
                            </tr>
                            ` : ''}
                            ${tautan ? `
                            <tr>
                                <td style="vertical-align: top;">tautan</td>
                                <td style="vertical-align: top;">:</td>
                                <td style="vertical-align: top;"><a href="${tautan}">${tautan}</a></td>
                            </tr>
                            ` : ''}
                            ` : `
                            <tr>
                                <td style="vertical-align: top;">tempat</td>
                                <td style="vertical-align: top;">:</td>
                                <td style="vertical-align: top;">${lokasi}</td>
                            </tr>
                            `}
                            <tr>
                                <td style="vertical-align: top;">agenda</td>
                                <td style="vertical-align: top;">:</td>
                                <td style="vertical-align: top;">${agenda.replace(/\n/g, '<br>')}</td>
                            </tr>
                        </table>

                        <!-- Penutup -->
                        <div style="margin: 15px 0; text-align: justify; line-height: 1.5;">
                            ${kalimatOpsional ? `<p>${kalimatOpsional}</p><br/>` : ''}
                            ${narahubung && noNarahubung ? `
                            <p>Untuk informasi lebih lanjut mengenai rapat dan konfirmasi kehadiran, tim ${gender} dapat menghubungi ${narahubung} di nomor ${noNarahubung}.</p>
                            <br/>
                            ` : ''}
                            <p>Demikian surat ini kami sampaikan. Atas perhatian dan kerja sama yang baik, kami ucapkan terima kasih.</p>
                        </div>

                        <!-- TTD -->
                        <div style="margin-top: 30px; display: table; width: 100%;">
                            <div style="display: table-cell; width: 50%; vertical-align: top;"></div>
                            <div style="display: table-cell; width: 50%; text-align: center; vertical-align: top;">
                                <div style="margin-bottom: 12px;">${jabatanPejabat},</div>
                                <div style="margin: 60px 0 12px 0;"></div>
                                <div style="font-weight: bold;">${namaPejabat}</div>
                                <div style="font-size: 9pt;">NIP ${nipPejabat}</div>
                            </div>
                        </div>

                        ${tembusan ? `
                        <div style="margin-top: 30px; clear: both;">
                            <strong>Tembusan:</strong><br>
                            ${tembusan.replace(/\n/g, '<br>')}
                        </div>
                        ` : ''}

                        <!-- Lampiran -->
                        <div style="page-break-before: always; margin-top: 30px;">
                            <div style="text-align: center; font-weight: bold; font-size: 12pt; margin-bottom: 20px;">
                                Lampiran<br>
                                Nomor: ___________________<br>
                                Tanggal: ${tanggalFormatted}
                            </div>
                            <div style="margin-bottom: 15px; text-align: center; font-weight: bold;">
                                <em>Yth.</em>
                            </div>
                            <table style="width: 100%; border-collapse: collapse; margin: 12px 0;">
                                <thead>
                                    <tr>
                                        <th style="border: 1px solid #000; padding: 8px; background-color: #f5f5f5; text-align: center; width: 30px;">No.</th>
                                        <th style="border: 1px solid #000; padding: 8px; background-color: #f5f5f5; text-align: center;">Nama dan Jabatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${undanganRows}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
                
                $('#previewContent').html(previewHTML);
            }

            // Function untuk mencari data pegawai berdasarkan NIP
            function findPegawaiByNip(nip) {
                const pegawaiData = <?= json_encode($daftarPegawai) ?>;
                return pegawaiData.find(p => p.nip === nip) || {
                    nama_pegawai: 'Data tidak ditemukan',
                    nip: nip,
                    pangkat: '-',
                    golongan: '-',
                    jabatan: '-'
                };
            }

            // Show update indicator function
            function showUpdateIndicator() {
                const indicator = document.getElementById('updateIndicator');
                indicator.classList.add('show');
                setTimeout(() => {
                    indicator.classList.remove('show');
                }, 1500);
            }

            // Show success message function
            function showSuccessMessage(message) {
                const successMsg = document.getElementById('successMessage');
                successMsg.textContent = message;
                
                // Remove existing classes
                successMsg.classList.remove('info');
                
                // Add info class for info messages
                if (message.includes('ℹ')) {
                    successMsg.classList.add('info');
                }
                
                successMsg.classList.add('show');
                setTimeout(() => {
                    successMsg.classList.remove('show');
                    setTimeout(() => {
                        successMsg.classList.remove('info');
                    }, 400);
                }, 3000);
            }

            // Auto preview on form change (debounced)
            let previewTimeout;
            $('#suratUndanganForm input, #suratUndanganForm textarea, #suratUndanganForm select').on('input change', function() {
                clearTimeout(previewTimeout);
                previewTimeout = setTimeout(() => {
                    generatePreview();
                    showUpdateIndicator();
                }, 1000);
            });

            // Initial preview
            setTimeout(() => {
                generatePreview();
                showUpdateIndicator();
            }, 500);

            // Form validation
            $('#suratUndanganForm').on('submit', function(e) {
                const pegawai = $('#pegawai').val();
                if (!pegawai || pegawai.length === 0) {
                    e.preventDefault();
                    alert('Mohon pilih minimal satu peserta undangan');
                    return false;
                }
            });

            // Set default tanggal ke hari ini + 7 hari
            const today = new Date();
            today.setDate(today.getDate() + 7);
            const defaultDate = today.toISOString().split('T')[0];
            $('#tanggal').val(defaultDate);

            // Add smooth animations on load
            $('.form-group').each(function(index) {
                $(this).css({
                    'opacity': '0',
                    'transform': 'translateY(20px)'
                });
                
                setTimeout(() => {
                    $(this).css({
                        'transition': 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)',
                        'opacity': '1',
                        'transform': 'translateY(0)'
                    });
                }, index * 100);
            });
        });
    </script>
</body>
</html>