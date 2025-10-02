<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek login dan role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($action === 'add') {
        $nip = trim($_POST['nip'] ?? '');
        $nama = trim($_POST['nama_pegawai'] ?? '');
        $pangkat = trim($_POST['pangkat'] ?? '') ?: null;
        $golongan = trim($_POST['golongan'] ?? '') ?: null;
        $jabatan = trim($_POST['jabatan'] ?? '') ?: null;
        
        if (empty($nip) || empty($nama)) {
            throw new Exception('NIP dan Nama harus diisi');
        }
        
        $stmt = $db->prepare("INSERT INTO pegawai (nip, nama_pegawai, pangkat, golongan, jabatan) VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute([$nip, $nama, $pangkat, $golongan, $jabatan]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Berhasil ditambahkan']);
        } else {
            throw new Exception('Gagal menambahkan data');
        }
        
    } elseif ($action === 'update') {
        $originalNip = trim($_POST['original_nip'] ?? '');
        $nama = trim($_POST['nama_pegawai'] ?? '');
        $pangkat = trim($_POST['pangkat'] ?? '') ?: null;
        $golongan = trim($_POST['golongan'] ?? '') ?: null;
        $jabatan = trim($_POST['jabatan'] ?? '') ?: null;
        
        if (empty($originalNip) || empty($nama)) {
            throw new Exception('Data tidak lengkap');
        }
        
        $stmt = $db->prepare("UPDATE pegawai SET nama_pegawai=?, pangkat=?, golongan=?, jabatan=? WHERE nip=?");
        $result = $stmt->execute([$nama, $pangkat, $golongan, $jabatan, $originalNip]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Berhasil diupdate']);
        } else {
            throw new Exception('Gagal mengupdate data');
        }
        
    } elseif ($action === 'delete') {
        $nip = trim($_POST['nip'] ?? '');
        
        if (empty($nip)) {
            throw new Exception('NIP tidak valid');
        }
        
        $stmt = $db->prepare("DELETE FROM pegawai WHERE nip=?");
        $result = $stmt->execute([$nip]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Berhasil dihapus']);
        } else {
            throw new Exception('Gagal menghapus data');
        }
        
    } else {
        throw new Exception('Action tidak valid');
    }
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        echo json_encode(['success' => false, 'message' => 'NIP sudah ada']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>