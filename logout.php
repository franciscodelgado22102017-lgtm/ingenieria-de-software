<?php
// logout.php
session_start();
session_unset();
session_destroy();

// Eliminar cookies
setcookie("id_usuario", "", time() - 3600, "/");
setcookie("username", "", time() - 3600, "/");
setcookie("user_email", "", time() - 3600, "/");

// Redirigir al login (AHORA index.php)
header("Location: index.php");
exit();
?>
