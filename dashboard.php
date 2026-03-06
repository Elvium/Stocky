<?php
session_start();

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
    $res = $conexion->query("SELECT status, role, store_id FROM users WHERE id = $uid");

    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $new_status = ($row['status'] === 'active') ? 'blocked' : 'active';

        // Si es "user", actualiza toda la tienda (incluyendo sellers)
        if ($row['role'] === 'user') {
            $store_id = intval($row['store_id']);
            $conexion->query("UPDATE users SET status = '$new_status' WHERE store_id = $store_id");
        } else {
            // Si no es "user", solo afecta al usuario individual
            $conexion->query("UPDATE users SET status = '$new_status' WHERE id = $uid");
        }
    }

    header("Location: dashboard.php");
    exit;
}


    // Cargar todos los usuarios
    $usuarios = $conexion->query("SELECT u.id, u.username, u.store_id, u.status,u.role, s.name AS store_name 
                                  FROM users u 
                                  LEFT JOIN stores s ON u.store_id = s.id
                                  WHERE u.role IN ('user', 'seller') 
                                  ORDER BY u.id DESC");
}

// Crear nuevo seller
if (isset($_POST['nuevo_seller'])) {
    $nombre_seller = trim($_POST['nombre_seller']);
    $tienda_id = intval($_POST['tienda_id']);

    if (!empty($nombre_seller) && $tienda_id > 0) {
        $password_hash = null;
        $role_seller = "seller";
        $status = "active";

        $stmt = $conexion->prepare("INSERT INTO users (username, password_hash, role, store_id, status) VALUES (?,?,?,?,?)");
        $stmt->bind_param("sssis", $nombre_seller, $password_hash, $role_seller, $tienda_id, $status);
        $stmt->execute();
        $stmt->close();

        // 🔄 Recargar dashboard para refrescar la tabla
        header("Location: dashboard.php");
        exit;
    }
}

// ================== CAMBIAR MODO DE INVENTARIO ==================
if (isset($_POST['cambiar_modo']) && $role === 'user') {

    $nuevo_modo = $_POST['modo'];

    if ($nuevo_modo === 'simple' || $nuevo_modo === 'controlado') {

        $stmt = $conexion->prepare("UPDATE stores SET inventory_mode = ? WHERE id = ?");
        $stmt->bind_param("si", $nuevo_modo, $store_id);
        $stmt->execute();
        $stmt->close();

        // actualizar sesión inmediatamente
        $_SESSION['inventory_mode'] = $nuevo_modo;
    }

    header("Location: dashboard.php");
    exit;
}

