<?php
require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/models/User.php';
require_once __DIR__ . '/../backend/controllers/AuthController.php';
require_once __DIR__ . '/../backend/helpers/utils.php';

// Cek error message dari session
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
<html lang="en">         
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kemendikti Saintek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= assetUrl('styles.css') ?>">
</head>     
<body class="login-page">
    <!-- Notifikasi Logout (auto-hide setelah 3 detik) -->
    <?php if (isset($_GET['status']) && $_GET['status'] === 'logout'): ?>
        <div class="logout-notification" id="logoutNotif">
            Anda berhasil logout.
        </div>
    <?php endif; ?>

    <div class="login-container">
        <!-- Left Side - Login Form -->
        <div class="login-left">
            <div class="login-form-wrapper">
                <div class="logo-section">
                    <div class="logo-icon">
                        <img src="<?= assetUrl('logo_kemendikti-saintek.png') ?>" alt="Logo Kemendikti" class="logo-img">
                    </div>

                    <div class="logo-text">Kementerian Pendidikan Tinggi, Sains dan Teknologi</div>
                </div>

                <h2 class="welcome-title">Web Generator Surat Tugas & Undangan</h2>

                <?php if($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="<?= baseUrl('login') ?>">
                    <input type="hidden" name="action" value="login">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username anda" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                    </div>
                    <!-- Remember Me & Forgot Password 
                    <div class="remember-forgot">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">
                                Ingat saya
                            </label>
                        </div>
                    </div>
                    -->

                    <button type="submit" class="btn btn-login">Login</button>
                </form>
            </div>
        </div>

        <!-- Right Side - Image Background -->
        <div class="login-right">
            <div class="decorative-circle circle-1"></div>
            <div class="decorative-circle circle-2"></div>
            
            <div class="right-content">
                <h1 class="right-title">Kemendikti Saintek</h1>
                <p class="right-description">
                    Sistem Generator Surat Tugas & Undangan yang membantu menghasilkan surat resmi secara otomatis, akurat, dan profesional.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide notifikasi logout setelah 3 detik
        const logoutNotif = document.getElementById('logoutNotif');
        if (logoutNotif) {
            // Bersihkan URL dari parameter status=logout
            if (window.location.search.includes('status=logout')) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }

            setTimeout(() => {
                logoutNotif.remove();
            }, 3000);
        }
    </script>
</body>
</html>