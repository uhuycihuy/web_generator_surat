<?php
require_once '../backend/controllers/UserController.php';

session_start();
// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Inisialisasi controller
$userController = new UserController();

// Ambil semua data pegawai
$pegawaiList = $userController->getAllPegawai();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pegawai - Saintek</title>
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="daftar-pegawai">
    <?php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $role = $_SESSION['user']['role'] ?? 'guest';

        if ($role === 'admin') {
            include "sidebar.php";
        } else {
            include "navbar.php";
        }
    ?>

    <div class="container-pegawai">
        <div class="header-section">
            <div class="title-group">
                <i class="fa-solid fa-users icon-title"></i>
                <div>
                    <h1 class="page-title">Daftar Pegawai Kemendikti Saintek</h1>
                    <p class="page-subtitle">Halaman mencari pegawai Kemendikti Saintek</p>
                </div>
            </div>
        </div>

        <!-- Search Box -->
        <div class="search-container">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="searchInput" class="search-input" placeholder="Cari nama, NIP, pangkat, golongan, atau jabatan...">
            <i class="fa-solid fa-xmark clear-icon" id="clearSearch" style="display: none;"></i>
        </div>

        <!-- Table -->
        <div class="table-container">
            <table class="pegawai-table">
                <thead>
                    <tr>
                        <th>Nama Pegawai</th>
                        <th>NIP</th>
                        <th>Pangkat</th>
                        <th>Golongan</th>
                        <th>Jabatan</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($pegawaiList)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px;">
                                Tidak ada data pegawai
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pegawaiList as $pegawai): ?>
                            <tr class="pegawai-row">
                                <td><?= htmlspecialchars($pegawai['nama_pegawai']) ?></td>
                                <td><?= htmlspecialchars($pegawai['nip']) ?></td>
                                <td><?= htmlspecialchars($pegawai['pangkat']) ?></td>
                                <td><?= htmlspecialchars($pegawai['golongan']) ?></td>
                                <td><?= htmlspecialchars($pegawai['jabatan']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            <div class="pagination-info">
                Menampilkan <span id="startEntry">0</span> - <span id="endEntry">0</span> dari <span id="totalEntries">0</span> data
            </div>
            <div class="pagination-controls" id="paginationControls">
                <!-- Pagination buttons akan di-generate oleh JavaScript -->
            </div>
        </div>
    </div>

    <script>
        // Pagination Configuration
        const rowsPerPage = 10;
        let currentPage = 1;
        let allRows = [];
        let filteredRows = [];

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            allRows = Array.from(document.querySelectorAll('.pegawai-row'));
            filteredRows = [...allRows];
            showPage(1);
        });

        // Clear search functionality
        const clearSearchBtn = document.getElementById('clearSearch');

        // Update search functionality untuk show/hide clear button
        document.getElementById('searchInput').addEventListener('input', function() {
            if (this.value !== '') {
                clearSearchBtn.style.display = 'block';
            } else {
                clearSearchBtn.style.display = 'none';
            }
        });

        // Clear button click handler
        clearSearchBtn.addEventListener('click', function() {
            document.getElementById('searchInput').value = '';
            this.style.display = 'none';
            
            // Reset ke semua data
            filteredRows = [...allRows];
            currentPage = 1;
            showPage(1);
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            
            if (searchValue === '') {
                // Jika search kosong, tampilkan semua data
                filteredRows = [...allRows];
            } else {
                // Filter data berdasarkan search
                filteredRows = allRows.filter(row => {
                    const text = row.textContent.toLowerCase();
                    return text.includes(searchValue);
                });
            }
            
            currentPage = 1;
            showPage(1);
        });

        function showPage(page) {
            currentPage = page;
            
            // Hide all rows first
            allRows.forEach(row => row.style.display = 'none');
            
            // Calculate start and end index
            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            
            // Show only rows for current page
            const rowsToShow = filteredRows.slice(start, end);
            rowsToShow.forEach(row => row.style.display = '');
            
            // Update pagination info
            updatePaginationInfo(start, end);
            
            // Update pagination buttons
            renderPaginationButtons();
        }

        function updatePaginationInfo(start, end) {
            const totalEntries = filteredRows.length;
            const startEntry = totalEntries > 0 ? start + 1 : 0;
            const endEntry = Math.min(end, totalEntries);
            
            document.getElementById('startEntry').textContent = startEntry;
            document.getElementById('endEntry').textContent = endEntry;
            document.getElementById('totalEntries').textContent = totalEntries;
        }

        function renderPaginationButtons() {
            const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
            const paginationControls = document.getElementById('paginationControls');
            
            if (totalPages <= 1) {
                paginationControls.innerHTML = '';
                return;
            }
            
            let html = '';
            
            // Previous button
            html += `<button class="pagination-btn ${currentPage === 1 ? 'disabled' : ''}" 
                     onclick="changePage(${currentPage - 1})" 
                     ${currentPage === 1 ? 'disabled' : ''}>
                     <i class="fa-solid fa-chevron-left"></i>
                   </button>`;
            
            // Page numbers
            const maxVisiblePages = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
            
            if (endPage - startPage < maxVisiblePages - 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }
            
            // First page
            if (startPage > 1) {
                html += `<button class="pagination-btn" onclick="changePage(1)">1</button>`;
                if (startPage > 2) {
                    html += `<span class="pagination-dots">...</span>`;
                }
            }
            
            // Page numbers
            for (let i = startPage; i <= endPage; i++) {
                html += `<button class="pagination-btn ${i === currentPage ? 'active' : ''}" 
                         onclick="changePage(${i})">${i}</button>`;
            }
            
            // Last page
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    html += `<span class="pagination-dots">...</span>`;
                }
                html += `<button class="pagination-btn" onclick="changePage(${totalPages})">${totalPages}</button>`;
            }
            
            // Next button
            html += `<button class="pagination-btn ${currentPage === totalPages ? 'disabled' : ''}" 
                     onclick="changePage(${currentPage + 1})" 
                     ${currentPage === totalPages ? 'disabled' : ''}>
                     <i class="fa-solid fa-chevron-right"></i>
                   </button>`;
            
            paginationControls.innerHTML = html;
        }

        function changePage(page) {
            const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
            if (page < 1 || page > totalPages) return;
            showPage(page);
        }
    </script>
</body>
</html>