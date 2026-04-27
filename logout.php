<?php
session_start();
session_unset();    // Elimina las variables
session_destroy();  // Destruye la sesión
header("Location: login.html");
exit();
?>