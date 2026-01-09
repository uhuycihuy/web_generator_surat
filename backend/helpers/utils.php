<?php
/**
 * Legacy Master Utilities File
 * Now acts as a central loader for all specialized helper files
 * Maintains backward compatibility by including all specialized helpers
 */

// Load specialized helper files
require_once __DIR__ . '/RoutingHelper.php';
require_once __DIR__ . '/AuthHelper.php';
require_once __DIR__ . '/FormattingHelper.php';
require_once __DIR__ . '/PejabatHelper.php';
require_once __DIR__ . '/CommonHelper.php';

// Legacy wrapper functions for backward compatibility

//Data Pejabat Pendatanganan - Unified Function
function getPejabatList() {
    return [
        [
            'nip'      => '197901142003121001',
            'nama'     => 'M Samsuri',
            'jabatan'  => 'Sekretaris'
        ],
        [
            'nip'      => '197604272005021001',
            'nama'     => 'Ahmad Najib Burhani',
            'jabatan'  => 'Direktur Jenderal Sains dan Teknologi'
        ]
    ];
}

//Data dropdown Pejabat Jabatan (Legacy - gunakan getPejabatList() untuk fitur baru)
function getPejabatJabatanList() {
    $pejabatList = getPejabatList();
    return array_column($pejabatList, 'jabatan');
}

//Data dropdown Pejabat Nama (Legacy - gunakan getPejabatList() untuk fitur baru)
function getNamaPejabatList() {
    return getPejabatList();
}
?>