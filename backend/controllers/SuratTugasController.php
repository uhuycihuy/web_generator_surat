<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/utils.php';

class SuratTugasController extends BaseController {
    public function exportWord() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'export_word') {
            return $this->jsonResponse(['error' => 'Invalid request'], 400);
        }
    try {
        // Get database connection
        $database = new Database();
        $db = $database->getConnection();
        
        // Get POST data
        $selectedPegawai = $_POST['pegawai'] ?? [];
        $tgl_mulai = $_POST['tgl_mulai'] ?? '';
        $tgl_selesai = $_POST['tgl_selesai'] ?? '';
        $acara = $_POST['acara'] ?? '';
        $lokasi = $_POST['lokasi'] ?? '';
        $dipa = $_POST['dipa'] ?? 'SP DIPA-139.05.1.693321/2025 tanggal 2 Desember 2024';
        $tembusan = $_POST['tembusan'] ?? '';
        $nip_pejabat = $_POST['nama_pejabat'] ?? '';
        
        // Get pejabat data using utils function
        $pejabatList = array_column(getNamaPejabatList(), 'nama', 'nip');
        $namaPejabat = $pejabatList[$nip_pejabat] ?? '';
        $nipPejabat = $nip_pejabat;
        
        // Get pegawai data
        $daftarPegawai = [];
        foreach ($selectedPegawai as $nipPegawai) {
            if (strpos($nipPegawai, 'L|') === 0) {
                // Pegawai eksternal
                $parts = explode('|', $nipPegawai);
                $daftarPegawai[] = [
                    'nama_pegawai' => $parts[1] ?? 'Nama Eksternal',
                    'nip' => '-',
                    'pangkat' => '-',
                    'golongan' => '-',
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
        
        // Format tanggal using utils function
        $tanggalFormatted = formatTanggalRange($tgl_mulai, $tgl_selesai);
        
        // Build pegawai table rows
        $pegawaiRows = '';
        $no = 1;
        foreach ($daftarPegawai as $pegawai) {
            if ($pegawai['is_external']) {
                $pegawaiRows .= '<tr>
                    <td style="border: 1px solid #000; padding: 6px; text-align: center;">' . $no++ . '</td>
                    <td style="border: 1px solid #000; padding: 6px;"><strong>' . htmlspecialchars($pegawai['nama_pegawai']) . '</strong><br><em>Pegawai Eksternal</em></td>
                    <td style="border: 1px solid #000; padding: 6px;">' . htmlspecialchars($pegawai['jabatan']) . '</td>
                </tr>';
            } else {
                $pegawaiRows .= '<tr>
                    <td style="border: 1px solid #000; padding: 6px; text-align: center;">' . $no++ . '</td>
                    <td style="border: 1px solid #000; padding: 6px;"><strong>' . htmlspecialchars($pegawai['nama_pegawai']) . '</strong><br>NIP ' . htmlspecialchars($pegawai['nip']) . '<br>' . htmlspecialchars($pegawai['pangkat']) . ', ' . htmlspecialchars($pegawai['golongan']) . '</td>
                    <td style="border: 1px solid #000; padding: 6px;">' . htmlspecialchars($pegawai['jabatan']) . '</td>
                </tr>';
            }
        }
        
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Surat Tugas</title>
            <style>
                body { font-family: "Times New Roman", serif; font-size: 11pt; line-height: 1.4; color: #000; margin: 20px; }
                table { border-collapse: collapse; }
            </style>
        </head>
        <body>
            <div style="font-family: \'Times New Roman\', serif; font-size: 11pt; line-height: 1.4; color: #000;">
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
                    <span style="font-weight: normal; font-size: 10pt;">Nomor: </span>
                </div>
                
                <div style="margin: 15px 0; text-align: justify; line-height: 1.5;">
                    <p>Dalam rangka kegiatan ' . htmlspecialchars($acara) . ', dengan ini Sekretaris Direktorat Jenderal Sains dan Teknologi menugaskan kepada nama di bawah ini,</p>
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
                        ' . $pegawaiRows . '
                    </tbody>
                </table>
                
                <div style="margin: 15px 0; text-align: justify; line-height: 1.5;">
                    <p>Untuk hadir dan melaksanakan tugas dalam kegiatan dimaksud yang akan diselenggarakan pada hari ' . $tanggalFormatted . ', bertempat di ' . htmlspecialchars($lokasi) . '</p>
                    
                    <p>Biaya kegiatan dibebankan kepada DIPA Satuan Kerja Direktorat Jenderal Sains dan Teknologi, Nomor: ' . htmlspecialchars($dipa) . '.</p>
                    
                    <p>Surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab dan yang bersangkutan diharapkan membuat laporan.</p>
                </div>
                
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
        
        $html .= '
            </div>
        </body>
        </html>';
        
        // Download file using BaseController method
        $filename = 'surat_tugas_' . date('Y-m-d') . '.doc';
        $this->downloadFile($html, $filename);
        
    } catch (Exception $e) {
        return $this->jsonResponse(['error' => $e->getMessage()], 500);
    }
    }
}

// Handle request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new SuratTugasController();
    $controller->exportWord();
}
?>