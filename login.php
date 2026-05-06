<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

header('Content-Type: text/html; charset=utf-8');

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$pwd = isset($_POST['pwd']) ? $_POST['pwd'] : '';

if (empty($email) || empty($pwd)) {
    echo "error";
    exit();
}

try {
    $db = conectarDB();
    
    $sql = "SELECT id_usuario, nombre, email, password FROM usuarios WHERE email = :email";
    $query = $db->prepare($sql);
    $query->execute(['email' => $email]);
    
    $usuario = $query->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        echo "error";
        exit();
    }
    
    // Verificar contraseña
    if (password_verify($pwd, $usuario['password'])) {
        session_start();
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['username'] = $usuario['nombre'];
        $_SESSION['email'] = $usuario['email'];
        
        echo "success";
    } else {
        echo "error";
    }
    
} catch (PDOException $e) {
    echo "error";
}
?>
