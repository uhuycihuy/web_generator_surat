<?php
// session_start(); // Dikomentari karena tidak semua halaman memerlukan session

function formatTanggalRange($tglMulai, $tglSelesai) {
    if (empty($tglMulai)) return '';

    $bulanIndo = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
             'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    $hariIndo = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];

    $start = strtotime($tglMulai);
    $end   = !empty($tglSelesai) ? strtotime($tglSelesai) : $start;

    // 🔹 Validasi: tanggal akhir tidak boleh sebelum tanggal awal
    if ($end < $start) {
        return 'Error: Tanggal akhir tidak boleh sebelum tanggal awal';
        // atau bisa juga otomatis disamakan dengan tanggal awal:
        // $end = $start;
    }

    $hari1 = $hariIndo[date('l', $start)];
    $hari2 = $hariIndo[date('l', $end)];

    $tgl1 = date('j', $start);
    $tgl2 = date('j', $end);

    $bln1 = $bulanIndo[(int)date('n', $start)];
    $bln2 = $bulanIndo[(int)date('n', $end)];

    $thn1 = date('Y', $start);
    $thn2 = date('Y', $end);

    // Jika bulan dan tahun sama
    if ($bln1 === $bln2 && $thn1 === $thn2) {
        if ($tgl1 === $tgl2) {
            return "$hari1, tanggal $tgl1 $bln1 $thn1";
        } else {
            return "$hari1-$hari2, tanggal $tgl1-$tgl2 $bln1 $thn1";
        }
    } else {
        return "$hari1, tanggal $tgl1 $bln1 $thn1 - $hari2, $tgl2 $bln2 $thn2";
    }
}

function formatTanggalIndonesia($tanggal) {
    if (empty($tanggal)) return '';
    
    $bulanIndo = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
             'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $hariIndo = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];
    
    $timestamp = strtotime($tanggal);
    $hari = $hariIndo[date('l', $timestamp)];
    $tgl = date('j', $timestamp);
    $bulan = $bulanIndo[(int)date('n', $timestamp)];
    $tahun = date('Y', $timestamp);
    
    return "$hari, $tgl $bulan $tahun";
}

function formatWaktuUndangan($waktuAwal, $waktuAkhir = '') {
    if (empty($waktuAkhir) || strtolower(trim($waktuAkhir)) === 'selesai') {
        return 'selesai';
    }
    return $waktuAkhir;
}

//Data dropdown Pejabat Pendatanganan
function getPejabatJabatanList() {
    return [
        "Sekretaris",
        "Direktur Jenderal Sains dan Teknologi"
    ];
}

function getNamaPejabatList() {
    return [
        [
            'nip'   => '197901142003121001',
            'nama'  => 'M Samsuri'
        ],
        [
            'nip'   => '197604272005021001',
            'nama'  => 'Ahmad Najib Burhani'
        ]
    ];
}

// Cek apakah user sudah login
function appBasePath() {
    if (PHP_SAPI === 'cli') {
        return '';
    }

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $directory  = str_replace('\\', '/', dirname($scriptName));
    if ($directory === '/' || $directory === '\\') {
        return '';
    }

    return rtrim($directory, '/');
}

function baseUrl($path = '') {
    if (PHP_SAPI === 'cli') {
        return $path;
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base   = appBasePath();

    $url = $scheme . '://' . $host;
    if (!empty($base)) {
        $url .= $base;
    }
    $url .= '/';

    if (!empty($path)) {
        $url .= ltrim($path, '/');
    }

    return $url;
}

function routeUrl($path = '', $query = '') {
    $url = baseUrl($path);
    if (!empty($query)) {
        $url .= (strpos($url, '?') === false ? '?' : '&') . ltrim($query, '?');
    }
    return $url;
}

function assetUrl($path) {
    $normalized = ltrim($path, '/');
    return baseUrl('assets/' . $normalized);
}

function redirectTo($path, $status = 302) {
    $destination = filter_var($path, FILTER_VALIDATE_URL) ? $path : baseUrl($path);
    header("Location: {$destination}", true, $status);
    exit;
}

function currentRoutePath() {
    if (PHP_SAPI === 'cli') {
        return '';
    }

    $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base       = appBasePath();

    if (!empty($base) && strpos($requestUri, $base) === 0) {
        $requestUri = substr($requestUri, strlen($base));
    }

    return trim($requestUri, '/');
}

function checkLogin() {
    if (!isset($_SESSION['user'])) {
        redirectTo('login');
    }
}

//Cek apakah user rolenya admin
function checkAdmin() {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        echo "Akses ditolak! Hanya admin yang boleh.";
        exit;
    }
}

// Ambil data user yang login
function currentUser() {
    return $_SESSION['user'] ?? null;
}
?>