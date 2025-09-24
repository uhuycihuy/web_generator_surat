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
            <form id="suratUndanganForm" method="POST" action="../backend/controllers/SuratUndanganController.php">


                <div class="form-group">
                    <label class="form-label required">Jenis Acara</label>
                    <select id="jenis_acara" name="jenis_acara" class="form-select">
                        <option value="offline">Offline</option>
                        <option value="online">Online</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label required">Hari, Tanggal</label>
                    <input type="date" id="hari_tanggal" name="hari_tanggal" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label required">Waktu</label>
                    <input type="text" id="waktu" name="waktu" class="form-input" placeholder="10.00 - 12.00 WIB">
                </div>

                <div class="form-group" id="tempat_group">
                    <label class="form-label required">Tempat</label>
                    <input type="text" id="tempat" name="tempat" class="form-input" placeholder="Ruang Rapat DJST">
                </div>

                <div class="form-group" id="media_group" style="display: none;">
                    <label class="form-label required">Media</label>
                    <input type="text" id="media" name="media" class="form-input" placeholder="Zoom">
                </div>

                <div class="form-group" id="rapat_id_group" style="display: none;">
                    <label class="form-label">Rapat ID</label>
                    <input type="text" id="rapat_id" name="rapat_id" class="form-input" placeholder="123456789">
                </div>

                <div class="form-group" id="sandi_group" style="display: none;">
                    <label class="form-label">Kata Sandi</label>
                    <input type="text" id="sandi" name="sandi" class="form-input" placeholder="abc123">
                </div>

                <div class="form-group" id="tautan_group" style="display: none;">
                    <label class="form-label">Tautan</label>
                    <input type="url" id="tautan" name="tautan" class="form-input" placeholder="https://zoom.us/j/123456">
                </div>

                <div class="form-group">
                    <label class="form-label required">Agenda</label>
                    <textarea id="agenda" name="agenda" class="form-textarea" placeholder="Deskripsi agenda rapat..." required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Narahubung</label>
                    <input type="text" id="narahubung" name="narahubung" class="form-input" placeholder="Budi Santoso">
                </div>

                <div class="form-group">
                    <label class="form-label">No. Narahubung</label>
                    <input type="text" id="no_narahubung" name="no_narahubung" class="form-input" placeholder="08123456789">
                </div>

                <div class="form-group">
                    <label class="form-label required">Pilih Peserta Undangan</label>
                    <select id="pegawai" name="pegawai[]" multiple="multiple" required>
                        <?php foreach ($daftarPegawai as $p): ?>
                            <option value="<?= htmlspecialchars($p['nip']) ?>"><?= htmlspecialchars($p['nama_pegawai']) ?> - <?= htmlspecialchars($p['jabatan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <div style="margin-top: 10px;">
                        <input type="checkbox" id="tambah_pegawai_luar" style="margin-right: 8px;">
                        <label for="tambah_pegawai_luar" style="font-size: 14px; color: #374151;">Tambah pegawai eksternal</label>
                    </div>
                    
                    <div class="pegawai-luar-form" id="pegawai_luar_form">
                        <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                            <input type="text" class="form-input" id="nama_luar" placeholder="Nama Lengkap" style="flex: 1;">
                            <input type="text" class="form-input" id="jabatan_luar" placeholder="Jabatan/Instansi" style="flex: 1;">
                        </div>
                        <button type="button" class="btn btn-primary" id="tambah_luar" style="font-size: 12px; padding: 6px 12px;">
                            ➕ Tambah
                        </button>
                    </div>
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
                    <textarea id="tembusan" name="tembusan" class="form-textarea" placeholder="Arsip\nKepala Bagian TU"></textarea>
                </div>

                <div class="form-buttons">
                    <button type="submit" name="action" value="export_word" class="btn btn-success">📄 Export ke Word</button>
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
                const jabatan = $('#jabatan_luar').val().trim();
                
                if (nama && jabatan) {
                    const value = `L|${nama}|${jabatan}`;
                    const text = `${nama} - ${jabatan} (Eksternal)`;
                    
                    // Add to select2
                    const newOption = new Option(text, value, true, true);
                    $('#pegawai').append(newOption).trigger('change');
                    
                    // Clear inputs
                    $('#nama_luar, #jabatan_luar').val('');
                } else {
                    alert('Mohon isi nama dan jabatan pegawai eksternal');
                }
            });

            // Toggle fields based on jenis_acara
            $('#jenis_acara').change(function() {
                const jenis = $(this).val();
                if (jenis === 'online') {
                    $('#tempat_group').hide();
                    $('#media_group, #rapat_id_group, #sandi_group, #tautan_group').show();
                } else {
                    $('#tempat_group').show();
                    $('#media_group, #rapat_id_group, #sandi_group, #tautan_group').hide();
                }
            });

            // Generate preview
            function generatePreview() {
                const jenis_acara = $('#jenis_acara').val();
                const hari_tanggal = $('#hari_tanggal').val();
                const waktu = $('#waktu').val();
                const tempat = $('#tempat').val();
                const media = $('#media').val();
                const rapat_id = $('#rapat_id').val();
                const sandi = $('#sandi').val();
                const tautan = $('#tautan').val();
                const agenda = $('#agenda').val();
                const narahubung = $('#narahubung').val();
                const no_narahubung = $('#no_narahubung').val();
                const tembusan = $('#tembusan').val();
                const nip_pejabat = $('#nama_pejabat').val();
                const nama_pejabat = nip_pejabat ? $('option:selected', '#nama_pejabat').text() : '';

                // Format tanggal
                let tanggalFormatted = '';
                if (hari_tanggal) {
                    const date = new Date(hari_tanggal);
                    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    tanggalFormatted = days[date.getDay()] + ', ' + date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear();
                }

                // Pegawai rows
                let undanganRows = '';
                const selectedPegawai = $('#pegawai').val() || [];
                selectedPegawai.forEach((nip, index) => {
                    const option = $(`#pegawai option[value="${nip}"]`);
                    const text = option.text();
                    
                    if (nip.startsWith('L|')) {
                        // Pegawai eksternal
                        const parts = nip.split('|');
                        undanganRows += `
                            <tr>
                                <td style="text-align: center; border: 1px solid #000; padding: 8px; width: 30px;">${index + 1}.</td>
                                <td style="border: 1px solid #000; padding: 8px;">${parts[1] || 'Nama Eksternal'}, ${parts[2] || 'Jabatan Eksternal'}</td>
                            </tr>
                        `;
                    } else {
                        // Pegawai internal
                        undanganRows += `
                            <tr>
                                <td style="text-align: center; border: 1px solid #000; padding: 8px; width: 30px;">${index + 1}.</td>
                                <td style="border: 1px solid #000; padding: 8px;">${text}</td>
                            </tr>
                        `;
                    }
                });

                const previewHTML = `
                    <div style="font-family: 'Times New Roman', serif; font-size: 11pt; line-height: 1.4; color: #000;">
                        <!-- Header -->
                        <div style="text-align: center; margin-bottom: 25px; border-bottom: 2px solid #000; padding-bottom: 12px;">
                            <table style="width: 100%; border: none;">
                                <tr>
                                    <td style="width: 70px; border: none; text-align: center; vertical-align: middle;">
                                        <div style="width: 60px; height: 60px; border: 1px solid #ddd; display: flex; align-items: center; justify-content: center; font-size: 7pt; color: #666;">
                                            LOGO<br>KEMENDIKTI
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
                            <p>Dalam rangka membangun <em>${agenda || 'agenda'}</em>, kami bermaksud menyelenggarakan rapat yang ditujukan bagi para dosen dan peneliti. Sehubungan dengan hal tersebut, kami mengundang Bapak/Ibu untuk berkenan hadir dan berpartisipasi dalam rapat yang akan dilaksanakan pada</p>
                        </div>

                        <!-- Detail Acara -->
                        <table style="width: 100%; margin: 10px 0;">
                            <tr>
                                <td style="width: 120px; vertical-align: top;">hari, tanggal</td>
                                <td style="width: 10px; vertical-align: top;">:</td>
                                <td style="vertical-align: top;">${tanggalFormatted}</td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top;">waktu</td>
                                <td style="vertical-align: top;">:</td>
                                <td style="vertical-align: top;">${waktu}</td>
                            </tr>
                            ${jenis_acara === 'online' ? `
                            <tr>
                                <td style="vertical-align: top;">media</td>
                                <td style="vertical-align: top;">:</td>
                                <td style="vertical-align: top;">${media}</td>
                            </tr>
                            ${rapat_id ? `<tr><td style="vertical-align: top;">rapat id</td><td>:</td><td>${rapat_id}</td></tr>` : ''}
                            ${sandi ? `<tr><td style="vertical-align: top;">kata sandi</td><td>:</td><td>${sandi}</td></tr>` : ''}
                            ${tautan ? `<tr><td style="vertical-align: top;">tautan</td><td>:</td><td><a href="${tautan}">${tautan}</a></td></tr>` : ''}
                            ` : `
                            <tr>
                                <td style="vertical-align: top;">tempat</td>
                                <td style="vertical-align: top;">:</td>
                                <td style="vertical-align: top;">${tempat}</td>
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
                            <p>Besar harapan kami Bapak/Ibu dapat meluangkan waktu untuk hadir dalam rapat dimaksud guna memberikan pemahaman yang lebih mendalam mengenai pentingnya integritas akademik, khususnya dalam pengelolaan jurnal ilmiah, serta memperkuat kolaborasi antar civitas akademika dan lembaga riset.</p>
                            <p>Untuk informasi lebih lanjut mengenai rapat dan konfirmasi kehadiran, tim Bapak/Ibu dapat menghubungi ${narahubung} di nomor ${no_narahubung}.</p>
                            <p>Demikian surat ini kami sampaikan. Atas perhatian dan kerja sama yang baik, kami ucapkan terima kasih.</p>
                        </div>

                        <!-- TTD -->
                        <div style="margin-top: 30px; display: table; width: 100%;">
                            <div style="display: table-cell; width: 50%; vertical-align: top;"></div>
                            <div style="display: table-cell; width: 50%; text-align: center; vertical-align: top;">
                                <div style="margin-bottom: 12px;">Sekretaris,</div>
                                <div style="margin: 60px 0 12px 0;"></div>
                                <div style="font-weight: bold;">${nama_pejabat}</div>
                                <div style="font-size: 9pt;">NIP ${nip_pejabat}</div>
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
                                    ${undanganRows || '<tr><td colspan="2" style="text-align: center; font-style: italic; color: #666; border: 1px solid #000; padding: 8px;">Belum ada yang diundang</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
                
                $('#previewContent').html(previewHTML);
            }

            // Show update indicator function
            function showUpdateIndicator() {
                const indicator = document.getElementById('updateIndicator');
                indicator.classList.add('show');
                setTimeout(() => {
                    indicator.classList.remove('show');
                }, 1500);
            }



            // Auto preview on form change
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

            // Form validation
            $('#suratUndanganForm').on('submit', function(e) {
                const pegawai = $('#pegawai').val();
                if (!pegawai || pegawai.length === 0) {
                    e.preventDefault();
                    alert('Mohon pilih minimal satu peserta undangan');
                    return false;
                }
            });
        });
    </script>
</body>
</html>