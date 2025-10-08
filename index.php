<?php
session_start();

// 🔗 Conexión a la base de datos centralizada
include 'conexion.php';

// Procesar login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Buscar usuario (sin filtrar por status todavía)
    $stmt = $conexion->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $user = $resultado->fetch_assoc();

    if ($user) {
        // Si el usuario está bloqueado → no lo dejamos avanzar
        if ($user['status'] === 'blocked') {
            $error = '⚠️ Tu cuenta ha sido bloqueada. Contacta con el administrador.';
        } else {
            // Caso 1: Primera vez (sin contraseña definida)
            if (empty($user['password_hash'])) {
                $new_hash = password_hash($password, PASSWORD_DEFAULT);

                $upd = $conexion->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $upd->bind_param("si", $new_hash, $user['id']);
                $upd->execute();
                $upd->close();

                // Iniciar sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['store_id'] = $user['store_id'];
                header("Location: dashboard.php");
                exit;
            }

            // Caso 2: Ya tiene contraseña → verificar
            if (password_verify($password, $user['password_hash'])) {
                if ($user['status'] === 'active') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['store_id'] = $user['store_id'];
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error = 'Tu cuenta no está activa. Contacta con el administrador.';
                }
            } else {
                $error = 'Contraseña incorrecta.';
            }
        }
    } else {
        $error = 'Usuario no encontrado.';
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
  <link rel="icon" type="image/png" href="img/favicon.png">
<link rel="shortcut icon" href="img/favicon.png">
</head>
<body>
<div class="container d-flex justify-content-center align-items-center vh-100">
  <div class="card p-4" style="width:350px;">
     <!-- Logo centrado -->
    <img src="Logo.PNG" alt="Stocky Logo" class="img-fluid mb-3" style="max-height:100px; object-fit:contain;">
    
    <h4 class="mb-3 text-center">Inicio de Sesión</h4>
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
      <!-- Versión del sistema -->
      
    </form>
    <p class="mt-3 text-center">
  <a href="quienes_somos.php" style="text-decoration:none; color:#007bff;">¿Quiénes somos?</a>
</p>

    <p class="mt-4 mb-0 text-center" style="color:#2C3E50; font-weight:500;">V.1.02</p>
  </div>
   <!-- Versión debajo de la tarjeta -->
 
</div>

<?php include 'footer.php'; ?>
</body>
</html>
