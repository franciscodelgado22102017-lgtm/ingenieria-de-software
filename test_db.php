<?php
require_once 'db.php';

try {
    $db = conectarDB();
    echo "<h1 style='color:green'>✅ Conexión exitosa a la base de datos!</h1>";
    
    // Prueba una consulta simple
    $query = $db->query("SELECT DATABASE() as db_name");
    $result = $query->fetch();
    echo "<p>Base de datos actual: <strong>" . $result['db_name'] . "</strong></p>";
    
} catch (Exception $e) {
    echo "<h1 style='color:red'>❌ Error de conexión</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>