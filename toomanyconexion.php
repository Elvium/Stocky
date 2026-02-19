<?php
// 👇 Al final de todo el archivo, fuera del HTML
if (isset($conexion) && $conexion instanceof mysqli) {
    $conexion->close();
}
?>