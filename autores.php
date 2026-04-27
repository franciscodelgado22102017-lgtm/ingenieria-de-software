<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.html");
    exit();
}
require_once 'db.php';

$db = conectarDB();

// Agregar autor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_autor'])) {
    $nombre = trim($_POST['nombre']);
    if (!empty($nombre)) {
        $stmt = $db->prepare("INSERT INTO autores (nombre) VALUES (:nombre)");
        $stmt->execute(['nombre' => $nombre]);
        $mensaje = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>✅ Autor agregado correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
    } else {
        $mensaje = '<div class="alert alert-warning">⚠️ El nombre del autor no puede estar vacío.</div>';
    }
}

// Eliminar autor
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    
    // Verificar si el autor tiene libros asociados
    $check = $db->prepare("SELECT COUNT(*) as total FROM libros WHERE id_autor = :id");
    $check->execute(['id' => $id]);
    $tieneLibros = $check->fetch()['total'] > 0;
    
    if ($tieneLibros) {
        $mensaje = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>❌ No se puede eliminar el autor porque tiene libros asociados.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
    } else {
        $db->prepare("DELETE FROM autores WHERE id_autor = :id")->execute(['id' => $id]);
        $mensaje = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>✅ Autor eliminado correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
    }
}

$autores = $db->query("SELECT id_autor, nombre FROM autores ORDER BY nombre")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Autores - Biblioteca Digital</title>
    <link href="./wwwroot/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./wwwroot/css/bootstrap-icons.min.css">
    <style>
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            padding: 12px 24px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .nav-tabs .nav-link:hover {
            color: #198754;
            background: transparent;
        }
        .nav-tabs .nav-link.active {
            color: #198754;
            border-bottom: 3px solid #198754;
            background: transparent;
        }
        .hover-shadow {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .hover-shadow:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
    </style>
</head>
<body>
    <!-- Navbar igual que libros.php -->
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

    <div class="container mt-4">
        <!-- Pestañas de navegación -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link" href="libros.php">
                    <i class="bi bi-book-fill me-2"></i>Gestión de Libros
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="autores.php">
                    <i class="bi bi-people-fill me-2"></i>Gestión de Autores
                </a>
            </li>
        </ul>

        <?php if (isset($mensaje)) echo $mensaje; ?>

        <!-- Formulario agregar autor -->
        <div class="card shadow-sm mb-4 border-0 hover-shadow">
            <div class="card-header bg-success text-white">
                <i class="bi bi-person-plus-fill me-2"></i>Agregar Nuevo Autor
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">
                            <i class="bi bi-person-badge-fill me-1"></i>Nombre del Autor
                        </label>
                        <input type="text" name="nombre" class="form-control" 
                               placeholder="Ej. Gabriel García Márquez" required>
                        <div class="form-text">Ingresa el nombre completo del autor</div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" name="nuevo_autor" class="btn btn-success w-100">
                            <i class="bi bi-save-fill me-1"></i>Agregar Autor
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de autores -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-info text-white">
                <i class="bi bi-table me-2"></i>Lista de Autores Registrados
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre del Autor</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($autores) > 0): ?>
                                <?php foreach ($autores as $autor): ?>
                                <tr>
                                    <td><?= $autor['id_autor'] ?></td>
                                    <td><strong><?= htmlspecialchars($autor['nombre']) ?></strong></td>
                                    <td>
                                        <a href="?eliminar=<?= $autor['id_autor'] ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('¿Estás seguro de eliminar este autor?')">
                                            <i class="bi bi-trash-fill"></i> Eliminar
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                        <p>No hay autores registrados en el sistema</p>
                                        <small>Agrega tu primer autor usando el formulario superior</small>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="./wwwroot/js/bootstrap.bundle.min.js"></script>
</body>
</html>
