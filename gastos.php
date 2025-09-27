<?php
session_start();

require 'verificar_sesion.php';

if ($_SESSION['role'] !== 'user') {
    header("Location: dashboard.php");
    exit;
}

$store_id = $_SESSION['store_id'];

// Guardar gasto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = ($_POST['name'] === 'other') ? trim($_POST['nameInput']) : trim($_POST['name']);
    $price = floatval($_POST['price']);

    if ($name !== '' && $price > 0) {
        $stmt = $conexion->prepare("INSERT INTO inventory_logs (store_id, action, name, price) VALUES (?, 'gasto', ?, ?)");
        $stmt->bind_param("isd", $store_id, $name, $price);
        $stmt->execute();
        $stmt->close();
    }
}

// Obtener nombres previos de gastos
$names_result = $conexion->query("SELECT DISTINCT name FROM inventory_logs WHERE action='gasto' AND store_id = $store_id");

// Obtener últimos gastos
$logs_result = $conexion->query("SELECT name, price, changed_at 
                             FROM inventory_logs 
                             WHERE action='gasto' AND store_id = $store_id 
                             ORDER BY changed_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gastos - Stocky</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <script>
    function toggleNameInput(select) {
      const input = document.getElementById("nameInput");
      if (select.value === "other") {
        input.classList.remove("d-none");
        input.required = true;
      } else {
        input.classList.add("d-none");
        input.required = false;
      }
    }
  </script>
</head>
<body>
<?php include 'navbar.php'; ?>
<main class="container my-5">
  <div class="container-section">
    <h2 class="section-title">💸 Registro de Gastos</h2>
    <form method="POST" action="" class="row g-3">
      <!-- Concepto del gasto -->
      <div class="col-md-6">
        <label class="form-label">Concepto del gasto</label>
        <select class="form-select" name="name" id="nameSelect" onchange="toggleNameInput(this)" required>
          <option value="">-- Seleccione --</option>
          <?php while ($n = $names_result->fetch_assoc()): ?>
              <option value="<?= htmlspecialchars($n['name']) ?>"><?= htmlspecialchars($n['name']) ?></option>
          <?php endwhile; ?>
          <option value="other">Otro...</option>
        </select>
        <input type="text" name="nameInput" id="nameInput" class="form-control mt-2 d-none" placeholder="Nuevo concepto de gasto">
      </div>

      <!-- Costo -->
      <div class="col-md-6">
        <label for="price" class="form-label">Costo</label>
        <input type="number" step="0.01" class="form-control" name="price" id="price" required>
      </div>

      <!-- Botones -->
      <div class="col-12">
        <button type="submit" class="btn btn-primary">Registrar Gasto</button>
        <a href="dashboard.php" class="btn btn-dashboard">Volver al Inicio</a>
      </div>
    </form>
  </div>

  <!-- Últimos gastos -->
  <div class="container-section">
    <h2 class="section-title">📋 Últimos Gastos Registrados</h2>
    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle">
        <thead>
          <tr>
            <th>Concepto</th>
            <th>Costo</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($logs_result->num_rows > 0): ?>
            <?php while ($row = $logs_result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td>$<?= number_format($row['price'], 2) ?></td>
                <td><?= $row['changed_at'] ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="3">No se han registrado gastos aún.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
<?php include 'footer.php'; ?>
</body>
</html>
