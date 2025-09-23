<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Pegawai.php';
require_once __DIR__ . '/../helpers/utils.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'export_word') {
    try {
        // Get database connection
        $database = new Database();
        $db = $database->getConnection();
        
        // Get POST data
        $selectedPegawai = $_POST['pegawai'] ?? [];
        $nomor_surat = $_POST['nomor_surat'] ?? ('001/UN/' . date('Y'));
        $rangka = $_POST['rangka'] ?? '';
        $hari_tanggal = $_POST['hari_tanggal'] ?? '';
        $waktu = $_POST['waktu'] ?? '';
        $media = $_POST['media'] ?? '';
        $rapat_id = $_POST['rapat_id'] ?? '';
        $sandi = $_POST['sandi'] ?? '';
        $tautan = $_POST['tautan'] ?? '';
        $agenda = $_POST['agenda'] ?? '';
        $narahubung = $_POST['narahubung'] ?? '';
        $no_narahubung = $_POST['no_narahubung'] ?? '';
        $tembusan = $_POST['tembusan'] ?? '';
        $nip_pejabat = $_POST['nama_pejabat'] ?? '';
        
        // Get data pejabat dari hardcoded list
        $namaPejabat = '';
        $nipPejabat = $nip_pejabat;
        
        // Hardcoded pejabat data
        $pejabatList = [
            '197901142003121001' => 'M Samsuri',
            '197604272005021001' => 'Ahmad Najib Burhani'
        ];
        
        if (isset($pejabatList[$nip_pejabat])) {
            $namaPejabat = $pejabatList[$nip_pejabat];
        }
        
        // Get pegawai data untuk daftar undangan
        $daftarPegawai = [];
        foreach ($selectedPegawai as $nipPegawai) {
            if (strpos($nipPegawai, 'L|') === 0) {
                // Pegawai eksternal
                $parts = explode('|', $nipPegawai);
                $daftarPegawai[] = [
                    'nama_pegawai' => $parts[1] ?? 'Nama Eksternal',
                    'jabatan' => $parts[2] ?? 'Jabatan Eksternal',
                    'is_external' => true
                ];
            } else {
                // Pegawai internal
                $stmt = $db->prepare("SELECT * FROM pegawai WHERE nip = ?");
                $stmt->execute([$nipPegawai]);
                $pegawai = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($pegawai) {
                    $pegawai['is_external'] = false;
                    $daftarPegawai[] = $pegawai;
                }
            }
        }
        
        // Format tanggal Indonesia
        $tanggalFormatted = '';
        if ($hari_tanggal) {
            $bulan = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            
            $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            
            $date = new DateTime($hari_tanggal);
            $tanggalFormatted = $hari[$date->format('w')] . ', ' . $date->format('j') . ' ' . $bulan[(int)$date->format('n')] . ' ' . $date->format('Y');
        }
        
        // Build daftar undangan
        $undanganRows = '';
        $no = 1;
        foreach ($daftarPegawai as $pegawai) {
            $undanganRows .= '<tr>
                <td style="text-align: center; border: 1px solid #000; padding: 8px; width: 30px;">' . $no++ . '.</td>
                <td style="border: 1px solid #000; padding: 8px;">' . htmlspecialchars($pegawai['nama_pegawai']) . ', ' . htmlspecialchars($pegawai['jabatan']) . '</td>
            </tr>';
        }
        
        if (empty($undanganRows)) {
            $undanganRows = '<tr><td colspan="2" style="text-align: center; font-style: italic; color: #666; border: 1px solid #000; padding: 8px;">Belum ada yang diundang</td></tr>';
        }
        
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Surat Undangan</title>
            <style>
                body { font-family: "Times New Roman", serif; font-size: 11pt; line-height: 1.4; color: #000; margin: 20px; }
                table { border-collapse: collapse; }
                .header-table { width: 100%; border: none; margin-bottom: 25px; }
                .content-table { width: 100%; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div style="font-family: \'Times New Roman\', serif; font-size: 11pt; line-height: 1.4; color: #000;">
                <!-- Header -->
                <div style="text-align: center; margin-bottom: 25px; border-bottom: 2px solid #000; padding-bottom: 12px;">
                    <table class="header-table">
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
                
                <!-- Nomor dan Lampiran -->
                <table style="width: 100%; margin-bottom: 20px;">
                    <tr>
                        <td style="width: 80px; vertical-align: top;">Nomor</td>
                        <td style="width: 10px; vertical-align: top;">:</td>
                        <td style="vertical-align: top;">' . htmlspecialchars($nomor_surat) . '</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">Lampiran</td>
                        <td style="vertical-align: top;">:</td>
                        <td style="vertical-align: top;">satu lembar</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">Hal</td>
                        <td style="vertical-align: top;">:</td>
                        <td style="vertical-align: top; font-weight: bold;">Undangan</td>
                    </tr>
                </table>
                
                <!-- Kepada Yth -->
                <div style="margin: 20px 0;">
                    <strong>Yth. Peserta Kegiatan</strong><br>
                    (daftar terlampir)
                </div>
                
                <!-- Isi Surat -->
                <div style="margin: 15px 0; text-align: justify; line-height: 1.5;">
                    <p>Dalam rangka membangun <em>' . htmlspecialchars($rangka) . '</em> di Perguruan Tinggi/Lembaga Riset, kami bermaksud menyelenggarakan rapat yang ditujukan bagi para dosen dan peneliti. Sehubungan dengan hal tersebut, kami mengundang Bapak/Ibu untuk berkenan hadir dan berpartisipasi dalam rapat yang akan dilaksanakan pada</p>
                </div>
                
                <!-- Detail Kegiatan -->
                <table class="content-table">
                    <tr>
                        <td style="width: 80px; vertical-align: top;">hari, tanggal</td>
                        <td style="width: 10px; vertical-align: top;">:</td>
                        <td style="vertical-align: top;">' . $tanggalFormatted . '</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">waktu</td>
                        <td style="vertical-align: top;">:</td>
                        <td style="vertical-align: top;">' . htmlspecialchars($waktu) . '</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">media</td>
                        <td style="vertical-align: top;">:</td>
                        <td style="vertical-align: top;">' . htmlspecialchars($media) . '</td>
                    </tr>';
                    
        if (!empty($rapat_id)) {
            $html .= '
                    <tr>
                        <td style="vertical-align: top;">rapat id</td>
                        <td style="vertical-align: top;">:</td>
                        <td style="vertical-align: top;">' . htmlspecialchars($rapat_id) . '</td>
                    </tr>';
        }
        
        if (!empty($sandi)) {
            $html .= '
                    <tr>
                        <td style="vertical-align: top;">kata sandi</td>
                        <td style="vertical-align: top;">:</td>
                        <td style="vertical-align: top;">' . htmlspecialchars($sandi) . '</td>
                    </tr>';
        }
        
        if (!empty($tautan)) {
            $html .= '
                    <tr>
                        <td style="vertical-align: top;">tautan</td>
                        <td style="vertical-align: top;">:</td>
                        <td style="vertical-align: top;">' . htmlspecialchars($tautan) . '</td>
                    </tr>';
        }
        
        $html .= '
                    <tr>
                        <td style="vertical-align: top;">agenda</td>
                        <td style="vertical-align: top;">:</td>
                        <td style="vertical-align: top;">' . nl2br(htmlspecialchars($agenda)) . '</td>
                    </tr>
                </table>
                
                <!-- Penutup -->
                <div style="margin: 15px 0; text-align: justify; line-height: 1.5;">
                    <p>Besar harapan kami Bapak/Ibu dapat meluangkan waktu untuk hadir dalam rapat dimaksud guna memberikan pemahaman yang lebih mendalam mengenai pentingnya integritas akademik, khususnya dalam pengelolaan jurnal ilmiah, serta memperkuat kolaborasi antar civitas akademika dan lembaga riset.</p>
                    
                    <p>Untuk informasi lebih lanjut mengenai rapat dan konfirmasi kehadiran, tim Bapak/Ibu dapat menghubungi ' . htmlspecialchars($narahubung) . ' kami melalui Saudara ' . htmlspecialchars($narahubung) . ' di nomor gawai ' . htmlspecialchars($no_narahubung) . '.</p>
                    
                    <p>Demikian surat ini kami sampaikan. Atas perhatian dan kerja sama yang baik, kami ucapkan terima kasih.</p>
                </div>
                
                <!-- Tanda Tangan -->
                <div style="margin-top: 30px; display: table; width: 100%;">
                    <div style="display: table-cell; width: 50%; vertical-align: top;"></div>
                    <div style="display: table-cell; width: 50%; text-align: center; vertical-align: top;">
                        <div style="margin-bottom: 12px;">Sekretaris,</div>
                        <div style="margin: 60px 0 12px 0;"></div>
                        <div style="font-weight: bold;">' . htmlspecialchars($namaPejabat) . '</div>
                        <div style="font-size: 9pt;">NIP ' . htmlspecialchars($nipPejabat) . '</div>
                    </div>
                </div>';
                
        if (!empty($tembusan)) {
            $html .= '
                <div style="margin-top: 30px; clear: both;">
                    <strong>Tembusan:</strong><br>
                    ' . nl2br(htmlspecialchars($tembusan)) . '
                </div>';
        }
        
        // Lampiran daftar undangan
        $html .= '
                <div style="page-break-before: always; margin-top: 30px;">
                    <div style="text-align: center; font-weight: bold; font-size: 12pt; margin-bottom: 20px;">
                        Lampiran<br>
                        Nomor: ' . htmlspecialchars($nomor_surat) . '<br>
                        Tanggal: ' . $tanggalFormatted . '
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
                            ' . $undanganRows . '
                        </tbody>
                    </table>
                </div>
            </div>
        </body>
        </html>';
        
        // Convert HTML to Word using simple method
        $filename = 'surat_undangan_' . date('Y-m-d') . '.doc';
        
        // Clear output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers
        header('Content-Type: application/msword');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Output HTML as DOC
        echo $html;
        exit();
        
    } catch (Exception $e) {
        header('Content-Type: text/plain');
        echo "Error: " . $e->getMessage();
        exit();
    }
} else {
    echo "Invalid request";
}
?>