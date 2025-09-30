<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$username = $_SESSION['user']['username'] ?? "Guest";
?>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<header class="header generator-surat">
    <div class="header-left">
        <div class="logo"></div>
        <div class="header-title">Kementerian Pendidikan Tinggi, Sains, dan Teknologi</div>
    </div>

    <div class="header-buttons">
        <!-- Generator Surat -->
        <a href="generator_surat.php" class="header-btn">
            <i class="fa-solid fa-file-lines"></i> Generator Surat
        </a>

        <!-- Daftar Pegawai -->
        <a href="daftar_pegawai.php" class="header-btn">
            <i class="fa-solid fa-users"></i> Daftar Pegawai
        </a>
        
        <!-- Profil User dengan dropdown -->
        <div class="profile-dropdown">
            <button class="header-btn profile-btn">
                <i class="fa-solid fa-user"></i> <?= htmlspecialchars($username) ?> <i class="fa-solid fa-caret-down"></i>
            </button>
            <div class="profile-menu">
                <p><i class="fa-solid fa-circle-user"></i> <?= htmlspecialchars($username) ?></p>
                <a href="logout.php" 
                   class="logout-btn" 
                   onclick="return confirm('Apakah Anda yakin ingin logout?')">
                   <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>
    </div>
</header>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const profileBtn = document.querySelector(".profile-btn");
    const profileMenu = document.querySelector(".profile-menu");

    profileBtn.addEventListener("click", () => {
        profileMenu.classList.toggle("show");
    });

    document.addEventListener("click", (e) => {
        if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
            profileMenu.classList.remove("show");
        }
    });
});
</script>
