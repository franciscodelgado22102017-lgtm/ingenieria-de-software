<?php
session_start();

$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

session_destroy();

setcookie("id_usuario", "", time() - 3600, "/");
setcookie("username", "", time() - 3600, "/");
setcookie("user_email", "", time() - 3600, "/");

header("Location: index.php");
exit();
?>
