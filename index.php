<?php
session_start();
require 'vendor/autoload.php'; // cargar librerías si se requieren aquí

// 🔗 Conexión a la base de datos centralizada
include 'conexion.php';


// Procesar login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Preparar consulta en mysqli
    $stmt = $conexion->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $user = $resultado->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username']= $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['store_id'] = $user['store_id'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos o usuario bloqueado.';
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Stocky - Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container d-flex justify-content-center align-items-center vh-100">
  <div class="card p-4" style="width:350px;">
     <!-- Logo centrado -->
    <img src="Logo.PNG" alt="Stocky Logo" class="img-fluid mb-3" style="max-height:100px; object-fit:contain;">
    
    <h4 class="mb-3 text-center">Inicio de Sesion</h4>
    <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST">
      <div class="mb-3">
        <label>Usuario</label>
        <input type="text" name="username" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Contraseña</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button class="btn btn-primary w-100">Ingresar</button>
    </form>
  </div>
</div>
</body>
</html>