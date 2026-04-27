<?php
// login_debug.php - Versión que muestra errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

header('Content-Type: text/html; charset=utf-8');

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$pwd = isset($_POST['pwd']) ? $_POST['pwd'] : '';

// Mostrar lo que recibimos
echo "DEBUG: Email recibido = '$email'<br>";
echo "DEBUG: Password recibida = " . (empty($pwd) ? 'VACIA' : 'RECIBIDA') . "<br>";

if (empty($email) || empty($pwd)) {
    echo "error - Campos vacios";
    exit();
}

try {
    $db = conectarDB();
    echo "DEBUG: Conexion DB exitosa<br>";
    
    $sql = "SELECT id_usuario, nombre, email, password FROM usuarios WHERE email = :email";
    $query = $db->prepare($sql);
    $query->execute(['email' => $email]);
    
    $usuario = $query->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        echo "error - Usuario no encontrado: $email";
        exit();
    }
    
    echo "DEBUG: Usuario encontrado: " . $usuario['nombre'] . "<br>";
    echo "DEBUG: Hash almacenado: " . substr($usuario['password'], 0, 30) . "...<br>";
    
    if (password_verify($pwd, $usuario['password'])) {
        session_start();
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['username'] = $usuario['nombre'];
        $_SESSION['email'] = $usuario['email'];
        
        echo "success";
    } else {
        echo "error - Password incorrecta";
    }
    
} catch (PDOException $e) {
    echo "error - DB: " . $e->getMessage();
}
?>
