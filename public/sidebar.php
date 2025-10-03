<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../backend/helpers/utils.php';
$username = $_SESSION['user']['username'] ?? "Admin";
$current_route = currentRoutePath();
?>

<!-- INLINE SCRIPT - Jalankan sebelum render untuk prevent FOUC -->
<script>
(function() {
    try {
        const html = document.documentElement;
        const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        html.dataset.sidebarState = collapsed ? 'collapsed' : 'open';
        html.classList.add(collapsed ? 'sidebar-collapsed' : 'sidebar-open', 'sidebar-prepared');
    } catch (err) {
        document.documentElement.classList.add('sidebar-open', 'sidebar-prepared');
        document.documentElement.dataset.sidebarState = 'open';
    }
})();
</script>

<div class="sidebar" id="sidebar">
    <!-- Profile Section -->
    <div class="sidebar-profile">
        <div class="profile-avatar">
            <img src="<?= assetUrl('logo_kemendikti-saintek.png') ?>" alt="Avatar">
        </div>
        <div class="profile-info">
            <h3 class="profile-name"><?= htmlspecialchars($username) ?></h3>
            <p class="profile-role"><i class="fa-solid fa-shield-halved"></i> Administrator</p>
            <div class="session-info">
                <i class="fa-solid fa-circle-dot"></i>
                <span>Session Active</span>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <!-- Generator Surat Section -->
        <div class="nav-section">
            <p class="nav-section-title"><i class="fa-solid fa-bars"></i> Main Menu</p>
            <?php $generatorRoutes = ['generator_surat', 'generate_surat', 'surat_tugas', 'surat_undangan']; ?>
            <a href="<?= baseUrl('generator_surat') ?>" class="nav-item <?= in_array($current_route, $generatorRoutes, true) ? 'active' : '' ?>">
                <i class="fa-solid fa-file-lines"></i>
                <span class="nav-text">Generator Surat</span>
            </a>
        </div>

        <!-- Management Section -->
        <div class="nav-section">
            <p class="nav-section-title"><i class="fa-solid fa-gear"></i> Management</p>
            <a href="<?= baseUrl('daftar_pegawai') ?>" class="nav-item <?= $current_route === 'daftar_pegawai' ? 'active' : '' ?>">
                <i class="fa-solid fa-users"></i>
                <span class="nav-text">Kelola Pegawai</span>
            </a>
            <a href="<?= baseUrl('kelola_user') ?>" class="nav-item <?= $current_route === 'kelola_user' ? 'active' : '' ?>">
                <i class="fa-solid fa-user-gear"></i>
                <span class="nav-text">Kelola User</span>
            </a>
        </div>

        <!-- Logout Section -->
        <div class="nav-section">
            <p class="nav-section-title"><i class="fa-solid fa-right-from-bracket"></i> Account</p>
            <a href="<?= baseUrl('logout') ?>" onclick="return confirm('Apakah Anda yakin ingin logout?');" class="nav-item">
                <i class="fa-solid fa-power-off"></i>
                <span class="nav-text">Logout</span>
            </a>
        </div>
    </nav>

    <!-- Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fa-solid fa-angles-left"></i>
    </button>
</div>

<!-- Overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Link ke CSS -->
<link rel="stylesheet" href="<?= assetUrl('styles.css') ?>">
<!-- Link FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script src="<?= assetUrl('sidebar.js') ?>" defer></script>
