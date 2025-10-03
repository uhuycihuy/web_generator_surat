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
            </div>
        </section>

        <section class="user-flash" id="userFlash" hidden></section>

        <section class="user-grid" aria-label="Daftar user terdaftar">
            <article class="user-card user-card--create" aria-labelledby="create-user-title">
                <div class="card-header">
                    <div>
                        <h2 id="create-user-title">Tambah User</h2>
                    </div>
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <form class="user-form" data-action="create">
                    <div class="form-field">
                        <label for="username-new">Username</label>
                        <input type="text" id="username-new" name="username" required autocomplete="off" placeholder="contoh: operator1">
                    </div>
                    <div class="form-field">
                        <label for="password-new">Password</label>
                        <input type="password" id="password-new" name="password" required minlength="6" placeholder="Minimal 6 karakter">
                    </div>
                    <input type="hidden" name="role" value="user">
                    <p class="form-hint">Akun baru otomatis menjadi role <strong>User</strong>.</p>
                    <button type="submit" class="btn btn-primary full-width">
                        <span>Tambah User</span>
                    </button>
                </form>
            </article>

            <?php if (empty($users)): ?>
                <article class="user-card user-card--empty">
                    <i class="fa-solid fa-user-slash"></i>
                    <p>Belum ada user terdaftar. Tambahkan minimal satu akun User.</p>
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
                                    <?php if ($isSelf): ?>
                                        <span class="self-chip" title="Akun Anda"><i class="fa-solid fa-circle-user"></i> Anda</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </header>

                        <?php if ($isAdmin): ?>
                            <div class="card-body card-body--static">
                                <p class="admin-note">
                                   
                                    Akun administrator tidak dapat diubah dari dashboard.
                                </p>
                               
                            </div>
                        <?php else: ?>
                            <div class="card-body" id="<?= $bodyId; ?>">
                                <form class="user-form" data-action="update">
                                    <input type="hidden" name="no_id" value="<?= $userId; ?>">
                                    <div class="form-field">
                                        <label>Username</label>
                                        <input type="text" name="username" required value="<?= htmlspecialchars($user['username']); ?>">
                                    </div>
                                    <div class="form-field">
                                        <label>Password lama <span>(wajib saat mengganti)</span></label>
                                        <input type="password" name="old_password" minlength="6" placeholder="Masukkan password sekarang">
                                    </div>
                                    <div class="form-field">
                                        <label>Password baru <span>(opsional)</span></label>
                                        <input type="password" name="password" minlength="6" placeholder="Biarkan kosong jika tidak diganti">
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <span>Simpan</span>
                                        </button>
                                        <button type="button" class="btn btn-danger" data-action="delete" data-no-id="<?= $userId; ?>">
                                            <i class="fa-solid fa-trash"></i> Hapus
                                        </button>
                                    </div>
                                </form>
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