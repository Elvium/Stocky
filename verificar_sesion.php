<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'conexion.php';

// Verificar login
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    !isset($_SESSION['username'])
) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$user_id  = $_SESSION['user_id'];
$role     = $_SESSION['role'];
$username = $_SESSION['username'];

// 🔎 Verificar estado en BD cada vez que carga la página
$sql = "SELECT status FROM users WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'blocked') {
        // Cerrar sesión y redirigir
        session_destroy();
        echo "<script>alert('Tu cuenta ha sido bloqueada. Contacta al administrador.'); window.location.href='index.php';</script>";
        exit;
    }
}

$stmt->close();
