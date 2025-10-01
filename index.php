<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: public/login.php");
    exit;
}

$role = $_SESSION['user']['role'] ?? 'user';

// Redirect berdasarkan role
if ($role === 'admin') {
    header("Location: public/daftar_pegawai.php");
    exit;
} else {
    header("Location: public/generator_surat.php");
    exit;
}
?>