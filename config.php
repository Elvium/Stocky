<?php
date_default_timezone_set('America/Bogota');

$conexion = new mysqli(...);

$conexion->set_charset("utf8mb4");
$conexion->query("SET time_zone = '-05:00'");