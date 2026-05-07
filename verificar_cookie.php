<?php
// verificar_cookie.php - Verifica si hay cookie activa y redirige
session_start();

// Si ya hay sesión activa
if (isset($_SESSION['id_usuario'])) {
    header("Location: home.php");
    exit();
}

// Verificar cookie
if (isset($_COOKIE["id_usuario"]) && !empty($_COOKIE["id_usuario"])) {
    try {
        require_once 'db.php';
        $db = conectarDB();
        $stmt = $db->prepare("SELECT id_usuario, nombre, email FROM usuarios WHERE id_usuario = :id");
        $stmt->execute(['id' => $_COOKIE["id_usuario"]]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['username'] = $usuario['nombre'];
            $_SESSION['email'] = $usuario['email'];
            header("Location: home.php");
            exit();
        }
    } catch (Exception $e) {
        setcookie("id_usuario", "", time() - 3600, "/");
    }
}

// No hay cookie válida, volver al login
header("Location: index.html");
exit();
?>
