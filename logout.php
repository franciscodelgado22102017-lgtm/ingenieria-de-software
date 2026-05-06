<?php
// logout.php - Destruir sesión y eliminar cookies
session_start();

// Destruir la sesión
session_unset();
session_destroy();

// Eliminar las cookies si existen
$cookie_names = ['id_usuario', 'username', 'user_email'];
foreach ($cookie_names as $name) {
    if (isset($_COOKIE[$name])) {
        setcookie($name, "", time() - 3600, "/");
    }
}

// Redirigir al login
header("Location: index.php");
exit();
?>
