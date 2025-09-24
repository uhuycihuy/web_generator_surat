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
          
        </div>

        <div class="row justify-content-center">
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-file-signature fa-4x text-primary mb-3"></i>
                        <h4 class="card-title">Surat Tugas</h4>
                        
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
                       
                        <a href="public/surat_undangan.php" class="btn btn-success btn-lg">
                            <i class="fas fa-plus"></i> Buat Surat Undangan
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>