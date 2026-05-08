<?php
session_start();

function verificarAutenticacion() {
    if (isset($_SESSION['id_usuario']) && !empty($_SESSION['id_usuario'])) {
        return true;
    }
    
    if (isset($_COOKIE["id_usuario"]) && !empty($_COOKIE["id_usuario"])) {
        try {
            require_once __DIR__ . '/db.php';
            $db = conectarDB();
            $stmt = $db->prepare("SELECT id_usuario, nombre, email FROM usuarios WHERE id_usuario = :id");
            $stmt->execute(['id' => $_COOKIE["id_usuario"]]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario) {
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['username'] = $usuario['nombre'];
                $_SESSION['email'] = $usuario['email'];
                return true;
            } else {
                setcookie("id_usuario", "", time() - 3600, "/");
                setcookie("username", "", time() - 3600, "/");
                setcookie("user_email", "", time() - 3600, "/");
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }
    
    return false;
}

if (!verificarAutenticacion()) {
    header("Location: index.php");
    exit();
}
?>
