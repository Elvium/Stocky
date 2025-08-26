<?php
session_start();
require 'conexion.php';

// Verificar login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'];
$store_id = $_SESSION['store_id'];

// ================== CERRAR SESIÓN ==================
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// ================== PROCESAR FORMULARIOS ==================

// Nuevo material
if (isset($_POST['nuevo_material'])) {
    $nombre = $_POST['nombre'];
    $cantidad = $_POST['cantidad'];
    $unidad = $_POST['unidad'];

    $stmt = $conexion->prepare("INSERT INTO inventory (user_id, name, quantity, unit) VALUES (?,?,?,?) 
                            ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
    $stmt->bind_param("isds", $user_id, $nombre, $cantidad, $unidad);
    $stmt->execute();
    $stmt->close();
}

// Nuevo producto
if (isset($_POST['nuevo_producto'])) {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];

    $stmt = $conexion->prepare("INSERT INTO products (user_id, name, price) VALUES (?,?,?)");
    $stmt->bind_param("isd", $user_id, $nombre, $precio);
    $stmt->execute();
    $stmt->close();
}

// Nueva venta
if (isset($_POST['nueva_venta'])) {
    $total = $_POST['total'];

    $stmt = $conexion->prepare("INSERT INTO sales (user_id, total) VALUES (?,?)");
    $stmt->bind_param("id", $user_id, $total);
    $stmt->execute();
    $stmt->close();
}

// ================== CONSULTAS ==================
$inventario = $conexion->query("SELECT * FROM inventory WHERE user_id = $user_id");
$productos  = $conexion->query("SELECT * FROM products WHERE user_id = $user_id");
$ventas     = $conexion->query("SELECT * FROM sales WHERE user_id = $user_id ORDER BY created_at DESC");

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Stocky</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Stocky</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="#inventario">Inventario</a></li>
        <li class="nav-item"><a class="nav-link" href="#productos">Productos</a></li>
        <li class="nav-item"><a class="nav-link" href="#ventas">Ventas</a></li>
      </ul>
      <a href="dashboard.php?logout=1" class="btn btn-danger btn-sm">Cerrar sesión</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h3 class="mb-4">Bienvenido, Tienda <?php echo $username; ?></h3>

  <!-- ================== INVENTARIO ================== -->
  <section id="inventario" class="mb-5">
    <h4>📦 Inventario</h4>
    <form method="post" class="row g-3 mb-3">
      <input type="hidden" name="nuevo_material" value="1">
      <div class="col-md-4">
        <input type="text" name="nombre" class="form-control" placeholder="Nombre del material" required>
      </div>
      <div class="col-md-3">
        <input type="number" step="0.01" name="cantidad" class="form-control" placeholder="Cantidad" required>
      </div>
      <div class="col-md-3">
        <input type="text" name="unidad" class="form-control" placeholder="Unidad (kg, lt, etc)" required>
      </div>
      <div class="col-md-2">
        <button class="btn btn-primary w-100">Agregar</button>
      </div>
    </form>

    <table class="table table-bordered">
      <thead>
        <tr><th>Nombre</th><th>Cantidad</th><th>Unidad</th><th>Fecha</th></tr>
      </thead>
      <tbody>
        <?php while($row = $inventario->fetch_assoc()): ?>
        <tr>
          <td><?php echo $row['name']; ?></td>
          <td><?php echo $row['quantity']; ?></td>
          <td><?php echo $row['unit']; ?></td>
          <td><?php echo $row['created_at']; ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </section>

  <!-- ================== PRODUCTOS ================== -->
  <section id="productos" class="mb-5">
    <h4>🍽️ Productos</h4>
    <form method="post" class="row g-3 mb-3">
      <input type="hidden" name="nuevo_producto" value="1">
      <div class="col-md-6">
        <input type="text" name="nombre" class="form-control" placeholder="Nombre del producto" required>
      </div>
      <div class="col-md-4">
        <input type="number" step="0.01" name="precio" class="form-control" placeholder="Precio" required>
      </div>
      <div class="col-md-2">
        <button class="btn btn-success w-100">Agregar</button>
      </div>
    </form>

    <table class="table table-bordered">
      <thead>
        <tr><th>Nombre</th><th>Precio</th><th>Fecha</th></tr>
      </thead>
      <tbody>
        <?php while($row = $productos->fetch_assoc()): ?>
        <tr>
          <td><?php echo $row['name']; ?></td>
          <td>$<?php echo number_format($row['price'], 2); ?></td>
          <td><?php echo $row['created_at']; ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </section>

  <!-- ================== VENTAS ================== -->
  <section id="ventas" class="mb-5">
    <h4>💵 Ventas</h4>
    <form method="post" class="row g-3 mb-3">
      <input type="hidden" name="nueva_venta" value="1">
      <div class="col-md-10">
        <input type="number" step="0.01" name="total" class="form-control" placeholder="Total de la venta" required>
      </div>
      <div class="col-md-2">
        <button class="btn btn-warning w-100">Registrar</button>
      </div>
    </form>

    <table class="table table-bordered">
      <thead>
        <tr><th>ID</th><th>Total</th><th>Fecha</th></tr>
      </thead>
      <tbody>
        <?php while($row = $ventas->fetch_assoc()): ?>
        <tr>
          <td><?php echo $row['id']; ?></td>
          <td>$<?php echo number_format($row['total'], 2); ?></td>
          <td><?php echo $row['created_at']; ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </section>

</div>
</body>
</html>
