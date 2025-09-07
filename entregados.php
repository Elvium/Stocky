<?php
session_start();
require 'conexion.php';
require 'verificar_sesion.php';

$user_id = $_SESSION['user_id'];
$store_id = $_SESSION['store_id'];

// Obtener pedidos de la tienda actual
$stmt = $conexion->prepare("
    SELECT id, total, comment, status, created_at
    FROM sales
    WHERE store_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $store_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Pedidos Entregados - Stocky</title>
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

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
     
        <th>Fecha</th>
        <th>Comentario</th>
        <th>Total</th>
        <th>Estado</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
     
          <td><?= $row['created_at'] ?></td>
          <td><?= htmlspecialchars($row['comment']) ?></td>
          <td>$<?= number_format($row['total'], 2) ?></td>
          <td>
            <?php if ($row['status'] === 'Active'): ?>
              <span class="badge bg-warning text-dark">PENDIENTE</span>
            <?php elseif ($row['status'] === 'Closed'): ?>
              <span class="badge bg-success">ENTREGADO</span>
            <?php else: ?>
              <span class="badge bg-secondary"><?= strtoupper($row['status']) ?></span>
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
