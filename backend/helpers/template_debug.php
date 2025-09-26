<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpWord\TemplateProcessor;

function debugTemplate($templatePath) {
    if (!file_exists($templatePath)) {
        return "Template tidak ditemukan: $templatePath";
    }
    
    try {
        $templateProcessor = new TemplateProcessor($templatePath);
        $variables = $templateProcessor->getVariables();
        
        $result = "Template: " . basename($templatePath) . "\n";
        $result .= "Placeholder yang ditemukan:\n";
        
        if (empty($variables)) {
            $result .= "- Tidak ada placeholder ditemukan\n";
        } else {
            foreach ($variables as $var) {
                $result .= "- \${" . $var . "}\n";
            }
        }
        
        return $result;
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
    
    foreach ($templates as $template) {
        echo debugTemplate($templateDir . $template) . "\n\n";
    }
}
?>