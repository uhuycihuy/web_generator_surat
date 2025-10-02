<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once '../backend/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Debug: cek tabel apa saja yang ada
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    // Coba berbagai nama tabel yang mungkin
    $possibleTables = ['users', 'user', 'admin', 'login', 'account', 'auth'];
    
    // Jika tidak ada tabel yang cocok, coba semua tabel
    if (empty(array_intersect($possibleTables, $tables))) {
        $possibleTables = $tables;
    }
    $userList = [];
    $tableUsed = '';
    
    foreach ($possibleTables as $table) {
        if (in_array($table, $tables)) {
            $query = "SELECT * FROM $table";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $userList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $tableUsed = $table;
            break;
        }
    }
    
} catch (Exception $e) {
    $userList = [];
    $error = $e->getMessage();
    $tables = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Saintek</title>
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="daftar-pegawai admin-layout">
    <?php include "sidebar.php"; ?>

    <div class="main-content">
        <div class="container-pegawai">
        <div class="header-section">
            <div class="title-group">
                <i class="fa-solid fa-users-gear icon-title"></i>
                <div>
                    <h1 class="page-title">Kelola User</h1>
                    <p class="page-subtitle">Daftar semua user sistem</p>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table class="pegawai-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($userList)): ?>
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 40px;">
                                Tidak ada data user<br>
                                <small>Tabel tersedia: <?= implode(', ', $tables ?? []) ?></small><br>
                                <?php if (isset($error)): ?>
                                    <small style="color: red;">Error: <?= $error ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <tr><td colspan="3"><strong>Tabel: <?= $tableUsed ?></strong></td></tr>
                        <?php foreach ($userList as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['username'] ?? $user['user'] ?? 'N/A') ?></td>
                                <td>
                                    <span style="font-family: monospace; background: #f8f9fa; padding: 2px 6px; border-radius: 4px;">
                                        <?= str_repeat('•', min(8, strlen($user['password'] ?? ''))) ?>
                                    </span>
                                    <small style="color: #666; margin-left: 8px;">(<?= strlen($user['password'] ?? '') ?> chars)</small>
                                </td>
                                <td>
                                    <span class="role-badge role-<?= $user['role'] ?? 'user' ?>">
                                        <?= ucfirst($user['role'] ?? 'user') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>
</body>
</html>