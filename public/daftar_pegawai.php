<?php
require_once __DIR__ . '/../backend/controllers/UserController.php';
require_once __DIR__ . '/../backend/helpers/utils.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

checkLogin();

// Inisialisasi controller
$userController = new UserController();

// Ambil semua data pegawai
$pegawaiList = $userController->getAllPegawai();
$role = $_SESSION['user']['role'] ?? 'guest';
$bodyClasses = 'daftar-pegawai';
if ($role === 'admin') {
    $bodyClasses .= ' admin-layout';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pegawai - Saintek</title>
    <link rel="stylesheet" href="<?= assetUrl('styles.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="<?= $bodyClasses ?>">
    <?php if ($role === 'admin'): ?>
        <?php include "sidebar.php"; ?>
        <main class="main-content">
    <?php else: ?>
        <?php include "navbar.php"; ?>
    <?php endif; ?>

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

        <?php if ($role === 'admin'): ?>
        <div class="action-buttons">
            <button class="btn btn-primary" onclick="openAddModal()">
                <i class="fa-solid fa-plus"></i> Tambah Pegawai
            </button>
        </div>
        <?php endif; ?>

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
                        <?php if ($role === 'admin'): ?><th>Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($pegawaiList)): ?>
                        <tr>
                            <td colspan="<?= $role === 'admin' ? '6' : '5' ?>" style="text-align: center; padding: 40px;">
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
                                <?php if ($role === 'admin'): ?>
                                <td class="action-buttons">
                                    <button class="btn-action btn-edit" onclick="openEditModal('<?= htmlspecialchars($pegawai['nip']) ?>')" title="Edit">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                    <button class="btn-action btn-delete" onclick="deletePegawai('<?= htmlspecialchars($pegawai['nip']) ?>')" title="Hapus">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                                <?php endif; ?>
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

    <!-- Modal Tambah/Edit Pegawai (hanya admin) -->
    <?php if ($role === 'admin'): ?>
    <div id="pegawaiModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Tambah Pegawai</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="pegawaiForm" novalidate>
                <input type="hidden" id="originalNip" name="original_nip">
                <div class="form-group">
                    <label for="nip">NIP *</label>
                    <input type="text" id="nip" name="nip" required>
                </div>
                <div class="form-group">
                    <label for="nama_pegawai">Nama Pegawai *</label>
                    <input type="text" id="nama_pegawai" name="nama_pegawai" required>
                </div>
                <div class="form-group">
                    <label for="pangkat">Pangkat</label>
                    <input type="text" id="pangkat" name="pangkat">
                </div>
                <div class="form-group">
                    <label for="golongan">Golongan</label>
                    <input type="text" id="golongan" name="golongan">
                </div>
                <div class="form-group">
                    <label for="jabatan">Jabatan</label>
                    <input type="text" id="jabatan" name="jabatan">
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($role === 'admin'): ?>
        </main>
    <?php endif; ?>

    <script>
        // Pagination Configuration
        const rowsPerPage = 10;
        let currentPage = 1;
        let allRows = [];
        let filteredRows = [];

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            var rows = document.querySelectorAll('.pegawai-row');
            allRows = [];
            for (var i = 0; i < rows.length; i++) {
                allRows.push(rows[i]);
            }
            filteredRows = allRows.slice();
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
                filteredRows = allRows.slice();
            } else {
                filteredRows = [];
                for (var i = 0; i < allRows.length; i++) {
                    var text = allRows[i].textContent.toLowerCase();
                    if (text.indexOf(searchValue) !== -1) {
                        filteredRows.push(allRows[i]);
                    }
                }
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
            html += '<button class="pagination-btn ' + (currentPage === 1 ? 'disabled' : '') + '" ' +
                     'onclick="changePage(' + (currentPage - 1) + ')" ' +
                     (currentPage === 1 ? 'disabled' : '') + '>' +
                     '<i class="fa-solid fa-chevron-left"></i>' +
                   '</button>';
            
            // Page numbers
            const maxVisiblePages = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
            
            if (endPage - startPage < maxVisiblePages - 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }
            
            // First page
            if (startPage > 1) {
                html += '<button class="pagination-btn" onclick="changePage(1)">1</button>';
                if (startPage > 2) {
                    html += '<span class="pagination-dots">...</span>';
                }
            }
            
            // Page numbers
            for (let i = startPage; i <= endPage; i++) {
                html += '<button class="pagination-btn ' + (i === currentPage ? 'active' : '') + '" ' +
                         'onclick="changePage(' + i + ')">' + i + '</button>';
            }
            
            // Last page
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    html += '<span class="pagination-dots">...</span>';
                }
                html += '<button class="pagination-btn" onclick="changePage(' + totalPages + ')">' + totalPages + '</button>';
            }
            
            // Next button
            html += '<button class="pagination-btn ' + (currentPage === totalPages ? 'disabled' : '') + '" ' +
                     'onclick="changePage(' + (currentPage + 1) + ')" ' +
                     (currentPage === totalPages ? 'disabled' : '') + '>' +
                     '<i class="fa-solid fa-chevron-right"></i>' +
                   '</button>';
            
            paginationControls.innerHTML = html;
        }

        function changePage(page) {
            const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
            if (page < 1 || page > totalPages) return;
            showPage(page);
        }

        // Modal functions (hanya untuk admin)
        <?php if ($role === 'admin'): ?>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Pegawai';
            document.getElementById('pegawaiForm').reset();
            document.getElementById('originalNip').value = '';
            document.getElementById('pegawaiModal').style.display = 'block';
        }

        function openEditModal(nip) {
            document.getElementById('modalTitle').textContent = 'Edit Pegawai';
            document.getElementById('originalNip').value = nip;
            
            var rows = document.querySelectorAll('.pegawai-row');
            var targetRow = null;
            
            for (var i = 0; i < rows.length; i++) {
                if (rows[i].cells[1].textContent.trim() === nip) {
                    targetRow = rows[i];
                    break;
                }
            }
            
            if (targetRow) {
                document.getElementById('nip').value = targetRow.cells[1].textContent.trim();
                document.getElementById('nama_pegawai').value = targetRow.cells[0].textContent.trim();
                document.getElementById('pangkat').value = targetRow.cells[2].textContent.trim();
                document.getElementById('golongan').value = targetRow.cells[3].textContent.trim();
                document.getElementById('jabatan').value = targetRow.cells[4].textContent.trim();
            }
            
            document.getElementById('pegawaiModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('pegawaiModal').style.display = 'none';
        }

        function deletePegawai(nip) {
            if (confirm('Apakah Anda yakin ingin menghapus pegawai ini?')) {
                var formData = new FormData();
                formData.append('action', 'delete');
                formData.append('nip', nip);
                
                fetch('<?= baseUrl('backend/controllers/AdminController.php') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    return response.text();
                })
                .then(function(text) {
                    try {
                        var data = JSON.parse(text);
                        if (data.success) {
                            alert('Pegawai berhasil dihapus');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    } catch (error) {
                        alert('Response error: ' + text);
                    }
                })
                .catch(function(error) {
                    alert('Network error: ' + error.message);
                });
            }
        }

        // Form submit
        document.getElementById('pegawaiForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validasi manual
            var nip = document.getElementById('nip').value.trim();
            var nama = document.getElementById('nama_pegawai').value.trim();
            
            if (!nip || !nama) {
                alert('NIP dan Nama Pegawai harus diisi');
                return;
            }
            
            var formData = new FormData();
            var isEdit = document.getElementById('originalNip').value !== '';
            
            formData.append('action', isEdit ? 'update' : 'add');
            formData.append('nip', nip);
            formData.append('nama_pegawai', nama);
            formData.append('pangkat', document.getElementById('pangkat').value.trim());
            formData.append('golongan', document.getElementById('golongan').value.trim());
            formData.append('jabatan', document.getElementById('jabatan').value.trim());
            
            if (isEdit) {
                formData.append('original_nip', document.getElementById('originalNip').value);
            }
            
            fetch('<?= baseUrl('backend/controllers/AdminController.php') ?>', {
                method: 'POST',
                body: formData
            })
            .then(function(response) {
                return response.text();
            })
            .then(function(text) {
                try {
                    var data = JSON.parse(text);
                    if (data.success) {
                        alert(isEdit ? 'Pegawai berhasil diupdate' : 'Pegawai berhasil ditambahkan');
                        closeModal();
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (error) {
                    alert('Response error: ' + text);
                }
            })
            .catch(function(error) {
                alert('Network error: ' + error.message);
            });
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('pegawaiModal');
            if (event.target === modal) {
                closeModal();
            }
        }
        <?php endif; ?>
    </script>
</body>
</html>