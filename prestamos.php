<?php
// prestamos.php - Versión corregida con manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores al usuario
ini_set('log_errors', 1);

session_start();

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.html");
    exit();
}

require_once 'db.php';

$db = conectarDB();
$mensaje = '';

// Verificar que la tabla prestamos existe
try {
    $check = $db->query("SHOW TABLES LIKE 'prestamos'");
    if ($check->rowCount() == 0) {
        // Crear la tabla si no existe
        $sql = "CREATE TABLE IF NOT EXISTS prestamos (
            id_prestamo INT AUTO_INCREMENT PRIMARY KEY,
            id_usuario INT NOT NULL,
            id_libro INT NOT NULL,
            fecha_prestamo DATE NOT NULL,
            fecha_devolucion_esperada DATE NOT NULL,
            fecha_devolucion_real DATE NULL,
            estado VARCHAR(20) DEFAULT 'prestado',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
            FOREIGN KEY (id_libro) REFERENCES libros(id_libro) ON DELETE CASCADE
        )";
        $db->exec($sql);
        $mensaje = '<div class="alert alert-info">Tabla de préstamos creada correctamente.</div>';
    }
} catch (Exception $e) {
    $mensaje = '<div class="alert alert-danger">Error al verificar/crear tabla: ' . $e->getMessage() . '</div>';
}

// Verificar que libros tenga campo disponibles
try {
    $check = $db->query("SHOW COLUMNS FROM libros LIKE 'disponibles'");
    if ($check->rowCount() == 0) {
        $db->exec("ALTER TABLE libros ADD COLUMN disponibles INT DEFAULT 1");
    }
} catch (Exception $e) {
    // Ignorar error si ya existe
}

// Procesar nuevo préstamo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_prestamo'])) {
    try {
        $id_usuario = intval($_POST['id_usuario']);
        $id_libro = intval($_POST['id_libro']);
        $fecha_prestamo = date('Y-m-d');
        $fecha_devolucion = !empty($_POST['fecha_devolucion']) ? $_POST['fecha_devolucion'] : date('Y-m-d', strtotime('+7 days'));
        $estado = 'prestado';
        
        // Verificar que el libro esté disponible
        $checkLibro = $db->prepare("SELECT disponibles FROM libros WHERE id_libro = :id_libro");
        $checkLibro->execute(['id_libro' => $id_libro]);
        $libro = $checkLibro->fetch();
        
        if ($libro && $libro['disponibles'] > 0) {
            $db->beginTransaction();
            
            // Insertar préstamo
            $sql = "INSERT INTO prestamos (id_usuario, id_libro, fecha_prestamo, fecha_devolucion_esperada, estado) 
                    VALUES (:id_usuario, :id_libro, :fecha_prestamo, :fecha_devolucion, :estado)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'id_usuario' => $id_usuario,
                'id_libro' => $id_libro,
                'fecha_prestamo' => $fecha_prestamo,
                'fecha_devolucion' => $fecha_devolucion,
                'estado' => $estado
            ]);
            
            // Actualizar disponible del libro
            $updateLibro = $db->prepare("UPDATE libros SET disponibles = disponibles - 1 WHERE id_libro = :id_libro");
            $updateLibro->execute(['id_libro' => $id_libro]);
            
            $db->commit();
            $mensaje = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>✅ Préstamo registrado correctamente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
        } else {
            $mensaje = '<div class="alert alert-warning">⚠️ El libro no está disponible para préstamo.</div>';
        }
    } catch (Exception $e) {
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        $mensaje = '<div class="alert alert-danger">❌ Error al registrar préstamo: ' . $e->getMessage() . '</div>';
    }
}

// Procesar devolución
if (isset($_GET['devolver'])) {
    try {
        $id_prestamo = intval($_GET['devolver']);
        
        $db->beginTransaction();
        
        // Obtener el id_libro del préstamo
        $getLibro = $db->prepare("SELECT id_libro FROM prestamos WHERE id_prestamo = :id_prestamo AND estado = 'prestado'");
        $getLibro->execute(['id_prestamo' => $id_prestamo]);
        $prestamo = $getLibro->fetch();
        
        if ($prestamo) {
            // Actualizar estado del préstamo
            $updatePrestamo = $db->prepare("UPDATE prestamos SET estado = 'devuelto', fecha_devolucion_real = NOW() WHERE id_prestamo = :id_prestamo");
            $updatePrestamo->execute(['id_prestamo' => $id_prestamo]);
            
            // Aumentar disponibles del libro
            $updateLibro = $db->prepare("UPDATE libros SET disponibles = disponibles + 1 WHERE id_libro = :id_libro");
            $updateLibro->execute(['id_libro' => $prestamo['id_libro']]);
            
            $db->commit();
            $mensaje = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>✅ Libro devuelto correctamente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
        } else {
            $db->rollBack();
            $mensaje = '<div class="alert alert-warning">⚠️ El préstamo no existe o ya fue devuelto.</div>';
        }
    } catch (Exception $e) {
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        $mensaje = '<div class="alert alert-danger">❌ Error al procesar devolución: ' . $e->getMessage() . '</div>';
    }
}

