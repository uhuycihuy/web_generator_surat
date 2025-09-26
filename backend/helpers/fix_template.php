<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpWord\TemplateProcessor;

function fixTemplate($templatePath) {
    if (!file_exists($templatePath)) {
        return "Template tidak ditemukan: $templatePath";
    }
    
    try {
        $templateProcessor = new TemplateProcessor($templatePath);
        $variables = $templateProcessor->getVariables();
        
        // Cek apakah template menggunakan format lama
        if (in_array('no', $variables) && in_array('nama_jabatan', $variables)) {
            echo "Template menggunakan format lama: " . basename($templatePath) . "\n";
            echo "Placeholder ditemukan: \${no}, \${nama_jabatan}\n";
            echo "Untuk menggunakan format baru, ganti di template Word dengan: \${DATA_PEGAWAI}\n\n";
            
            return "Format lama terdeteksi";
        } elseif (in_array('DATA_PEGAWAI', $variables)) {
            echo "Template sudah menggunakan format baru: " . basename($templatePath) . "\n";
            echo "Placeholder: \${DATA_PEGAWAI}\n\n";
            
            return "Format baru sudah digunakan";
        } else {
            echo "Template tidak memiliki placeholder pegawai: " . basename($templatePath) . "\n\n";
            return "Tidak ada placeholder pegawai";
        }
        
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}

// Jika dipanggil langsung
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $templateDir = __DIR__ . '/../templates/';
    $templates = [
        'template_surat_undangan_offline.docx',
        'template_surat_undangan_online.docx'
    ];
    
    echo "=== ANALISIS TEMPLATE ===\n\n";
    
    foreach ($templates as $template) {
        fixTemplate($templateDir . $template);
    }
    
    echo "=== REKOMENDASI ===\n";
    echo "1. Template offline sudah menggunakan format baru (\${DATA_PEGAWAI})\n";
    echo "2. Template online masih menggunakan format lama (\${no}, \${nama_jabatan})\n";
    echo "3. Controller sudah diperbaiki untuk menangani kedua format\n";
    echo "4. Untuk konsistensi, sebaiknya update template online ke format baru\n\n";
}
?>