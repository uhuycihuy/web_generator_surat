<?php
// Integrasi dengan database real
require_once '../backend/config/database.php';
require_once '../backend/models/Pegawai.php';
require_once '../backend/helpers/utils.php';

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
    <title>Generator Surat Tugas - DIRJEN SAINTEK</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .preview-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-height: 600px;
            overflow-y: auto;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #2c3e50;
            box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(44, 62, 80, 0.4);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 600;
        }
        
        .section-divider {
            border-top: 2px solid #e9ecef;
            margin: 30px 0;
            padding-top: 20px;
        }
        
        .preview-content {
            font-family: 'Times New Roman', serif;
            line-height: 1.6;
            padding: 20px;
        }
        
        .pegawai-luar-form {
            display: none;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        
        .select2-container--default .select2-selection--multiple {
            border: 2px solid #e9ecef !important;
            border-radius: 8px !important;
            min-height: 45px !important;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <div class="text-center mb-4">
            <h1 class="display-5 fw-bold text-primary">
                <i class="fas fa-file-alt me-3"></i>Generator Surat Tugas
            </h1>
            <p class="lead text-muted">Direktorat Jenderal Sains dan Teknologi</p>
            <?php if ($databaseStatus === 'connected'): ?>
            <div class="alert alert-success">
                <i class="fas fa-database me-2"></i>
                <strong>Database Connected:</strong> <?= $statusMessage ?>
                Data real dari database: <?= count($daftarPegawai) ?> pegawai loaded.
            </div>
            <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Database Error:</strong> <?= $statusMessage ?>
                Fallback ke data dummy untuk testing.
            </div>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <!-- Form Column -->
            <div class="col-lg-6">
                <div class="form-card">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-edit me-2"></i>Form Surat Tugas</h3>
                    </div>
                    
                    <div class="card-body p-4">
                        <form id="suratTugasForm" method="POST" action="../backend/controllers/SuratTugasController.php">
                            
                            <!-- Nomor Surat -->
                            <div class="mb-4">
                                <label for="nomor_surat" class="form-label">
                                    <i class="fas fa-hashtag me-2"></i>Nomor Surat
                                </label>
                                <input type="text" class="form-control" id="nomor_surat" name="nomor_surat" 
                                    value="001/ST/<?= date('Y') ?>" placeholder="001/ST/2025" required>
                            </div>
                            
                            <!-- Acara/Kegiatan -->
                            <div class="mb-4">
                                <label for="acara" class="form-label">
                                    <i class="fas fa-clipboard-list me-2"></i>Acara/Kegiatan
                                </label>
                                <textarea class="form-control" id="acara" name="acara" rows="3" 
                                    placeholder="Contoh: Rekonsiliasi Kebutuhan dan Penyusunan Prognosis Anggaran Sekretariat Direktorat Jenderal Sains dan Teknologi"
                                    required>Rekonsiliasi Kebutuhan dan Penyusunan Prognosis Anggaran Sekretariat Direktorat Jenderal Sains dan Teknologi</textarea>
                            </div>

                            <!-- Tanggal Mulai -->
                            <div class="mb-4">
                                <label for="tgl_mulai" class="form-label">
                                    <i class="fas fa-calendar me-2"></i>Tanggal Mulai
                                </label>
                                <input type="date" class="form-control" id="tgl_mulai" name="tgl_mulai" value="2025-08-19" required>
                            </div>

                            <!-- Tanggal Selesai -->
                            <div class="mb-4">
                                <label for="tgl_selesai" class="form-label">
                                    <i class="fas fa-calendar-check me-2"></i>Tanggal Selesai (Opsional)
                                </label>
                                <input type="date" class="form-control" id="tgl_selesai" name="tgl_selesai" value="2025-08-20">
                                <small class="text-muted">Kosongkan jika kegiatan hanya satu hari</small>
                            </div>

                            <!-- Lokasi -->
                            <div class="mb-4">
                                <label for="lokasi" class="form-label">
                                    <i class="fas fa-map-marker-alt me-2"></i>Lokasi/Tempat
                                </label>
                                <input type="text" class="form-control" id="lokasi" name="lokasi" 
                                    placeholder="Contoh: Ruang Rapat Rektorat Lantai 9, Kampus Baru Depok, Universitas Indonesia, Jawa Barat 16424"
                                    value="Ruang Rapat Rektorat Lantai 9, Kampus Baru Depok, Universitas Indonesia, Jawa Barat 16424"
                                    required>
                            </div>

                            <!-- DIPA -->
                            <div class="mb-4">
                                <label for="dipa" class="form-label">
                                    <i class="fas fa-file-invoice me-2"></i>Nomor DIPA
                                </label>
                                <input type="text" class="form-control" id="dipa" name="dipa" 
                                    value="SP DIPA-139.05.1.693321/2025 tanggal 2 Desember 2024"
                                    placeholder="Contoh: SP DIPA-139.05.1.693321/2025 tanggal 2 Desember 2024">
                            </div>

                            <!-- Pegawai yang Ditugaskan -->
                            <div class="mb-4">
                                <label for="pegawai" class="form-label">
                                    <i class="fas fa-users me-2"></i>Pegawai yang Ditugaskan
                                </label>
                                <select class="form-control" id="pegawai" name="pegawai[]" multiple="multiple" required>
                                    <?php foreach ($daftarPegawai as $pegawai): ?>
                                        <option value="<?= htmlspecialchars($pegawai['nip']) ?>">
                                            <?= htmlspecialchars($pegawai['nama_pegawai']) ?> - <?= htmlspecialchars($pegawai['jabatan']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                
                                <div class="mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="tambah_pegawai_luar">
                                        <label class="form-check-label" for="tambah_pegawai_luar">
                                            Tambah pegawai dari luar instansi
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="pegawai-luar-form" id="pegawai_luar_form">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input type="text" class="form-control mb-2" id="nama_luar" 
                                                placeholder="Nama Lengkap">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control mb-2" id="jabatan_luar" 
                                                placeholder="Jabatan/Instansi">
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-secondary" id="tambah_luar">
                                        <i class="fas fa-plus me-1"></i>Tambah
                                    </button>
                                </div>
                            </div>

                            <div class="section-divider"></div>

                            <!-- Pejabat Penandatangan -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="pejabat" class="form-label">
                                        <i class="fas fa-user-tie me-2"></i>Jabatan Penandatangan
                                    </label>
                                    <input type="text" class="form-control" id="pejabat" name="pejabat" 
                                        value="Sekretaris" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="nama_pejabat" class="form-label">
                                        <i class="fas fa-signature me-2"></i>Nama Pejabat
                                    </label>
                                    <select class="form-select" id="nama_pejabat" name="nama_pejabat" required>
                                        <option value="">Pilih Pejabat</option>
                                        <?php foreach ($pejabatList as $pejabat): ?>
                                            <option value="<?= htmlspecialchars($pejabat['nip']) ?>">
                                                <?= htmlspecialchars($pejabat['nama']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Tembusan -->
                            <div class="mb-4">
                                <label for="tembusan" class="form-label">
                                    <i class="fas fa-copy me-2"></i>Tembusan (Opsional)
                                </label>
                                <textarea class="form-control" id="tembusan" name="tembusan" rows="2" 
                                    placeholder="Contoh: Direktur Jenderal Sains dan Teknologi"></textarea>
                            </div>

                            <!-- Buttons -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" class="btn btn-primary me-md-2" id="previewBtn">
                                    <i class="fas fa-eye me-2"></i>Preview
                                </button>
                                <?php if ($databaseStatus === 'connected'): ?>
                                <button type="submit" class="btn btn-success" name="action" value="export_word">
                                    <i class="fas fa-download me-2"></i>Download DOCX
                                </button>
                                <?php else: ?>
                                <button type="button" class="btn btn-warning" onclick="alert('Database tidak terhubung. Mohon perbaiki koneksi database untuk download DOCX.')">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Database Required
                                </button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Preview Column -->
            <div class="col-lg-6">
                <div class="preview-card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Preview Surat Tugas</h5>
                    </div>
                    <div class="card-body p-0">
                        <div id="previewContent" class="preview-content">
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-file-alt fa-3x mb-3"></i>
                                <p>Klik "Preview" untuk melihat tampilan surat</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Select2 for pegawai selection
            $('#pegawai').select2({
                placeholder: 'Pilih pegawai yang akan ditugaskan...',
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

            // Preview functionality dengan JavaScript (tanpa AJAX)
            $('#previewBtn').click(function() {
                generatePreview();
            });

            // Function untuk generate preview
            function generatePreview() {
                const nomor_surat = $('#nomor_surat').val() || '001/ST/<?= date('Y') ?>';
                const acara = $('#acara').val() || '[Acara belum diisi]';
                const tglMulai = $('#tgl_mulai').val();
                const tglSelesai = $('#tgl_selesai').val();
                const lokasi = $('#lokasi').val() || '[Lokasi belum diisi]';
                const dipa = $('#dipa').val() || 'SP DIPA-139.05.1.693321/2025 tanggal 2 Desember 2024';
                const namaPejabat = $('#nama_pejabat option:selected').text() || '[Pejabat belum dipilih]';
                const nipPejabat = $('#nama_pejabat').val() || '';
                const tembusan = $('#tembusan').val();
                
                // Format tanggal
                let tanggalFormatted = '[Tanggal belum diisi]';
                if (tglMulai) {
                    const startDate = new Date(tglMulai);
                    const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    
                    if (tglSelesai && tglSelesai !== tglMulai) {
                        const endDate = new Date(tglSelesai);
                        tanggalFormatted = `${hari[startDate.getDay()]}-${hari[endDate.getDay()]}, ${startDate.getDate()}-${endDate.getDate()} ${bulan[startDate.getMonth()]} ${startDate.getFullYear()}`;
                    } else {
                        tanggalFormatted = `${hari[startDate.getDay()]}, ${startDate.getDate()} ${bulan[startDate.getMonth()]} ${startDate.getFullYear()}`;
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
                            pegawaiRows += `
                                <tr>
                                    <td style="text-align: center; border: 1px solid #000; padding: 6px;">${index + 1}.</td>
                                    <td style="border: 1px solid #000; padding: 6px;">
                                        <strong>${parts[1] || 'Nama Eksternal'}</strong><br>
                                        <em>Pegawai Eksternal</em>
                                    </td>
                                    <td style="border: 1px solid #000; padding: 6px;">${parts[2] || 'Jabatan Eksternal'}</td>
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
                        
                        <div style="text-align: center; font-weight: bold; font-size: 12pt; margin: 20px 0; text-decoration: underline;">
                            <strong>SURAT TUGAS</strong><br>
                            <span style="font-weight: normal; font-size: 10pt;">Nomor: ${nomor_surat}</span>
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
                            
                            <p>Biaya kegiatan dibebankan kepada DIPA Satuan Kerja Direktorat Jenderal Sains dan Teknologi, Nomor: ${dipa}.</p>
                            
                            <p>Surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab dan yang bersangkutan diharapkan membuat laporan.</p>
                        </div>
                        
                        <div style="margin-top: 30px; display: table; width: 100%;">
                            <div style="display: table-cell; width: 50%; vertical-align: top;"></div>
                            <div style="display: table-cell; width: 50%; text-align: center; vertical-align: top;">
                                <div style="margin-bottom: 12px;">Sekretaris,</div>
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

            // Load preview saat halaman pertama kali dibuka
            setTimeout(function() {
                generatePreview();
            }, 500);

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
        });
    </script>
</body>
</html>