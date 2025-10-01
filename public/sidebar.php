<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$username = $_SESSION['user']['username'] ?? "Admin";
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- INLINE SCRIPT - Jalankan sebelum render untuk prevent FOUC -->
<script>
(function() {
    const savedState = localStorage.getItem('sidebarCollapsed');
    const html = document.documentElement;
    
    if (savedState === 'true') {
        html.classList.add('sidebar-collapsed');
    } else {
        html.classList.add('sidebar-open');
    }
})();
</script>

<div class="sidebar" id="sidebar">
    <!-- Profile Section -->
    <div class="sidebar-profile">
        <div class="profile-avatar">
            <img src="/web_generator_surat/public/assets/logo_kemendikti-saintek.png" alt="Avatar">
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
            <a href="generator_surat.php" class="nav-item <?= $current_page == 'generator_surat.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-file-lines"></i>
                <span class="nav-text">Generator Surat</span>
            </a>
        </div>

        <!-- Management Section -->
        <div class="nav-section">
            <p class="nav-section-title"><i class="fa-solid fa-gear"></i> Management</p>
            <a href="daftar_pegawai.php" class="nav-item <?= $current_page == 'daftar_pegawai.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-users"></i>
                <span class="nav-text">Kelola Pegawai</span>
            </a>
            <a href="kelola_user.php" class="nav-item <?= $current_page == 'kelola_user.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-user-gear"></i>
                <span class="nav-text">Kelola User</span>
            </a>
        </div>

        <!-- Logout Section -->
        <div class="nav-section">
            <p class="nav-section-title"><i class="fa-solid fa-right-from-bracket"></i> Account</p>
            <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin logout?');" class="nav-item">
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
<link rel="stylesheet" href="assets/styles.css">
<!-- Link FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const html = document.documentElement;

    // Mark as loaded untuk enable transitions
    setTimeout(() => html.classList.add('js-loaded'), 50);

    // Sync dengan state yang sudah di-set inline
    const savedState = localStorage.getItem('sidebarCollapsed');
    if (savedState === 'true') {
        sidebar.classList.add('collapsed');
    }

    // Toggle sidebar
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        
        if (sidebar.classList.contains('collapsed')) {
            html.classList.remove('sidebar-open');
            html.classList.add('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', 'true');
        } else {
            html.classList.remove('sidebar-collapsed');
            html.classList.add('sidebar-open');
            localStorage.setItem('sidebarCollapsed', 'false');
        }
    });

    // Mobile menu toggle
    if (window.innerWidth <= 768) {
        html.classList.remove('sidebar-open', 'sidebar-collapsed');
        
        if (!document.querySelector('.mobile-menu-btn')) {
            const mobileBtn = document.createElement('button');
            mobileBtn.className = 'mobile-menu-btn';
            mobileBtn.innerHTML = '<i class="fa-solid fa-bars"></i>';
            document.body.appendChild(mobileBtn);

            mobileBtn.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-open');
                sidebarOverlay.classList.toggle('show');
            });

            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('show');
            });
        }
    }
});
</script>