// Obtener datos para los selects (con manejo de errores)
try {
    $usuarios = $db->query("SELECT id_usuario, nombre, email FROM usuarios ORDER BY nombre")->fetchAll();
} catch (Exception $e) {
    $usuarios = [];
    $mensaje .= '<div class="alert alert-danger">Error al cargar usuarios: ' . $e->getMessage() . '</div>';
}

try {
    $librosDisponibles = $db->query("SELECT id_libro, titulo, isbn, disponibles FROM libros WHERE disponibles > 0 ORDER BY titulo")->fetchAll();
} catch (Exception $e) {
    $librosDisponibles = [];
    $mensaje .= '<div class="alert alert-danger">Error al cargar libros: ' . $e->getMessage() . '</div>';
}

try {
    $prestamosActivos = $db->query("
        SELECT p.id_prestamo, p.fecha_prestamo, p.fecha_devolucion_esperada, p.estado,
               u.nombre as usuario_nombre, u.email,
               l.titulo as libro_titulo, l.isbn
        FROM prestamos p
        JOIN usuarios u ON p.id_usuario = u.id_usuario
        JOIN libros l ON p.id_libro = l.id_libro
        WHERE p.estado = 'prestado'
        ORDER BY p.fecha_prestamo DESC
    ")->fetchAll();
} catch (Exception $e) {
    $prestamosActivos = [];
}

try {
    $historialPrestamos = $db->query("
        SELECT p.id_prestamo, p.fecha_prestamo, p.fecha_devolucion_esperada, p.fecha_devolucion_real, p.estado,
               u.nombre as usuario_nombre, u.email,
               l.titulo as libro_titulo, l.isbn
        FROM prestamos p
        JOIN usuarios u ON p.id_usuario = u.id_usuario
        JOIN libros l ON p.id_libro = l.id_libro
        ORDER BY p.fecha_prestamo DESC
        LIMIT 50
    ")->fetchAll();
} catch (Exception $e) {
    $historialPrestamos = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Préstamos - Biblioteca Digital</title>
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
        .nav-tabs .nav-link.active {
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
            background: transparent;
        }
        .estado-prestado { background-color: #ffc107; color: #000; }
        .estado-devuelto { background-color: #198754; color: #fff; }
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

    <div class="container mt-4">
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item"><a class="nav-link" href="libros.php"><i class="bi bi-book-fill me-2"></i>Libros</a></li>
            <li class="nav-item"><a class="nav-link" href="autores.php"><i class="bi bi-people-fill me-2"></i>Autores</a></li>
            <li class="nav-item"><a class="nav-link active" href="prestamos.php"><i class="bi bi-journal-arrow-up me-2"></i>Préstamos</a></li>
        </ul>

        <?php echo $mensaje; ?>

        <!-- Formulario registrar préstamo -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-plus-circle-fill me-2"></i>Registrar Nuevo Préstamo
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Usuario</label>
                        <select name="id_usuario" class="form-select" required>
                            <option value="">-- Selecciona un usuario --</option>
                            <?php foreach ($usuarios as $usuario): ?>
                                <option value="<?= $usuario['id_usuario'] ?>"><?= htmlspecialchars($usuario['nombre']) ?> (<?= htmlspecialchars($usuario['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Libro</label>
                        <select name="id_libro" class="form-select" required>
                            <option value="">-- Selecciona un libro --</option>
                            <?php foreach ($librosDisponibles as $libro): ?>
                                <option value="<?= $libro['id_libro'] ?>"><?= htmlspecialchars($libro['titulo']) ?> (Disponibles: <?= $libro['disponibles'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Fecha Devolución</label>
                        <input type="date" name="fecha_devolucion" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" name="registrar_prestamo" class="btn btn-primary w-100">+</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Préstamos Activos -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-clock-history me-2"></i>Préstamos Activos (<?= count($prestamosActivos) ?>)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr><th>ID</th><th>Usuario</th><th>Libro</th><th>Préstamo</th><th>Dev. Esperada</th><th>Estado</th><th>Acción</th></tr>
                        </thead>
                        <tbody>
                            <?php if (count($prestamosActivos) > 0): ?>
                                <?php foreach ($prestamosActivos as $p): ?>
                                <tr>
                                    <td><?= $p['id_prestamo'] ?></td>
                                    <td><?= htmlspecialchars($p['usuario_nombre']) ?><br><small><?= htmlspecialchars($p['email']) ?></small></td>
                                    <td><?= htmlspecialchars($p['libro_titulo']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($p['fecha_prestamo'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($p['fecha_devolucion_esperada'])) ?></td>
                                    <td><span class="badge bg-warning">Prestado</span></td>
                                    <td><a href="?devolver=<?= $p['id_prestamo'] ?>" class="btn btn-success btn-sm" onclick="return confirm('¿Registrar devolución?')">Devolver</a></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center py-4">No hay préstamos activos</td></tr>
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
