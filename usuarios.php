<?php
// usuarios.php - Sin warnings de permisos
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

header('Content-Type: text/html; charset=utf-8');

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$pwd = isset($_POST['pwd']) ? $_POST['pwd'] : '';

if (empty($nombre) || empty($email) || empty($pwd)) {
    echo "Error: Campos vacíos";
    exit();
}

try {
    $db = conectarDB();
    
    $checkSql = "SELECT COUNT(*) as total FROM usuarios WHERE email = :email";
    $checkQuery = $db->prepare($checkSql);
    $checkQuery->execute(['email' => $email]);
    $result = $checkQuery->fetch(PDO::FETCH_ASSOC);
    
    if ($result['total'] > 0) {
        echo "El email '$email' ya existe";
        exit();
    }
    
    $sql = "INSERT INTO usuarios (nombre, email, password) VALUES (:nombre, :email, :password)";
    $query = $db->prepare($sql);
    $passwordHash = password_hash($pwd, PASSWORD_DEFAULT);
    
    $resultado = $query->execute([
        'nombre' => $nombre,
        'email' => $email,
        'password' => $passwordHash
    ]);
    
    if ($resultado) {
        $id = $db->lastInsertId();
        session_start();
        $_SESSION['id_usuario'] = $id;
        $_SESSION['username'] = $nombre;
        $_SESSION['email'] = $email;
        
        echo "success";
    } else {
        echo "Error al insertar";
    }
    
} catch (PDOException $e) {
    echo "Error de base de datos: " . $e->getMessage();
}
?>
