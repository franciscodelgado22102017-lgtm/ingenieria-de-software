<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.html");
    exit();
}
require_once 'db.php';

$db = conectarDB();
$mensaje = '';

// Procesar nuevo préstamo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_prestamo')) {
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
        try {
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
        } catch (Exception $e) {
            $db->rollBack();
            $mensaje = '<div class="alert alert-danger">❌ Error al registrar préstamo: ' . $e->getMessage() . '</div>';
        }
    } else {
        $mensaje = '<div class="alert alert-warning">⚠️ El libro no está disponible para préstamo.</div>';
    }
}

// Procesar devolución
if (isset($_GET['devolver'])) {
    $id_prestamo = intval($_GET['devolver']);
    
    try {
        $db->beginTransaction();
        
        // Obtener el id_libro del préstamo
        $getLibro = $db->prepare("SELECT id_libro FROM prestamos WHERE id_prestamo = :id_prestamo");
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
        }
    } catch (Exception $e) {
        $db->rollBack();
        $mensaje = '<div class="alert alert-danger">❌ Error al procesar devolución.</div>';
    }
}

// Obtener lista de usuarios
$usuarios = $db->query("SELECT id_usuario, nombre, email FROM usuarios ORDER BY nombre")->fetchAll();

// Obtener libros disponibles
$librosDisponibles = $db->query("SELECT id_libro, titulo, isbn, disponibles FROM libros WHERE disponibles > 0 ORDER BY titulo")->fetchAll();

// Obtener préstamos activos
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

// Obtener historial de préstamos
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
        .estado-prestado {
            background-color: #ffc107;
            color: #000;
        }
        .estado-devuelto {
            background-color: #198754;
            color: #fff;
        }
        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
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

    <div class="container mt-4">
        <!-- Pestañas de navegación -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link" href="libros.php">
                    <i class="bi bi-book-fill me-2"></i>Libros
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="autores.php">
                    <i class="bi bi-people-fill me-2"></i>Autores
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="prestamos.php">
                    <i class="bi bi-journal-arrow-up me-2"></i>Préstamos
                </a>
            </li>
        </ul>

        <?php echo $mensaje; ?>

        <!-- Formulario registrar préstamo -->
        <div class="card shadow-sm mb-4 border-0 hover-shadow">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-plus-circle-fill me-2"></i>Registrar Nuevo Préstamo
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-person-fill me-1"></i>Usuario
                        </label>
                        <select name="id_usuario" class="form-select" required>
                            <option value="">-- Selecciona un usuario --</option>
                            <?php foreach ($usuarios as $usuario): ?>
                                <option value="<?= $usuario['id_usuario'] ?>">
                                    <?= htmlspecialchars($usuario['nombre']) ?> (<?= htmlspecialchars($usuario['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-book-fill me-1"></i>Libro
                        </label>
                        <select name="id_libro" class="form-select" required>
                            <option value="">-- Selecciona un libro --</option>
                            <?php foreach ($librosDisponibles as $libro): ?>
                                <option value="<?= $libro['id_libro'] ?>">
                                    <?= htmlspecialchars($libro['titulo']) ?> (Disponibles: <?= $libro['disponibles'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (count($librosDisponibles) == 0): ?>
                            <div class="text-danger small">⚠️ No hay libros disponibles para préstamo</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">
                            <i class="bi bi-calendar-fill me-1"></i>Fecha Devolución
                        </label>
                        <input type="date" name="fecha_devolucion" class="form-control" 
                               value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                        <div class="form-text">Déjalo en blanco para 7 días</div>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" name="registrar_prestamo" class="btn btn-primary w-100" <?= count($librosDisponibles) == 0 ? 'disabled' : '' ?>>
                            <i class="bi bi-save-fill fs-5"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Préstamos Activos -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-clock-history me-2"></i>Préstamos Activos
                <span class="badge bg-dark ms-2"><?= count($prestamosActivos) ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Libro</th>
                                <th>Fecha Préstamo</th>
                                <th>Fecha Devolución</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($prestamosActivos) > 0): ?>
                                <?php foreach ($prestamosActivos as $prestamo): ?>
                                <tr>
                                    <td><?= $prestamo['id_prestamo'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($prestamo['usuario_nombre']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($prestamo['email']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($prestamo['libro_titulo']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($prestamo['fecha_prestamo'])) ?></td>
                                    <td>
                                        <?php 
                                        $fechaEsperada = new DateTime($prestamo['fecha_devolucion_esperada']);
                                        $hoy = new DateTime();
                                        $diasRestantes = $hoy->diff($fechaEsperada)->days;
                                        $estaVencido = $fechaEsperada < $hoy;
                                        ?>
                                        <span class="badge <?= $estaVencido ? 'bg-danger' : 'bg-info' ?>">
                                            <?= date('d/m/Y', strtotime($prestamo['fecha_devolucion_esperada'])) ?>
                                            <?php if (!$estaVencido): ?>
                                                (<?= $diasRestantes ?> días)
                                            <?php else: ?>
                                                (VENCIDO)
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td><span class="badge estado-prestado">Prestado</span></td>
                                    <td>
                                        <a href="?devolver=<?= $prestamo['id_prestamo'] ?>" 
                                           class="btn btn-success btn-sm" 
                                           onclick="return confirm('¿Registrar devolución de este libro?')">
                                            <i class="bi bi-arrow-return-left"></i> Devolver
                                        </a>
                                     </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                        <p>No hay préstamos activos en el sistema</p>
                                        <small>Registra un nuevo préstamo usando el formulario superior</small>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Historial de Préstamos -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-secondary text-white">
                <i class="bi bi-archive-fill me-2"></i>Historial de Préstamos
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Libro</th>
                                <th>Préstamo</th>
                                <th>Dev. Esperada</th>
                                <th>Dev. Real</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($historialPrestamos) > 0): ?>
                                <?php foreach ($historialPrestamos as $prestamo): ?>
                                <tr>
                                    <td><?= $prestamo['id_prestamo'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($prestamo['usuario_nombre']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($prestamo['email']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($prestamo['libro_titulo']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($prestamo['fecha_prestamo'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($prestamo['fecha_devolucion_esperada'])) ?></td>
                                    <td>
                                        <?= $prestamo['fecha_devolucion_real'] ? date('d/m/Y', strtotime($prestamo['fecha_devolucion_real'])) : '—' ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $prestamo['estado'] == 'devuelto' ? 'bg-success' : 'bg-warning' ?>">
                                            <?= ucfirst($prestamo['estado']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                        <p>No hay historial de préstamos</p>
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
