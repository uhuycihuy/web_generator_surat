<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../backend/helpers/utils.php';
require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/models/User.php';

checkLogin();

if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    redirectTo('generator_surat');
}

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);

$users = $userModel->getAll();
$currentUserId = $_SESSION['user']['id'] ?? null;
$adminCount = $userModel->getAdminCount();
$totalUserCount = count($users);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Saintek</title>
    <link rel="stylesheet" href="<?= assetUrl('styles.css') ?>">
    <link rel="icon" href="<?= assetUrl('logo_kemendikti-saintek.png') ?>" type="image/png" sizes="64x64">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="admin-layout user-management">
    <?php include "sidebar.php"; ?>

    <main class="main-content">
        <section class="page-header">
            <div class="header-text">
                <h1 class="page-title">Kelola Akun</h1>
                <p class="page-subtitle">Atur pengguna internal untuk generator surat</p>
            </div>
            <div class="header-meta">
                <span class="meta-item" data-user-count>
                    <i class="fa-solid fa-user-check" aria-hidden="true"></i>
                    <span class="count-number"><?= $totalUserCount; ?></span> aktif
                </span>
                <span class="meta-item" data-admin-count>
                    <i class="fa-solid fa-user-shield" aria-hidden="true"></i>
                    <span class="count-number"><?= $adminCount; ?></span> admin
                </span>
                <div class="header-actions">
                    <button class="btn btn-primary" id="openCreateUser" aria-label="Tambah akun baru">Tambah</button>
                </div>
            </div>
        </section>

        <section class="user-flash" id="userFlash" hidden></section>

        <section class="user-grid" aria-label="Daftar user terdaftar">
            <!-- Create card removed; Add button moved to header-meta for a compact layout -->

            <?php if (empty($users)): ?>
                <article class="user-card user-card--empty">
                    <i class="fa-solid fa-user-slash"></i>
                    <p>Belum ada user terdaftar. Tambahkan minimal satu akun.</p>
                </article>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <?php
                        $userId = (int)$user['no_id'];
                        $isAdmin = ($user['role'] ?? 'user') === 'admin';
                        $isSelf = $userId === (int)$currentUserId;
                        $bodyId = 'user-body-' . $userId;
                    ?>
                    <article class="user-card<?= $isAdmin ? ' user-card--admin' : ''; ?>" data-user-id="<?= $userId; ?>" data-role="<?= htmlspecialchars($user['role']); ?>">
                        <header class="card-header">
                            <div class="card-title-row">
                                <h2><?= htmlspecialchars($user['username']); ?></h2>
                                <div class="card-meta">
                                    <span class="role-chip role-<?= htmlspecialchars($user['role']); ?>">
                                        <?= $user['role'] === 'admin' ? 'Administrator' : 'User'; ?>
                                    </span>
                                    <?php if ($isSelf && !$isAdmin): ?>
                                            <span class="self-chip" title="Akun Anda"><i class="fa-solid fa-circle-user"></i> Anda</span>
                                        <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!$isAdmin): ?>
                                <div class="card-actions">
                                    <button class="btn btn-icon btn-edit" data-action="edit" data-user-id="<?= $userId; ?>" aria-label="Edit <?= htmlspecialchars($user['username']); ?>">
                                        <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                    </button>
                                    <button class="btn btn-icon btn-delete" data-action="delete" data-no-id="<?= $userId; ?>" aria-label="Hapus <?= htmlspecialchars($user['username']); ?>">
                                        <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </header>

                        <?php if ($isAdmin): ?>
                            <div class="card-body card-body--static">
                                <p class="admin-note">
                                   
                                    Akun administrator tidak dapat diubah dari dashboard.
                                </p>
                               
                            </div>
                            <?php else: ?>
                            <div class="card-body compact" id="<?= $bodyId; ?>">
                                <div class="user-summary">
                                    <div class="summary-left">
                                        <!-- username is displayed in the header -->
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <script>
    window.USER_ENDPOINT = '<?= baseUrl('backend/controllers/AdminController.php'); ?>';
    window.CURRENT_USER_ID = <?= $currentUserId !== null ? (int)$currentUserId : 'null'; ?>;
    </script>
    <script src="<?= assetUrl('kelola_user.js'); ?>" defer></script>
</body>
</html>