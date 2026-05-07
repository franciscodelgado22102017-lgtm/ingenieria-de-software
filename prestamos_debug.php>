<?php
// prestamos_debug.php - Versión para depurar error 500
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

session_start();

echo "=== INICIO DEBUG ===<br>";
echo "Paso 1: Sesión iniciada<br>";

if (!isset($_SESSION['id_usuario'])) {
    echo "ERROR: Usuario no autenticado<br>";
    echo "Redirigiendo a index.html<br>";
    // header("Location: index.html");
    // exit();
} else {
    echo "Usuario autenticado: " . $_SESSION['id_usuario'] . "<br>";
}

echo "Paso 2: Incluyendo db.php<br>";
require_once 'db.php';
echo "db.php incluido correctamente<br>";

echo "Paso 3: Conectando a base de datos<br>";
try {
    $db = conectarDB();
    echo "Conexión exitosa a la base de datos<br>";
} catch (Exception $e) {
    echo "ERROR de conexión: " . $e->getMessage() . "<br>";
    die();
}

echo "Paso 4: Verificando tablas<br>";
$tablas = ['usuarios', 'libros', 'autores', 'prestamos'];
foreach ($tablas as $tabla) {
    $check = $db->query("SHOW TABLES LIKE '$tabla'");
    if ($check->rowCount() > 0) {
        echo "✓ Tabla '$tabla' existe<br>";
    } else {
        echo "✗ Tabla '$tabla' NO existe - ¡Este es el problema!<br>";
    }
}

echo "=== FIN DEBUG ===<br>";
?>
