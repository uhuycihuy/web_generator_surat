<?php

function formatLongLink($url, $maxLength = 50) {
    if (empty($url)) return '';
    
    // Jika URL pendek, return as is
    if (strlen($url) <= $maxLength) {
        return $url;
    }
    
    // Pecah URL panjang dengan line break
    $formatted = '';
    $chunks = str_split($url, $maxLength);
    
    foreach ($chunks as $index => $chunk) {
        $formatted .= $chunk;
        // Tambah line break kecuali chunk terakhir
        if ($index < count($chunks) - 1) {
            $formatted .= "\r\n";
        }
    }
    
    return $formatted;
}

function formatTautanOnline($tautan) {
    if (empty($tautan)) return '';
    
    // Cek apakah sudah ada protocol
    if (!preg_match('/^https?:\/\//', $tautan)) {
        $tautan = 'https://' . $tautan;
    }
    
    // Format untuk Word document dengan line break
    return formatLongLink($tautan, 45);
}

?>