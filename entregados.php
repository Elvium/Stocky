<?php
session_start();

require 'verificar_sesion.php';

$user_id = $_SESSION['user_id'];
$store_id = $_SESSION['store_id'];

// --- Marcar Entregado (ajusta según estado actual: Active→Unpaid, Pending→Closed) ---
if (isset($_GET['entregar'])) {
    $sale_id = intval($_GET['entregar']);

    // obtener estado actual
    $q = $conexion->prepare("SELECT status FROM sales WHERE id = ? AND store_id = ?");
    $q->bind_param("ii", $sale_id, $store_id);
    $q->execute();
    $res = $q->get_result();

    if ($row = $res->fetch_assoc()) {
        $cur = $row['status'] ?: 'Active'; // si viene vacío o NULL, lo tratamos como Active
$new = $cur;


        // lógica de transición:
        // Active  -> Unpaid  (marcado como entregado, no pagado)
        // Pending -> Closed  (ya estaba pagado, ahora también entregado => cerrado)
        if ($cur === 'Active') {
            $new = 'Unpaid';
        } elseif ($cur === 'Pending') {
            $new = 'Closed';
        }

        if ($new !== $cur) {
            $u = $conexion->prepare("UPDATE sales SET status = ? WHERE id = ? AND store_id = ?");
            $u->bind_param("sii", $new, $sale_id, $store_id);
            $u->execute();
        }
    }

    header("Location: entregados.php");
    exit;
}


// --- Marcar Pagado (ajusta según estado actual: Active→Pending, Unpaid→Closed) ---
if (isset($_GET['pagar'])) {
    $sale_id = intval($_GET['pagar']);

    // obtener estado actual
    $q = $conexion->prepare("SELECT status FROM sales WHERE id = ? AND store_id = ?");
    $q->bind_param("ii", $sale_id, $store_id);
    $q->execute();
    $res = $q->get_result();

    if ($row = $res->fetch_assoc()) {
        $cur = $row['status'] ?: 'Active'; // si viene vacío o NULL, lo tratamos como Active
        $new = $cur;


        // lógica de transición:
        // Active  -> Pending (pagado pero no entregado)
        // Unpaid  -> Closed  (ya estaba entregado, ahora también pagado => cerrado)
        if ($cur === 'Active') {
            $new = 'Pending';
        } elseif ($cur === 'Unpaid') {
            $new = 'Closed';
        }

        if ($new !== $cur) {
            $u = $conexion->prepare("UPDATE sales SET status = ? WHERE id = ? AND store_id = ?");
            $u->bind_param("sii", $new, $sale_id, $store_id);
            $u->execute();
        }
    }

    header("Location: entregados.php");
    exit;
}


// --- Filtro por estado ---
// (Reemplaza aquí el bloque $sql antiguo por el siguiente)
$estadoFiltro = $_GET['estado'] ?? 'todos';

$sql = "
    SELECT s.id, s.client, s.total, s.comment, s.status, s.created_at,
           GROUP_CONCAT(CONCAT(si.qty, ' x ', COALESCE(p.name, '')) SEPARATOR '<br>') AS productos
    FROM sales s
    LEFT JOIN sales_items si ON s.id = si.sale_id
    LEFT JOIN products p ON si.product_id = p.id
    WHERE s.store_id = ?
      AND s.created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)
";


if ($estadoFiltro === 'Active') {
    $sql .= " AND s.status = 'Active'";
} elseif ($estadoFiltro === 'Pending') {
    $sql .= " AND s.status = 'Pending'";
} elseif ($estadoFiltro === 'Unpaid') {
    $sql .= " AND s.status = 'Unpaid'";
} elseif ($estadoFiltro === 'Closed') {
    $sql .= " AND s.status = 'Closed'";
}

$sql .= " GROUP BY s.id";
$sql .= " ORDER BY created_at DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $store_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pedidos - Stocky</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/png" href="img/favicon.png">
<link rel="shortcut icon" href="img/favicon.png">
</head>
<body>
<?php include 'navbar.php'; ?>

<main class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="m-0">Pedidos (Pendientes, Entregados y Pagados)</h2>
    <a href="dashboard.php" class="btn btn-dashboard btn-sm">⬅ Volver al Inicio</a>
  </div>

  <!-- Filtro por estado -->
  <form method="get" class="mb-3 d-flex align-items-center gap-2">
    <label for="estado" class="form-label m-0">Filtrar por estado:</label>
    <select name="estado" id="estado" class="form-select form-select-sm" onchange="this.form.submit()">
  <option value="todos" <?= $estadoFiltro === 'todos' ? 'selected' : '' ?>>Todos</option>
  <option value="Active" <?= $estadoFiltro === 'Active' ? 'selected' : '' ?>>No entregado / No pagado</option>
  <option value="Pending" <?= $estadoFiltro === 'Pending' ? 'selected' : '' ?>>Pagado (no entregado)</option>
  <option value="Unpaid" <?= $estadoFiltro === 'Unpaid' ? 'selected' : '' ?>>Entregado (no pagado)</option>
  <option value="Closed" <?= $estadoFiltro === 'Closed' ? 'selected' : '' ?>>Cerrado</option>
</select>

  </form>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead>
        <tr>
          <th>Fecha</th>
<th>Cliente</th>
<th>Productos</th>
<th>Comentario</th>

          <th class="text-end">Total</th>
          
          <th>Entregado</th>
          <th>Pagado</th>
          <th>Accion</th>
          
        </tr>
      </thead>
      <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
  <td><?= $row['created_at'] ?></td>
  <td><?= htmlspecialchars($row['client']) ?></td>
  <td><?= $row['productos'] ?? '—' ?></td>
  <td><?= htmlspecialchars($row['comment']) ?></td>
  <td class="text-end">$<?= number_format($row['total'], 2) ?></td>

  <?php
    $status = $row['status'] ?? 'Active';
    $delivered = in_array($status, ['Unpaid', 'Closed']);
    $paid = in_array($status, ['Pending', 'Closed']);
  ?>

  <!-- Estado Entregado -->
  <td class="text-center">
    <?php if ($delivered): ?>
      <span class="badge bg-success">ENTREGADO</span>
    <?php else: ?>
      <span class="badge bg-warning text-dark">NO ENTREGADO</span>
    <?php endif; ?>
  </td>

  <!-- Estado Pagado -->
  <td class="text-center">
    <?php if ($paid): ?>
      <span class="badge bg-success">PAGADO</span>
    <?php else: ?>
      <span class="badge bg-warning text-dark">NO PAGADO</span>
    <?php endif; ?>
  </td>

  <!-- Acción -->
  <td class="text-center">
    <div class="d-flex justify-content-center gap-2">
      <?php if (!$delivered): ?>
        <a href="?entregar=<?= $row['id'] ?>" class="btn btn-sm btn-success">Entregado</a>
      <?php else: ?>
        <button class="btn btn-sm btn-outline-success" disabled>Entregado</button>
      <?php endif; ?>

      <?php if (!$paid): ?>
        <a href="?pagar=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Pagado</a>
      <?php else: ?>
        <button class="btn btn-sm btn-outline-primary" disabled>Pagado</button>
      <?php endif; ?>
    </div>
  </td>
</tr>


          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</main>

<?php include 'footer.php'; ?>
</body>
</html>
<?php
require 'toomanyconexion.php'; 
?>