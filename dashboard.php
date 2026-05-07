<?php
require_once 'check_auth.php'; 

require_once 'db.php';
$db = conectarDB();

// Obtener estadísticas del usuario
$stmt = $db->prepare("SELECT COUNT(*) as total FROM prestamos WHERE id_usuario = :id AND estado = 'prestado'");
$stmt->execute(['id' => $_SESSION['id_usuario']]);
$prestamosActivos = $stmt->fetch();

$stmt = $db->prepare("SELECT COUNT(*) as total FROM prestamos WHERE id_usuario = :id");
$stmt->execute(['id' => $_SESSION['id_usuario']]);
$totalPrestamos = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Biblioteca Digital</title>
    <link href="./wwwroot/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./wwwroot/css/bootstrap-icons.min.css">
    <style>
        .dashboard-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container">
            <span class="navbar-brand">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard
            </span>
            <div>
                <span class="text-white me-3">
                    <i class="bi bi-person-circle me-1"></i>
                    <?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuario'); ?>
                </span>
                <a href="home.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="bi bi-house-door"></i> Home
                </a>
                <a href="logout.php" class="btn btn-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <h3><i class="bi bi-person-circle me-2"></i>Bienvenido, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuario'); ?>!</h3>
                    <p>Email: <?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-0">📚 Préstamos activos: <strong><?php echo $prestamosActivos['total']; ?></strong></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-0">📖 Total préstamos realizados: <strong><?php echo $totalPrestamos['total']; ?></strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-4 mb-4">
                <div class="card dashboard-card shadow-sm">
                    <div class="card-body text-center p-4" onclick="window.location.href='libros.php'">
                        <i class="bi bi-book-fill text-primary" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">Gestión de Libros</h4>
                        <p class="text-muted">Administra tu colección de libros</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card dashboard-card shadow-sm">
                    <div class="card-body text-center p-4" onclick="window.location.href='autores.php'">
                        <i class="bi bi-people-fill text-success" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">Gestión de Autores</h4>
                        <p class="text-muted">Administra los autores</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card dashboard-card shadow-sm">
                    <div class="card-body text-center p-4" onclick="window.location.href='mis_prestamos.php'">
                        <i class="bi bi-journal-bookmark-fill text-warning" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">Mis Préstamos</h4>
                        <p class="text-muted">Solicita y gestiona tus préstamos</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="./wwwroot/js/bootstrap.bundle.min.js"></script>
</body>
</html>
