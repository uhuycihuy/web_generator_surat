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
    <title>Generator Surat Tugas - Kementerian Pendidikan Tinggi, Sains, dan Teknologi</title>
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
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
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
            background: linear-gradient(135deg, #3b82f6, #1e40af);
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
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
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

        .preview-section {
            background: #1e40af;
            border-radius: 12px;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(30, 64, 175, 0.3);
        }

        .preview-header {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
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
                radial-gradient(circle at 25% 25%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(147, 197, 253, 0.1) 0%, transparent 50%);
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
        }

        .select2-container--default .select2-selection--multiple {
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
            min-height: 45px !important;
            background: #fafbfc !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
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
            <a href="surat_undangan.php" class="header-btn">✉️ Surat Undangan</a>
        </div>
    </header>

    <div class="container">
        <div class="form-section">
            <div class="form-header">
                <div class="form-icon">📝</div>
                <div class="form-title">
                    <h2>Generator Surat Tugas</h2>
                    <p>Lengkapi data untuk membuat surat</p>
                </div>
            </div>
            <form id="suratTugasForm" method="POST" action="../backend/controllers/SuratTugasController.php">
                <div class="form-group">
                    <label class="form-label required">Kegiatan/Acara</label>
                    <textarea id="acara" name="acara" class="form-textarea" placeholder="Contoh: Rekonsiliasi Kebutuhan dan Penyusunan Prognosis Anggaran Sekretariat Direktorat Jenderal Sains dan Teknologi" required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label required">Daftar Pegawai</label>
                    <select id="pegawai" name="pegawai[]" multiple="multiple" required>
                        <?php foreach ($daftarPegawai as $pegawai): ?>
                            <option value="<?= htmlspecialchars($pegawai['nip']) ?>">
                                <?= htmlspecialchars($pegawai['nama_pegawai']) ?> - <?= htmlspecialchars($pegawai['jabatan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <div style="margin-top: 10px;">
                        <input type="checkbox" id="tambah_pegawai_luar" style="margin-right: 8px;">
                        <label for="tambah_pegawai_luar" style="font-size: 14px; color: #374151;">Tambah pegawai eksternal</label>
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
                        <button type="button" class="btn btn-secondary" id="tambah_luar" style="font-size: 12px; padding: 6px 12px;">
                            ➕ Tambah
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Tanggal Awal Kegiatan</label>
                    <input type="date" id="tgl_mulai" name="tgl_mulai" class="form-input" value="2025-08-19" required>
                </div>

                <div class="form-group">
                    <label class="form-label required">Tanggal Akhir Kegiatan</label>
                    <input type="date" id="tgl_selesai" name="tgl_selesai" class="form-input" value="2025-08-20">
                    <small style="color: #6b7280; font-size: 12px;">Opsional jika satu hari</small>
                </div>

                <div class="form-group">
                    <label class="form-label required">Tempat Kegiatan</label>
                    <textarea id="lokasi" name="lokasi" class="form-textarea" placeholder="Contoh: Ruang Rapat Rektorat Lantai 9, Kampus Baru Depok, Universitas Indonesia, Jawa Barat 16424" required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label required">Nomor dan Tanggal DIPA</label>
                    <input type="text" id="dipa" name="dipa" class="form-input" placeholder="SP DIPA-139.05.1.693321/2025 tanggal 2 Desember 2024." value="SP DIPA-139.05.1.693321/2025 tanggal 2 Desember 2024">
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
                    <button type="submit" class="btn btn-success" name="action" value="export_word"> Download DOCX</button>
                    <?php else: ?>
                    <button type="button" class="btn btn-secondary" onclick="alert('Database tidak terhubung. Mohon perbaiki koneksi database untuk download DOCX.')"> Database Required</button>
                    <?php endif; ?>
                    <button type="reset" class="btn btn-secondary">🔄 Reset</button>
                </div>
            </form>
        </div>

        <div class="preview-section">
            <div class="preview-header">
                <h3>Preview Surat Tugas</h3>
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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

    <script>
        $(document).ready(function() {
            // Initialize Select2 for pegawai selection
            $('#pegawai').select2({
                placeholder: 'Pilih pegawai yang akan ditugaskan...',
                allowClear: true,
                width: '100%'
            }).on('select2:select', function(e) {
                // Show success message when selecting from database
                const selectedText = e.params.data.text;
                if (!selectedText.includes('(Eksternal)')) {
                    showSuccessMessage('✓ Pegawai berhasil dipilih');
                }
            }).on('select2:unselect', function(e) {
                // Show info when removing selection
                showSuccessMessage('ℹ Pegawai dihapus dari daftar');
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
                    const value = `L|${nama}|${nip || ''}|${pangkat || '-'}|${golongan || '-'}|${jabatan}`;
                    const text = `${nama} - ${jabatan} (Eksternal)`;
                    
                    // Add to select2
                    const newOption = new Option(text, value, true, true);
                    $('#pegawai').append(newOption).trigger('change');
                    
                    // Show success feedback
                    showSuccessMessage('✓ Pegawai eksternal berhasil ditambahkan');
                    
                    // Button animation
                    const btn = $(this);
                    btn.html('✓ Ditambahkan').addClass('btn-success').removeClass('btn-secondary');
                    setTimeout(() => {
                        btn.html('➕ Tambah').removeClass('btn-success').addClass('btn-secondary');
                    }, 2000);
                    
                    // Clear inputs
                    $('#nama_luar, #nip_luar, #pangkat_luar, #golongan_luar, #jabatan_luar').val('');
                } else {
                    alert('Mohon isi nama dan jabatan pegawai eksternal');
                }
            });



            // Function untuk generate preview
            function generatePreview() {
                const acara = $('#acara').val() || '[Acara belum diisi]';
                const tglMulai = $('#tgl_mulai').val();
                const tglSelesai = $('#tgl_selesai').val();
                const lokasi = $('#lokasi').val() || '[Lokasi belum diisi]';
                const dipa = $('#dipa').val() || 'SP DIPA-139.05.1.693321/2025 tanggal 2 Desember 2024';
                const jabatanPejabat = $('#jabatan_pejabat option:selected').text() || '[Jabatan belum dipilih]';
                const namaPejabat = $('#nama_pejabat option:selected').text() || '[Pejabat belum dipilih]';
                const nipPejabat = $('#nama_pejabat').val() || '';
                const tembusan = $('#tembusan').val();
                
                // Format tanggal
               // Format tanggal (sesuai PHP formatTanggalRange)
let tanggalFormatted = '[Tanggal belum diisi]';
if (tglMulai) {
    const startDate = new Date(tglMulai);
    const endDate = tglSelesai ? new Date(tglSelesai) : startDate;

    // Validasi tanggal akhir tidak boleh sebelum tanggal mulai
    if (endDate < startDate) {
        tanggalFormatted = 'Error: Tanggal akhir tidak boleh sebelum tanggal awal';
    } else {
        const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        const hari1 = hari[startDate.getDay()];
        const hari2 = hari[endDate.getDay()];

        const tgl1 = startDate.getDate();
        const tgl2 = endDate.getDate();

        const bln1 = bulan[startDate.getMonth()];
        const bln2 = bulan[endDate.getMonth()];

        const thn1 = startDate.getFullYear();
        const thn2 = endDate.getFullYear();

        if (bln1 === bln2 && thn1 === thn2) {
            // Sama bulan & tahun
            if (tgl1 === tgl2) {
                tanggalFormatted = `${hari1}, tanggal ${tgl1} ${bln1} ${thn1}`;
            } else {
                tanggalFormatted = `${hari1}-${hari2}, tanggal ${tgl1}-${tgl2} ${bln1} ${thn1}`;
            }
        } else {
            // Beda bulan atau tahun
            tanggalFormatted = `${hari1}, tanggal ${tgl1} ${bln1} ${thn1} - ${hari2}, ${tgl2} ${bln2} ${thn2}`;
        }
    }
}

                
                // Get selected pegawai
                const selectedPegawai = $('#pegawai').val() || [];
                let pegawaiRows = '';
                
                if (selectedPegawai.length > 0) {
                    selectedPegawai.forEach((nip, index) => {
                        const option = $(`#pegawai option[value="${nip}"]`);
                        const text = option.text();
                        
                        if (nip.startsWith('L|')) {
                            // Pegawai eksternal
                            const parts = nip.split('|');
                            let detailPegawai = `<strong>${parts[1] || 'Nama Eksternal'}</strong>`;
                            
                            // Tambahkan NIP jika ada dan tidak kosong
                            if (parts[2] && parts[2].trim() !== '' && parts[2] !== '-') {
                                detailPegawai += `<br>${parts[2]}`;
                            }
                            
                            // Tambahkan pangkat dan golongan jika ada dan tidak kosong
                            const pangkat = parts[3] && parts[3].trim() !== '' && parts[3] !== '-' ? parts[3] : '';
                            const golongan = parts[4] && parts[4].trim() !== '' && parts[4] !== '-' ? parts[4] : '';
                            
                            if (pangkat || golongan) {
                                const pangkatGolongan = [pangkat, golongan].filter(item => item).join(', ');
                                if (pangkatGolongan) {
                                    detailPegawai += `<br>${pangkatGolongan}`;
                                }
                            }
                            
                            pegawaiRows += `
                                <tr>
                                    <td style="text-align: center; border: 1px solid #000; padding: 6px;">${index + 1}.</td>
                                    <td style="border: 1px solid #000; padding: 6px;">
                                        ${detailPegawai}
                                    </td>
                                    <td style="border: 1px solid #000; padding: 6px;">${parts[5] || 'Jabatan Eksternal'}</td>
                                </tr>
                            `;
                        } else {
                            // Pegawai internal - cari data dari array
                            const pegawaiData = findPegawaiByNip(nip);
                            pegawaiRows += `
                                <tr>
                                    <td style="text-align: center; border: 1px solid #000; padding: 6px;">${index + 1}.</td>
                                    <td style="border: 1px solid #000; padding: 6px;">
                                        <strong>${pegawaiData.nama_pegawai}</strong><br>
                                        ${pegawaiData.nip}<br>
                                        ${pegawaiData.pangkat}, ${pegawaiData.golongan}
                                    </td>
                                    <td style="border: 1px solid #000; padding: 6px;">${pegawaiData.jabatan}</td>
                                </tr>
                            `;
                        }
                    });
                } else {
                    pegawaiRows = `
                        <tr>
                            <td colspan="3" style="text-align: center; font-style: italic; color: #666; border: 1px solid #000; padding: 6px;">
                                Belum ada pegawai yang dipilih
                            </td>
                        </tr>
                    `;
                }
                
                // Template preview
                const previewHTML = `
                    <div style="font-family: 'Times New Roman', serif; font-size: 11pt; line-height: 1.4; color: #000;">
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
                        
                        <div style="text-align: center; font-weight: bold; font-size: 12pt; margin: 20px 0; text-decoration: underline;">
                            <strong>SURAT TUGAS</strong><br>
                            <span style="font-weight: normal; font-size: 10pt;">Nomor: </span>
                        </div>
                        
                        <div style="margin: 15px 0; text-align: justify; line-height: 1.5;">
                            <p>Dalam rangka kegiatan ${acara}, dengan ini Sekretaris Direktorat Jenderal Sains dan Teknologi menugaskan kepada nama di bawah ini,</p>
                        </div>
                        
                        <table style="width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 10pt;">
                            <thead>
                                <tr>
                                    <th style="width: 40px; border: 1px solid #000; padding: 6px; background-color: #f5f5f5; text-align: center;">No.</th>
                                    <th style="width: 50%; border: 1px solid #000; padding: 6px; background-color: #f5f5f5; text-align: center;">Nama, NIP, Pangkat dan Golongan</th>
                                    <th style="width: 45%; border: 1px solid #000; padding: 6px; background-color: #f5f5f5; text-align: center;">Jabatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${pegawaiRows}
                            </tbody>
                        </table>
                        
                        <div style="margin: 15px 0; text-align: justify; line-height: 1.5;">
                            <p>Untuk hadir dan melaksanakan tugas dalam kegiatan dimaksud yang akan diselenggarakan pada hari ${tanggalFormatted}, bertempat di ${lokasi}</p>
                            
                            <br/>
                            <p>Biaya kegiatan dibebankan kepada DIPA Satuan Kerja Direktorat Jenderal Sains dan Teknologi, Nomor: ${dipa}.</p>
                            
                            <br/>
                            <p>Surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab dan yang bersangkutan diharapkan membuat laporan.</p>
                        </div>
                        
                        <div style="margin-top: 30px; display: table; width: 100%;">
                            <div style="display: table-cell; width: 50%; vertical-align: top;"></div>
                            <div style="display: table-cell; width: 50%; text-align: center; vertical-align: top;">
                                <div style="margin-bottom: 12px;"> ${jabatanPejabat} ,</div>
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

            // Auto preview on form change (debounced)
            let previewTimeout;
            $('#suratTugasForm input, #suratTugasForm textarea, #suratTugasForm select').on('input change', function() {
                clearTimeout(previewTimeout);
                previewTimeout = setTimeout(function() {
                    generatePreview();
                }, 1000);
            });

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

            // Load preview saat halaman pertama kali dibuka
            setTimeout(function() {
                generatePreview();
                showUpdateIndicator();
            }, 500);

            // Auto preview on form change
            $('#suratTugasForm input, #suratTugasForm textarea, #suratTugasForm select').on('input change', function() {
                clearTimeout(previewTimeout);
                previewTimeout = setTimeout(function() {
                    generatePreview();
                    showUpdateIndicator();
                }, 1000);
            });

            // Form validation
            $('#suratTugasForm').on('submit', function(e) {
                const pegawai = $('#pegawai').val();
                if (!pegawai || pegawai.length === 0) {
                    e.preventDefault();
                    alert('Mohon pilih minimal satu pegawai yang akan ditugaskan');
                    return false;
                }
            });

            // Set tanggal selesai minimal sama dengan tanggal mulai
            $('#tgl_mulai').change(function() {
                const tglMulai = $(this).val();
                $('#tgl_selesai').attr('min', tglMulai);
            });

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