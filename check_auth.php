<?php
// check_auth.php - Verificar autenticación con sesión o cookie
// Incluir este archivo al inicio de CADA página protegida

session_start();

// Función para verificar si el usuario está autenticado
function verificarAutenticacion() {
    // Primero verificar si existe sesión activa
    if (isset($_SESSION['id_usuario']) && !empty($_SESSION['id_usuario'])) {
        return true;
    }
    
    // Si no hay sesión, verificar cookie
    if (isset($_COOKIE["id_usuario"]) && !empty($_COOKIE["id_usuario"])) {
        // Restaurar sesión desde la cookie
        $_SESSION['id_usuario'] = $_COOKIE["id_usuario"];
        
        // Cargar datos adicionales del usuario desde BD
        try {
            require_once __DIR__ . '/db.php';
            $db = conectarDB();
            $stmt = $db->prepare("SELECT nombre, email FROM usuarios WHERE id_usuario = :id");
            $stmt->execute(['id' => $_SESSION['id_usuario']]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario) {
                $_SESSION['username'] = $usuario['nombre'];
                $_SESSION['email'] = $usuario['email'];
                return true;
            } else {
                // Cookie inválida, eliminarla
                setcookie("id_usuario", "", time() - 3600, "/");
                setcookie("username", "", time() - 3600, "/");
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }
    
    return false;
}

// Verificar autenticación
if (!verificarAutenticacion()) {
    // No está autenticado, redirigir al login
    header("Location: index.php");
    exit();
}
?>
