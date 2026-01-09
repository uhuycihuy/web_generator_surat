<?php
/**
 * Pejabat (Official) Helper Functions - Database Queries
 * 
 * Query and format pejabat data from database
 * NOTE: Use these functions for database queries. For legacy hardcoded data, use utils.php
 */

/**
 * Get all pejabat from database
 * 
 * @param PDO $db Database connection
 * @return array List of pejabat
 */
function getPejabatListFromDb($db) {
    try {
        $query = "SELECT id, nama, jabatan, nip FROM pegawai WHERE jabatan != '' ORDER BY nama ASC";
        $stmt  = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching pejabat list: " . $e->getMessage());
        return [];
    }
}

/**
 * Get pejabat by specific jabatan (position) from database
 * 
 * @param PDO $db Database connection
 * @param string $jabatan Jabatan to search for
 * @return array|null Pejabat data or null
 */
function getPejabatByJabatanDb($db, $jabatan) {
    try {
        $query = "SELECT id, nama, jabatan, nip FROM pegawai WHERE jabatan = ? LIMIT 1";
        $stmt  = $db->prepare($query);
        $stmt->execute([$jabatan]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching pejabat by jabatan: " . $e->getMessage());
        return null;
    }
}

/**
 * Get all pejabat grouped by jabatan from database
 * 
 * @param PDO $db Database connection
 * @return array Pejabat grouped by jabatan
 */
function getPejabatGroupedByJabatanDb($db) {
    $pejabat = getPejabatListFromDb($db);
    $grouped = [];

    foreach ($pejabat as $person) {
        $jabatan = $person['jabatan'];
        if (!isset($grouped[$jabatan])) {
            $grouped[$jabatan] = [];
        }
        $grouped[$jabatan][] = $person;
    }

    return $grouped;
}

/**
 * Format pejabat name with title
 * 
 * @param string $nama Name
 * @param string $jabatan Position/title
 * @return string Formatted string
 */
function formatNamaPejabat($nama, $jabatan = '') {
    $result = $nama;
    if (!empty($jabatan)) {
        $result .= " ($jabatan)";
    }
    return $result;
}
?>