// ================== FUNCIONALIDAD USUARIOS TIENDA ==================
if ($role === 'user' || $role === 'seller') {
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
// Aviso de insumos bajos en stock
$insumos_bajos = $conexion->query("
    SELECT name, quantity, unit, limite
    FROM inventory
    WHERE store_id = $store_id AND quantity < limite
");

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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - Stocky</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/png" href="img/favicon.png">
<link rel="shortcut icon" href="img/favicon.png">
</head>
<body>
<?php include 'navbar.php'; ?>
<main class="container my-5">
<div class="container mt-4">
  <h3 class="mb-4">Bienvenido, <?php echo ($role === 'super') ? "Administrador" : "Tienda $username"; ?></h3>

<!-- ================== AVISO DE INSUMOS INSUFICIENTES ================== -->
 <?php if (($role === 'user' || $role === 'seller') && $insumos_bajos && $insumos_bajos->num_rows > 0): ?>
  <div class="alert alert-info">
    <strong>⚠️ Atención:</strong> Los siguientes insumos están por agotarse:
    <ul class="mb-0">
      <?php while($i = $insumos_bajos->fetch_assoc()): ?>
        <li>
          <?php echo $i['name']; ?> → 
          <?php echo $i['quantity'] . " " . $i['unit']; ?> 
          (límite mínimo: <?php echo $i['limite']; ?>)
        </li>
      <?php endwhile; ?>
    </ul>
  </div>
<?php endif; ?>

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

      <h4>➕ Crear nuevo vendedor (Seller)</h4>
<form method="post" class="row g-3 mb-3">
  <input type="hidden" name="nuevo_seller" value="1">
  <div class="col-md-4">
    <input type="text" name="nombre_seller" class="form-control" placeholder="Nombre del vendedor" required>
  </div>
  <div class="col-md-4">
    <select name="tienda_id" class="form-control" required>
      <option value="">-- Selecciona tienda --</option>
      <?php
      $tiendas = $conexion->query("SELECT id, name FROM stores ORDER BY name ASC");
      while($t = $tiendas->fetch_assoc()):
      ?>
        <option value="<?php echo $t['id']; ?>"><?php echo $t['name']; ?></option>
      <?php endwhile; ?>
    </select>
  </div>
  <div class="col-md-4">
    <button class="btn btn-success w-100">Crear Seller</button>
  </div>
</form>

      <h4>👥 Usuarios registrados</h4>
      <input type="text" id="buscar" class="form-control mb-3" placeholder="Buscar tienda...">

      <table class="table table-bordered" id="tablaUsuarios">
        <thead>
          <tr><th>ID</th><th>Usuario</th><th>Rol</th><th>Tienda</th><th>Status</th><th>Acción</th></tr>

        </thead>
        <tbody>
          <?php while($row = $usuarios->fetch_assoc()): ?>
          <tr>
  <td><?php echo $row['id']; ?></td>
  <td><?php echo $row['username']; ?></td>
  <td><?php echo ucfirst($row['role']); ?></td>
  <td><?php echo $row['store_name']; ?></td>
  <td><?php echo $row['status']; ?></td>
  <td>
  <?php if ($row['role'] === 'user'): ?>
    <a href="dashboard.php?toggle_user=<?php echo $row['id']; ?>" 
       class="btn btn-sm <?php echo ($row['status'] === 'active') ? 'btn-danger' : 'btn-success'; ?>">
       <?php echo ($row['status'] === 'active') ? 'Bloquear' : 'Desbloquear'; ?>
    </a>
  <?php else: ?>
    <span class="text-muted">N/A</span>
  <?php endif; ?>
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
<div class="container py-4">
  <h2 class="mb-4">Panel de Administrador</h2>
 <!-- ================== CONFIGURAR MODO TIENDA ================== -->
<div class="card shadow-sm mb-4 border-0">
  <div class="card-body py-3">

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
      <h5 class="mb-0">⚙️ Configurar Modo Tienda</h5>
      <small class="text-muted">Puedes cambiarlo cuando lo necesites</small>
    </div>

    <form method="POST">
      <input type="hidden" name="cambiar_modo" value="1">

      <div class="row g-3">

        <!-- MODO CONTROLADO -->
        <div class="col-md-6">
          <button 
            name="modo" 
            value="controlado"
            class="card h-100 text-start border <?php echo ($_SESSION['inventory_mode'] === 'controlado') ? 'border-primary shadow-sm' : 'border-light'; ?>"
            style="background:white;"
          >
            <div class="card-body p-3">

              <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Modo Controlado</strong>
                <?php if ($_SESSION['inventory_mode'] === 'controlado'): ?>
                  <span class="badge bg-primary">Activo</span>
                <?php endif; ?>
              </div>

              <small class="text-muted">
                Descuenta automáticamente los insumos al realizar pedidos y calcula cuántos productos puedes preparar según tu inventario.
              </small>

            </div>
          </button>
        </div>

        <!-- MODO SIMPLE -->
        <div class="col-md-6">
          <button 
            name="modo" 
            value="simple"
            class="card h-100 text-start border <?php echo ($_SESSION['inventory_mode'] === 'simple') ? 'border-primary shadow-sm' : 'border-light'; ?>"
            style="background:white;"
          >
            <div class="card-body p-3">

              <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Modo Simple</strong>
                <?php if ($_SESSION['inventory_mode'] === 'simple'): ?>
                  <span class="badge bg-primary">Activo</span>
                <?php endif; ?>
              </div>

              <small class="text-muted">
                El inventario se usa solo para registrar costos de insumos. Los pedidos no afectan el stock ni las recetas.
              </small>

            </div>
          </button>
        </div>

      </div>

    </form>

  </div>
</div>

        </form>

      </div>

    </div>

  </div>
</div>
  <div class="row g-4">
    
    <!-- INVENTARIO -->
    <div class="col-md-6 col-lg-3">
      <div class="card text-center shadow-sm h-100">
        <div class="card-body">
          <div class="mb-3" style="font-size:2rem; color:#1f3b4d;">📦</div>
          <h5 class="card-title">Inventario</h5>
          <p class="card-text">Ingresa los insumos de tu tienda</p>
          <a href="inventario.php" class="btn btn-primary">Ir a Inventario</a>
        </div>
      </div>
    </div>

   





    <!-- PRODUCTOS -->
    <div class="col-md-6 col-lg-3">
      <div class="card text-center shadow-sm h-100">
        <div class="card-body">
          <div class="mb-3" style="font-size:2rem; color:#1f3b4d;">🍽️</div>
          <h5 class="card-title">Recetas</h5>
          <p class="card-text">Ingresa los ingredientes para los productos de tu menú</p>
          <a href="productos.php" class="btn btn-primary">Ir a Productos</a>
        </div>
      </div>
    </div>

  <!-- RECETAS -->
  <div class="col-md-6 col-lg-3">
    <div class="card text-center shadow-sm h-100">
      <div class="card-body">
        <div class="mb-3" style="font-size:2rem; color:#1f3b4d;">📖</div>
        <h5 class="card-title">Guía de Preparación</h5>
        <p class="card-text">Consulta las recetas disponibles de tu tienda.</p>
        <a href="recetas.php" class="btn btn-primary">Ir a Recetas</a>
      </div>
    </div>
  </div>


    <!-- PEDIDOS -->
    <div class="col-md-6 col-lg-3">
      <div class="card text-center shadow-sm h-100">
        <div class="card-body">
          <div class="mb-3" style="font-size:2rem; color:#1f3b4d;">🛒</div>
          <h5 class="card-title">Pedidos</h5>
          <p class="card-text">Realiza y registra los pedidos para los clientes.</p>
          <a href="pedidos.php" class="btn btn-primary">Ir a Pedidos</a>
        </div>
      </div>
    </div>

<!-- ENTREGADOS -->
    <div class="col-md-6 col-lg-3">
      <div class="card text-center shadow-sm h-100">
        <div class="card-body">
          <div class="mb-3" style="font-size:2rem; color:#1f3b4d;">✅</div>
          <h5 class="card-title">Estado de Pedidos</h5>
          <p class="card-text">Consulta los pedidos pendientes y entregados.</p>
          <a href="entregados.php" class="btn btn-primary">Ir a Estado de Pedidos</a>
        </div>
      </div>
    </div>

  
 <!-- GASTOS -->
<div class="col-md-6 col-lg-3">
  <div class="card text-center shadow-sm h-100">
    <div class="card-body">
      <div class="mb-3" style="font-size:2rem; color:#1f3b4d;">💸</div>
      <h5 class="card-title">Gastos</h5>
      <p class="card-text">Registra los gastos diarios como domicilios o pago a empleados.</p>
      <a href="gastos.php" class="btn btn-primary">Ir a Gastos</a>
    </div>
  </div>
</div>

    <!-- INFORMES -->
    <div class="col-md-6 col-lg-3">
      <div class="card text-center shadow-sm h-100">
        <div class="card-body">
          <div class="mb-3" style="font-size:2rem; color:#1f3b4d;">📊</div>
          <h5 class="card-title">Informes</h5>
          <p class="card-text">Descarga los informes mensuales y diarios para la contabilidad de tu negocio.</p>
          <a href="informes.php" class="btn btn-primary">Ir a Informes</a>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- VISTA DEL VENDEDOR (GUIA DE PREPARACION, PEDIDOS Y ESTADOS DE PEDIDOS) -->

<?php elseif ($role === 'seller'): ?>
<div class="container py-4">
  <h2 class="mb-4">Panel del Vendedor</h2>
  <div class="row g-4">

    <!-- RECETAS -->
  <div class="col-md-6 col-lg-4">
    <div class="card text-center shadow-sm h-100">
      <div class="card-body">
        <div class="mb-3" style="font-size:2rem; color:#1f3b4d;">📖</div>
        <h5 class="card-title">Recetas</h5>
        <p class="card-text">Consulta las recetas disponibles de tu tienda.</p>
        <a href="recetas.php" class="btn btn-primary">Ir a Recetas</a>
      </div>
    </div>
  </div>

    <!-- PEDIDOS -->
    <div class="col-md-6 col-lg-4">
      <div class="card text-center shadow-sm h-100">
        <div class="card-body">
          <div class="mb-3" style="font-size:2rem; color:#1f3b4d;">🛒</div>
          <h5 class="card-title">Pedidos</h5>
          <p class="card-text">Realiza y registra los pedidos de los clientes.</p>
          <a href="pedidos.php" class="btn btn-primary">Ir a Pedidos</a>
        </div>
      </div>
    </div>

    <!-- ESTADO DE PEDIDOS -->
    <div class="col-md-6 col-lg-4">
      <div class="card text-center shadow-sm h-100">
        <div class="card-body">
          <div class="mb-3" style="font-size:2rem; color:#1f3b4d;">✅</div>
          <h5 class="card-title">Estado de pedidos</h5>
          <p class="card-text">Consulta los pedidos pendientes y entregados.</p>
          <a href="entregados.php" class="btn btn-primary">Ir a Estado de Pedidos</a>
        </div>
      </div>
    </div>

  </div>
</div>
<?php endif; ?>
</div>
</main>



<?php include 'footer.php'; ?>


</body>
</html>
