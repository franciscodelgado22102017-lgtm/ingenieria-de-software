<?php
// login.php - Procesa login y crea cookies
session_start();
require_once 'db.php';

// Permitir peticiones AJAX
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$pwd = isset($_POST['pwd']) ? $_POST['pwd'] : '';
$remember = isset($_POST['remember']) ? (int)$_POST['remember'] : 0;

if (empty($email) || empty($pwd)) {
    if ($is_ajax) {
        echo "error_campos_vacios";
    } else {
        header("Location: index.html?error=campos_vacios");
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

        if ($remember == 1) {
            $expiry = time() + (86400 * 30);
            setcookie("id_usuario", $usuario['id_usuario'], $expiry, "/");
            setcookie("username", $usuario['nombre'], $expiry, "/");
            setcookie("user_email", $usuario['email'], $expiry, "/");
        }

        if ($is_ajax) {
            echo "success";
        } else {
            header("Location: home.php");
        }
        exit();
    } else {
        if ($is_ajax) {
            echo "error_password";
        } else {
            header("Location: index.html?error=password_incorrecta");
        }
        exit();
    }
} catch (PDOException $e) {
    if ($is_ajax) {
        echo "error_db";
    } else {
        header("Location: index.html?error=db_error");
    }
    exit();
}
?>
