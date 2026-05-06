<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Biblioteca Digital</title>
    <link href="./wwwroot/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./wwwroot/css/bootstrap-icons.min.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 50vh;
            display: flex;
            align-items: center;
        }
        .hover-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        .hover-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="home.php">
                <i class="bi bi-journal-bookmark-fill me-2"></i>
                Biblioteca Digital
            </a>
            <div class="ms-auto">
                <a href="logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 text-white">
                    <h1 class="display-4 fw-bold mb-3">
                        ¡Bienvenido, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuario'); ?>!
                    </h1>
                    <p class="lead mb-0">
                        <i class="bi bi-envelope-fill me-2"></i><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>
                    </p>
                </div>
                <div class="col-lg-4 text-center d-none d-lg-block">
                    <i class="bi bi-journal-bookmark-fill text-white-50 display-1"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm hover-card" onclick="window.location.href='libros.php'">
                    <div class="card-body text-center p-5">
                        <div class="mb-3">
                            <i class="bi bi-book-fill text-primary" style="font-size: 4rem;"></i>
                        </div>
                        <h3 class="card-title h2 mb-3">Gestión de Libros</h3>
                        <p class="text-muted mb-4">Administra el catálogo de libros de la biblioteca.</p>
                        <div class="d-grid">
                            <button class="btn btn-primary btn-lg rounded-pill">
                                <i class="bi bi-arrow-right-circle me-2"></i>Acceder
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm hover-card" onclick="window.location.href='autores.php'">
                    <div class="card-body text-center p-5">
                        <div class="mb-3">
                            <i class="bi bi-people-fill text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h3 class="card-title h2 mb-3">Gestión de Autores</h3>
                        <p class="text-muted mb-4">Administra los autores de los libros.</p>
                        <div class="d-grid">
                            <button class="btn btn-success btn-lg rounded-pill">
                                <i class="bi bi-arrow-right-circle me-2"></i>Acceder
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm hover-card" onclick="window.location.href='mis_prestamos.php'">
                    <div class="card-body text-center p-5">
                        <div class="mb-3">
                            <i class="bi bi-journal-bookmark-fill text-warning" style="font-size: 4rem;"></i>
                        </div>
                        <h3 class="card-title h2 mb-3">Mis Préstamos</h3>
                        <p class="text-muted mb-4">Solicita préstamos y consulta tu historial.</p>
                        <div class="d-grid">
                            <button class="btn btn-warning btn-lg rounded-pill">
                                <i class="bi bi-arrow-right-circle me-2"></i>Acceder
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="./wwwroot/js/bootstrap.bundle.min.js"></script>
</body>
</html>
