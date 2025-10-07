<?php
require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/models/User.php';
require_once __DIR__ . '/../backend/controllers/AuthController.php';
require_once __DIR__ . '/../backend/helpers/utils.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user'])) {
    header('Location: ' . baseUrl('generator_surat'));
    exit;
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kemendikti Saintek</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600&family=Roboto:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/web_generator_surat/public/assets/login.css?v=<?= time() ?>">
</head>

<body class="login-page">

    <?php if (isset($_GET['status']) && $_GET['status'] === 'logout'): ?>
        <div class="logout-notification" id="logoutNotif">
            ✓ Anda berhasil logout
        </div>
    <?php endif; ?>

    <!-- Layer background + overlay -->
    <div class="background-layer"></div>
    <div class="overlay-layer"></div>

    <!-- SPLIT LAYOUT -->
    <div class="split">
        <!-- LEFT PANEL: logo atas + hero text bawah -->
        <div class="left-panel">
            <div class="logo-header">
                <img src="<?= assetUrl('logo_kemendikti-saintek.png') ?>" alt="Logo Kemendikti">
                <h1>Kementerian Pendidikan Tinggi,<br>Sains, dan Teknologi</h1>
            </div>

            <div class="hero-text">
                Buat Surat Resmi Lebih<br>Cepat dan Tepat.
            </div>
        </div>

        <!-- RIGHT PANEL: floating login card (mengonsumsi setengah layar kanan) -->
        <div class="right-panel">
            <div class="login-card">
                <h2 class="login-title">Selamat Datang!</h2>
                <p class="login-subtitle">Masuk untuk memulai pembuatan surat.</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= baseUrl('login') ?>">
                    <input type="hidden" name="action" value="login">

                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    </div>

                    <button type="submit" class="btn-login">Masuk</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const notif = document.getElementById('logoutNotif');
        if (notif) {
            if (window.location.search.includes('status=logout')) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            setTimeout(() => notif.remove(), 3000);
        }
    </script>
</body>
</html>
