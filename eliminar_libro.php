<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.html");
    exit();
}
require_once 'db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $db = conectarDB();
    $stmt = $db->prepare("DELETE FROM libros WHERE id_libro = :id");
    $stmt->execute(['id' => $id]);
}
header("Location: libros.php");
exit();
?>
