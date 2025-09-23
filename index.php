<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generator Surat Tugas & Undangan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-file-alt me-2"></i>Generator Surat
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="login.php">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12 text-center mb-4">
                <h1><i class="fas fa-file-signature"></i> Generator Surat Tugas & Undangan</h1>
                <p class="lead text-muted">Buat surat tugas dan surat undangan dengan mudah dan cepat</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-file-signature fa-4x text-primary mb-3"></i>
                        <h4 class="card-title">Surat Tugas</h4>
                        <p class="card-text">Buat surat tugas untuk perjalanan dinas atau kegiatan resmi lainnya dengan template yang sudah tersedia.</p>
                        <a href="public/surat_tugas.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus"></i> Buat Surat Tugas
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-envelope fa-4x text-success mb-3"></i>
                        <h4 class="card-title">Surat Undangan</h4>
                        <p class="card-text">Buat surat undangan untuk rapat, seminar, atau acara resmi dengan format yang professional.</p>
                        <a href="#" class="btn btn-success btn-lg" onclick="alert('Fitur surat undangan akan segera tersedia')">
                            <i class="fas fa-plus"></i> Buat Surat Undangan
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6><i class="fas fa-check text-success"></i> Fitur Tersedia</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-dot-circle text-primary"></i> Generator Surat Tugas</li>
                                    <li><i class="fas fa-dot-circle text-primary"></i> Template Professional</li>
                                    <li><i class="fas fa-dot-circle text-primary"></i> Export ke Word (.docx)</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <h6><i class="fas fa-clock text-warning"></i> Akan Datang</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-dot-circle text-warning"></i> Generator Surat Undangan</li>
                                    <li><i class="fas fa-dot-circle text-warning"></i> Sistem Login Admin/Staff</li>
                                    <li><i class="fas fa-dot-circle text-warning"></i> Manajemen Template</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <h6><i class="fas fa-users text-info"></i> Untuk Admin</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-dot-circle text-info"></i> CRUD Data Pegawai</li>
                                    <li><i class="fas fa-dot-circle text-info"></i> Manajemen User</li>
                                    <li><i class="fas fa-dot-circle text-info"></i> Dashboard Statistik</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-light mt-5 py-4">
        <div class="container text-center">
            <p class="text-muted mb-0">&copy; 2025 Generator Surat Tugas & Undangan. Dibuat untuk kemudahan administrasi.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>