<?php
/* ============================================================
   conexion.php - conexión central con MySQLi
   ============================================================ */

$host = "localhost";
$user = "c2880148_stocky";
$password = "tapoGI63vi";
$dbname = "c2880148_stocky";

// Crear conexión
$conexion = new mysqli($host, $user, $password, $dbname);

// Verificar conexión
if ($conexion->connect_error) {
    die("❌ Error de conexión: " . $conexion->connect_error);
}

/*
 Nota:
 - Para usarlo en cualquier archivo solo:
   include 'conexion.php';
 - Y ya tendrás disponible la variable $conexion
 - Ejemplo de consulta:
   $sql = "SELECT * FROM productos";
   $resultado = $conexion->query($sql);
*/
?>
