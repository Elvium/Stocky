<?php
session_start();
require 'conexion.php';
require 'verificar_sesion.php';
// Verificar login y datos de sesión obligatorios
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
$store_id = isset($_SESSION['store_id']) ? $_SESSION['store_id'] : null;

// ================== CERRAR SESIÓN ==================
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}


// ================== FUNCIONALIDAD ADMIN ==================
if ($role === 'super') {
    // Crear nueva tienda
    if (isset($_POST['nueva_tienda'])) {
        $nombre_tienda = trim($_POST['nombre_tienda']);

        if (!empty($nombre_tienda)) {
            // Insertar en stores
            $stmt = $conexion->prepare("INSERT INTO stores (name) VALUES (?)");
            $stmt->bind_param("s", $nombre_tienda);
            $stmt->execute();
            $store_new_id = $stmt->insert_id;
            $stmt->close();

            // Crear usuario por defecto con la tienda como username
            $password_hash = null;
            $role_user = "user";
            $status = "active";

            $stmt = $conexion->prepare("INSERT INTO users (username, password_hash, role, store_id, status) VALUES (?,?,?,?,?)");
            $stmt->bind_param("sssis", $nombre_tienda, $password_hash, $role_user, $store_new_id, $status);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Bloquear/desbloquear usuarios
    if (isset($_GET['toggle_user'])) {
        $uid = intval($_GET['toggle_user']);
        $res = $conexion->query("SELECT status FROM users WHERE id = $uid");
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $new_status = ($row['status'] === 'active') ? 'blocked' : 'active';
            $conexion->query("UPDATE users SET status = '$new_status' WHERE id = $uid");
        }
        header("Location: dashboard.php");
        exit;
    }

    // Cargar todos los usuarios
    $usuarios = $conexion->query("SELECT u.id, u.username, u.store_id, u.status, s.name AS store_name 
                                  FROM users u 
                                  LEFT JOIN stores s ON u.store_id = s.id
                                  WHERE u.role = 'user' 
                                  ORDER BY u.id DESC");
}

// ================== FUNCIONALIDAD USUARIOS TIENDA ==================
if ($role === 'user') {
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

    // Consultas
    $inventario = $conexion->query("SELECT * FROM inventory WHERE user_id = $user_id");
    $productos  = $conexion->query("SELECT * FROM products WHERE user_id = $user_id");
    $ventas     = $conexion->query("SELECT * FROM sales WHERE user_id = $user_id ORDER BY created_at DESC");
}
 if (isset($_SESSION['blocked_message'])): ?>
  <div class="global-message error">
    <?= $_SESSION['blocked_message']; ?>
  </div>
  <?php unset($_SESSION['blocked_message']); ?>
<?php endif; ?>


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
        <?php if ($role === 'user'): ?>
          <li class="nav-item"><a class="nav-link" href="#inventario">Inventario</a></li>
          <li class="nav-item"><a class="nav-link" href="#productos">Productos</a></li>
          <li class="nav-item"><a class="nav-link" href="#ventas">Ventas</a></li>
        <?php elseif ($role === 'super'): ?>
          <li class="nav-item"><a class="nav-link" href="#gestion">Gestión</a></li>
        <?php endif; ?>
      </ul>
      <a href="dashboard.php?logout=1" class="btn btn-danger btn-sm">Cerrar sesión</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h3 class="mb-4">Bienvenido, <?php echo ($role === 'super') ? "Administrador" : "Tienda $username"; ?></h3>

  <?php if ($role === 'super'): ?>
    <!-- ================== GESTIÓN ADMIN ================== -->
    <section id="gestion" class="mb-5">
      <h4>🏬 Crear nueva tienda</h4>
      <form method="post" class="row g-3 mb-3">
        <input type="hidden" name="nueva_tienda" value="1">
        <div class="col-md-8">
          <input type="text" name="nombre_tienda" class="form-control" placeholder="Nombre de la tienda" required>
        </div>
        <div class="col-md-4">
          <button class="btn btn-primary w-100">Crear</button>
        </div>
      </form>

      <h4>👥 Usuarios registrados</h4>
      <input type="text" id="buscar" class="form-control mb-3" placeholder="Buscar tienda...">

      <table class="table table-bordered" id="tablaUsuarios">
        <thead>
          <tr><th>ID</th><th>Tienda</th><th>Status</th><th>Acción</th></tr>
        </thead>
        <tbody>
          <?php while($row = $usuarios->fetch_assoc()): ?>
          <tr>
            <td><?php echo $row['store_id']; ?></td>
            <td><?php echo $row['username']; ?></td>
            <td><?php echo $row['status']; ?></td>
            <td>
              <a href="dashboard.php?toggle_user=<?php echo $row['id']; ?>" 
                 class="btn btn-sm <?php echo ($row['status'] === 'active') ? 'btn-danger' : 'btn-success'; ?>">
                 <?php echo ($row['status'] === 'active') ? 'Bloquear' : 'Desbloquear'; ?>
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </section>

    <script>
    // Buscador en tiempo real (simple con JS)
    document.getElementById("buscar").addEventListener("keyup", function() {
        let filtro = this.value.toLowerCase();
        let filas = document.querySelectorAll("#tablaUsuarios tbody tr");
        filas.forEach(fila => {
            let texto = fila.textContent.toLowerCase();
            fila.style.display = texto.includes(filtro) ? "" : "none";
        });
    });
    </script>

  <?php elseif ($role === 'user'): ?>
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
  <?php endif; ?>
</div>
</body>
</html>
