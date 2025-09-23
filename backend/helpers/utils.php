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
            return "$hari1,tanggal $tgl1 $bln1 $thn1";
        } else {
            return "$hari1–$hari2, tanggal $tgl1–$tgl2 $bln1 $thn1";
        }
    } else {
        return "$hari1, tanggal $tgl1 $bln1 $thn1 – $hari2, $tgl2 $bln2 $thn2";
    }
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
function checkLogin() {
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit;
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