<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$entity = $_POST['entity'] ?? 'pegawai';
$action = $_POST['action'] ?? '';

try {
    $database = new Database();
    $db = $database->getConnection();

    if ($entity === 'pegawai') {
        switch ($action) {
            case 'add':
                $nip = trim($_POST['nip'] ?? '');
                $nama = trim($_POST['nama_pegawai'] ?? '');
                $pangkat = trim($_POST['pangkat'] ?? '') ?: null;
                $golongan = trim($_POST['golongan'] ?? '') ?: null;
                $jabatan = trim($_POST['jabatan'] ?? '') ?: null;

                if ($nip === '' || $nama === '') {
                    throw new InvalidArgumentException('NIP dan Nama harus diisi');
                }

                $stmt = $db->prepare('INSERT INTO pegawai (nip, nama_pegawai, pangkat, golongan, jabatan) VALUES (?, ?, ?, ?, ?)');
                if (!$stmt->execute([$nip, $nama, $pangkat, $golongan, $jabatan])) {
                    throw new RuntimeException('Gagal menambahkan data');
                }

                echo json_encode(['success' => true, 'message' => 'Berhasil ditambahkan']);
                break;

            case 'update':
                $originalNip = trim($_POST['original_nip'] ?? '');
                $nama = trim($_POST['nama_pegawai'] ?? '');
                $pangkat = trim($_POST['pangkat'] ?? '') ?: null;
                $golongan = trim($_POST['golongan'] ?? '') ?: null;
                $jabatan = trim($_POST['jabatan'] ?? '') ?: null;

                if ($originalNip === '' || $nama === '') {
                    throw new InvalidArgumentException('Data tidak lengkap');
                }

                $stmt = $db->prepare('UPDATE pegawai SET nama_pegawai = ?, pangkat = ?, golongan = ?, jabatan = ? WHERE nip = ?');
                if (!$stmt->execute([$nama, $pangkat, $golongan, $jabatan, $originalNip])) {
                    throw new RuntimeException('Gagal mengupdate data');
                }

                echo json_encode(['success' => true, 'message' => 'Berhasil diupdate']);
                break;

            case 'delete':
                $nip = trim($_POST['nip'] ?? '');

                if ($nip === '') {
                    throw new InvalidArgumentException('NIP tidak valid');
                }

                $stmt = $db->prepare('DELETE FROM pegawai WHERE nip = ?');
                if (!$stmt->execute([$nip])) {
                    throw new RuntimeException('Gagal menghapus data');
                }

                echo json_encode(['success' => true, 'message' => 'Berhasil dihapus']);
                break;

            default:
                throw new InvalidArgumentException('Action tidak valid');
        }
        return;
    }

    if ($entity === 'user') {
        $userModel = new User($db);
        $currentUserId = $_SESSION['user']['id'] ?? null;
        $currentAdminCount = $userModel->getAdminCount();

        switch ($action) {
            case 'create':
                $username = trim($_POST['username'] ?? '');
                $password = trim($_POST['password'] ?? '');
                $role = 'user';

                if ($username === '' || $password === '') {
                    throw new InvalidArgumentException('Username dan password wajib diisi');
                }

                if ($userModel->getByUsername($username)) {
                    throw new InvalidArgumentException('Username sudah digunakan');
                }

                if (!$userModel->addUser($username, $password, $role)) {
                    throw new RuntimeException('Gagal menambahkan user');
                }

                $createdUser = $userModel->getByUsername($username) ?: [];
                unset($createdUser['password']);

                echo json_encode([
                    'success' => true,
                    'message' => 'User berhasil ditambahkan',
                    'data' => [
                        'user' => $createdUser,
                    ],
                ]);
                break;

            case 'update':
                $noId = (int)($_POST['no_id'] ?? 0);
                $username = trim($_POST['username'] ?? '');
                $password = trim($_POST['password'] ?? '');
                $oldPassword = trim($_POST['old_password'] ?? '');

                if ($noId <= 0) {
                    throw new InvalidArgumentException('ID user tidak valid');
                }

                if ($username === '') {
                    throw new InvalidArgumentException('Username wajib diisi');
                }

                $targetUser = $userModel->getById($noId);
                if (!$targetUser) {
                    throw new RuntimeException('User tidak ditemukan');
                }

                if (($targetUser['role'] ?? 'user') === 'admin') {
                    throw new RuntimeException('Akun administrator tidak dapat diubah');
                }

                $currentPasswordHash = $targetUser['password'] ?? '';
                unset($targetUser['password']);

                if ($password !== '') {
                    if ($oldPassword === '') {
                        throw new InvalidArgumentException('Password lama wajib diisi untuk mengganti password');
                    }

                    if ($currentPasswordHash === '' || !password_verify($oldPassword, $currentPasswordHash)) {
                        throw new RuntimeException('Password lama tidak sesuai');
                    }
                }

                $role = $targetUser['role'] ?? 'user';

                $existing = $userModel->getByUsername($username);
                if ($existing && (int)$existing['no_id'] !== $noId) {
                    throw new InvalidArgumentException('Username sudah digunakan');
                }

                $userModel->updateUser($noId, $username, $role, $password !== '' ? $password : null);

                if ($currentUserId === $noId) {
                    $_SESSION['user']['username'] = $username;
                    $_SESSION['user']['role'] = $role;
                }

                $updatedUser = $userModel->getById($noId) ?: [];
                unset($updatedUser['password']);

                echo json_encode([
                    'success' => true,
                    'message' => 'Perubahan user berhasil disimpan',
                    'data' => [
                        'user' => $updatedUser,
                    ],
                ]);
                break;

            case 'delete':
                $noId = (int)($_POST['no_id'] ?? 0);

                if ($noId <= 0) {
                    throw new InvalidArgumentException('ID user tidak valid');
                }

                if ($currentUserId === $noId) {
                    throw new RuntimeException('Tidak dapat menghapus akun sendiri');
                }

                $targetUser = $userModel->getById($noId);
                if (!$targetUser) {
                    throw new RuntimeException('User tidak ditemukan');
                }

                if (($targetUser['role'] ?? 'user') === 'admin') {
                    throw new RuntimeException('Akun administrator tidak dapat dihapus');
                }

                if (($targetUser['role'] ?? 'user') === 'admin' && $currentAdminCount <= 1) {
                    throw new RuntimeException('Tidak dapat menghapus admin terakhir');
                }

                $userModel->deleteUser($noId);

                echo json_encode([
                    'success' => true,
                    'message' => 'User berhasil dihapus',
                    'data' => [
                        'deleted_id' => $noId,
                    ],
                ]);
                break;

            default:
                throw new InvalidArgumentException('Action tidak valid');
        }
        return;
    }

    throw new InvalidArgumentException('Entity tidak dikenal');

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (RuntimeException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'NIP sudah ada']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
}
