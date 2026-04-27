<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.html");
    exit();
}
require_once 'db.php';

$db = conectarDB();

// Procesar formulario al agregar libro
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_libro'])) {
    $titulo = trim($_POST['titulo']);
    $isbn   = trim($_POST['isbn']);
    $id_autor = intval($_POST['id_autor']);
    $disponibles = 1;

    if (!empty($titulo) && !empty($isbn) && $id_autor > 0) {
        $sql = "INSERT INTO libros (titulo, isbn, id_autor, disponibles) VALUES (:titulo, :isbn, :id_autor, :disponibles)";
        $stmt = $db->prepare($sql);
        if ($stmt->execute([
            'titulo' => $titulo,
            'isbn' => $isbn,
            'id_autor' => $id_autor,
            'disponibles' => $disponibles
        ])) {
            $mensaje = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>✅ Libro agregado correctamente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
        } else {
            $mensaje = '<div class="alert alert-danger">❌ Error al agregar libro.</div>';
        }
    } else {
        $mensaje = '<div class="alert alert-warning">⚠️ Completa todos los campos.</div>';
    }
}

// Obtener lista de autores para el SELECT
$autores = $db->query("SELECT id_autor, nombre FROM autores ORDER BY nombre")->fetchAll();

// Obtener lista de libros con nombre del autor
$libros = $db->query("
    SELECT l.id_libro, l.titulo, l.isbn, a.nombre AS autor, l.disponibles
    FROM libros l
    JOIN autores a ON l.id_autor = a.id_autor
    ORDER BY l.id_libro DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Libros - Biblioteca Digital</title>
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
            color: #0d6efd;
            background: transparent;
        }
        .nav-tabs .nav-link.active {
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
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
    <!-- Navbar igual que autores.php -->
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
                <a class="nav-link active" href="libros.php">
                    <i class="bi bi-book-fill me-2"></i>Gestión de Libros
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="autores.php">
                    <i class="bi bi-people-fill me-2"></i>Gestión de Autores
                </a>
            </li>
        </ul>

        <?php echo $mensaje; ?>

        <!-- Formulario agregar libro -->
        <div class="card shadow-sm mb-4 border-0 hover-shadow">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-plus-circle-fill me-2"></i>Agregar Nuevo Libro
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">
                                <i class="bi bi-journal-bookmark-fill me-1"></i>Título del Libro
                            </label>
                            <input type="text" name="titulo" class="form-control" 
                                   placeholder="Ej. Cien años de soledad" required>
                            <div class="form-text">Ingresa el título completo del libro</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-upc-scan me-1"></i>ISBN
                            </label>
                            <input type="text" name="isbn" class="form-control" 
                                   placeholder="978-3-16-148410-0" required>
                            <div class="form-text">Código ISBN único del libro</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-person-fill me-1"></i>Autor
                            </label>
                            <select name="id_autor" class="form-select" required>
                                <option value="">-- Selecciona un autor --</option>
                                <?php foreach ($autores as $autor): ?>
                                    <option value="<?= $autor['id_autor'] ?>">
                                        <?= htmlspecialchars($autor['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Selecciona el autor del libro</div>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" name="agregar_libro" class="btn btn-primary w-100">
                                <i class="bi bi-save-fill fs-5"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de libros -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-info text-white">
                <i class="bi bi-table me-2"></i>Lista de Libros Registrados
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>ISBN</th>
                                <th>Autor</th>
                                <th>Disponibles</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($libros) > 0): ?>
                                <?php foreach ($libros as $libro): ?>
                                <tr>
                                    <td><?= $libro['id_libro'] ?></td>
                                    <td><strong><?= htmlspecialchars($libro['titulo']) ?></strong></td>
                                    <td><code><?= htmlspecialchars($libro['isbn']) ?></code></td>
                                    <td><?= htmlspecialchars($libro['autor']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $libro['disponibles'] > 0 ? 'success' : 'danger' ?>">
                                            <?= $libro['disponibles'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="eliminar_libro.php?id=<?= $libro['id_libro'] ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('¿Estás seguro de eliminar este libro?')">
                                            <i class="bi bi-trash-fill"></i> Eliminar
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                        <p>No hay libros registrados en el sistema</p>
                                        <small>Agrega tu primer libro usando el formulario superior</small>
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
