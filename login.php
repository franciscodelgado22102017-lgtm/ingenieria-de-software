<?php
session_start();
require_once 'db.php';

$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$pwd = isset($_POST['pwd']) ? $_POST['pwd'] : '';
$remember = isset($_POST['remember']) ? (int)$_POST['remember'] : 0;

// LOG PARA DEPURAR
error_log("=== LOGIN DEBUG ===");
error_log("Email: $email");
error_log("Remember: $remember");

if (empty($email) || empty($pwd)) {
    if ($is_ajax) {
        echo "error_campos_vacios";
    } else {
        header("Location: index.php?error=campos_vacios");
    }
    exit();
}

try {
    $db = conectarDB();
    $sql = "SELECT id_usuario, nombre, email, password FROM usuarios WHERE email = :email";
    $query = $db->prepare($sql);
    $query->execute(['email' => $email]);
    $usuario = $query->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($pwd, $usuario['password'])) {
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['username'] = $usuario['nombre'];
        $_SESSION['email'] = $usuario['email'];
        
        error_log("Usuario autenticado: " . $usuario['id_usuario']);
        
        // CREAR COOKIES SIEMPRE (para pruebas), no solo cuando remember=1
        $expiry = time() + (86400 * 30); // 30 días
        
        // Crear cookies usando el método más básico y compatible
        setcookie("id_usuario", (string)$usuario['id_usuario'], $expiry, "/");
        setcookie("username", $usuario['nombre'], $expiry, "/");
        setcookie("user_email", $usuario['email'], $expiry, "/");
        
        error_log("Cookies creadas - id_usuario: " . $usuario['id_usuario']);

        if ($is_ajax) {
            echo "success";
        } else {
            header("Location: home.php");
        }
        exit();
    } else {
        error_log("Error de autenticación para: $email");
        if ($is_ajax) {
            echo "error_password";
        } else {
            header("Location: index.php?error=password_incorrecta");
        }
        exit();
    }
} catch (PDOException $e) {
    error_log("Error DB: " . $e->getMessage());
    if ($is_ajax) {
        echo "error_db";
    } else {
        header("Location: index.php?error=db_error");
    }
    exit();
}
?>
