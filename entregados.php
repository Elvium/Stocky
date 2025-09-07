<?php
session_start();
require 'conexion.php';
require 'verificar_sesion.php';

$user_id = $_SESSION['user_id'];
$store_id = $_SESSION['store_id'];

// --- Cambiar estado a Entregado ---
if (isset($_GET['entregar'])) {
    $sale_id = intval($_GET['entregar']);
    $update = $conexion->prepare("UPDATE sales SET status = 'Closed' WHERE id = ? AND store_id = ?");
    $update->bind_param("ii", $sale_id, $store_id);
    $update->execute();
    header("Location: entregados.php");
    exit;
}

// --- Filtro por estado ---
$estadoFiltro = $_GET['estado'] ?? 'todos';
$sql = "
    SELECT id, client, total, comment, status, created_at
    FROM sales
    WHERE store_id = ?
";
if ($estadoFiltro === 'Active') {
    $sql .= " AND status = 'Active'";
} elseif ($estadoFiltro === 'Closed') {
    $sql .= " AND status = 'Closed'";
}
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
  <title>Pedidos - Stocky</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<main class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="m-0">Pedidos (Pendientes y Entregados)</h2>
    <a href="dashboard.php" class="btn btn-primary btn-sm">⬅ Volver al Inicio</a>
  </div>

  <!-- Filtro por estado -->
  <form method="get" class="mb-3 d-flex align-items-center gap-2">
    <label for="estado" class="form-label m-0">Filtrar por estado:</label>
    <select name="estado" id="estado" class="form-select form-select-sm" onchange="this.form.submit()">
      <option value="todos" <?= $estadoFiltro === 'todos' ? 'selected' : '' ?>>Todos</option>
      <option value="Active" <?= $estadoFiltro === 'Active' ? 'selected' : '' ?>>Pendientes</option>
      <option value="Closed" <?= $estadoFiltro === 'Closed' ? 'selected' : '' ?>>Entregados</option>
    </select>
  </form>

  <table class="table table-bordered table-striped align-middle">
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Cliente</th>
        <th>Comentario</th>
        <th class="text-end">Total</th>
        <th>Estado</th>
        <th>Acción</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['created_at'] ?></td>
          <td><?= htmlspecialchars($row['client']) ?></td>
          <td><?= htmlspecialchars($row['comment']) ?></td>
          <td class="text-end">$<?= number_format($row['total'], 2) ?></td>
          <td>
            <?php if ($row['status'] === 'Active'): ?>
              <span class="badge bg-warning text-dark">PENDIENTE</span>
            <?php elseif ($row['status'] === 'Closed'): ?>
              <span class="badge bg-success">ENTREGADO</span>
            <?php else: ?>
              <span class="badge bg-secondary"><?= strtoupper($row['status']) ?></span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($row['status'] === 'Active'): ?>
              <a href="?entregar=<?= $row['id'] ?>" class="btn btn-success btn-sm">Marcar como Entregado</a>
            <?php else: ?>
              <button class="btn btn-secondary btn-sm" disabled>✔ Entregado</button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</main>

<?php include 'footer.php'; ?>
</body>
</html>
