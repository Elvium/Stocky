<?php
session_start();
require 'conexion.php';
require 'verificar_sesion.php';
require_once 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;

$user_id = $_SESSION['user_id'];
$store_id = $_SESSION['store_id'];

// --- Informe del Día ---
if (isset($_POST['fecha'])) {
    $fecha = $_POST['fecha'];

    // 1. Total de ventas del día
    $stmt = $conexion->prepare("
        SELECT SUM(total) AS total_dia
        FROM sales
        WHERE store_id = ? AND DATE(created_at) = ?
    ");
    $stmt->bind_param("is", $store_id, $fecha);
    $stmt->execute();
    $resTotal = $stmt->get_result()->fetch_assoc();
    $total_dia = $resTotal['total_dia'] ?? 0;

    // 2. Pedidos del día
    $stmt = $conexion->prepare("
        SELECT client, total, created_at
        FROM sales
        WHERE store_id = ? AND DATE(created_at) = ?
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("is", $store_id, $fecha);
    $stmt->execute();
    $pedidos = $stmt->get_result();

    // 3. Productos vendidos
    $stmt = $conexion->prepare("
        SELECT p.name, SUM(si.qty) AS cantidad, SUM(si.qty * si.unit_price) AS subtotal
        FROM sales_items si
        INNER JOIN products p ON si.product_id = p.id
        INNER JOIN sales s ON si.sale_id = s.id
        WHERE s.store_id = ? AND DATE(s.created_at) = ?
        GROUP BY p.name
        ORDER BY cantidad DESC
    ");
    $stmt->bind_param("is", $store_id, $fecha);
    $stmt->execute();
    $productos = $stmt->get_result();

    // --- Nombre tienda ---
    $stmt = $conexion->prepare("SELECT name FROM stores WHERE id = ?");
    $stmt->bind_param("i", $store_id);
    $stmt->execute();
    $store = $stmt->get_result()->fetch_assoc();
    $store_name = $store['name'] ?? 'Mi Tienda';

    // --- Generar PDF ---
    $dompdf = new Dompdf();
    $html = '
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { color: #2c7a7b; text-align: center; margin-bottom: 5px; }
        h3 { color: #333; text-align: left; margin-top: 20px; }
        .fecha { text-align: right; font-size: 12px; color: #333; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: center; }
        th { background-color: #2c7a7b; color: white; }
        .total { font-weight: bold; font-size: 13px; text-align: right; padding: 8px; }
        .footer { margin-top: 30px; text-align: center; font-size: 11px; color: #777; }
    </style>

    <div class="fecha">Fecha seleccionada: ' . htmlspecialchars($fecha) . '</div>
    <h1>Informe de Ventas del Día</h1>
    <h3>' . htmlspecialchars($store_name) . '</h3>

    <h3>Pedidos del Día</h3>
    <table>
        <thead>
            <tr>
                <th>Hora</th>
                <th>Cliente</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>';
        while ($row = $pedidos->fetch_assoc()) {
            $hora = date("H:i", strtotime($row['created_at']));
            $html .= '<tr>
                <td>' . $hora . '</td>
                <td>' . htmlspecialchars($row['client']) . '</td>
                <td>$' . number_format($row['total'], 2) . '</td>
            </tr>';
        }
    $html .= '
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="total">TOTAL PEDIDOS DEL DÍA:</td>
                <td class="total">$' . number_format($total_dia, 2) . '</td>
            </tr>
        </tfoot>
    </table>

    <h3>Productos Vendidos</h3>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad Vendida</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>';
        $total_productos = 0;
        while ($row = $productos->fetch_assoc()) {
            $total_productos += $row['subtotal'];
            $html .= '<tr>
                <td>' . htmlspecialchars($row['name']) . '</td>
                <td>' . $row['cantidad'] . '</td>
                <td>$' . number_format($row['subtotal'], 2) . '</td>
            </tr>';
        }
    $html .= '
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="total">TOTAL PRODUCTOS VENDIDOS:</td>
                <td class="total">$' . number_format($total_productos, 2) . '</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">Generado por Stocky</div>
    ';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("Ventas_$fecha.pdf", ["Attachment" => true]);
    exit;
}

// --- Informe General ---
if (isset($_POST['fecha_inicio'], $_POST['fecha_fin'])) {
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    if ($fecha_inicio > $fecha_fin) {
    $error = "La fecha inicial no puede ser mayor que la fecha final.";
} else{


    // Ingresos: agrupados por fecha
    $stmt = $conexion->prepare("
        SELECT DATE(created_at) as fecha, SUM(total) as ingreso
        FROM sales
        WHERE store_id = ? AND DATE(created_at) BETWEEN ? AND ?
        GROUP BY DATE(created_at)
    ");
    $stmt->bind_param("iss", $store_id, $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $ingresos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Gastos: cada insumo insertado
    $stmt = $conexion->prepare("
        SELECT name, price as gasto, DATE(changed_at) as fecha
FROM inventory_logs
WHERE store_id = ? AND action = 'insert' AND DATE(changed_at) BETWEEN ? AND ?

    ");
    $stmt->bind_param("iss", $store_id, $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $gastos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // --- Preparar PDF ---
    $dompdf = new Dompdf();
    $html = '
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { color: #2c7a7b; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: center; }
        th { background-color: #2c7a7b; color: white; }
        .total { font-weight: bold; text-align: right; }
        .footer { margin-top: 30px; text-align: center; font-size: 11px; color: #777; }
    </style>

    <h1>Informe General</h1>
    <p style="text-align:center;">Periodo: ' . $fecha_inicio . ' a ' . $fecha_fin . '</p>

    <table>
        <thead>
            <tr>
                <th>Detalle</th>
                <th>Ingresos</th>
                <th>Gastos</th>
            </tr>
        </thead>
        <tbody>';

    $total_ingresos = 0;
    $total_gastos = 0;

  // Unir ingresos y gastos en un solo arreglo
$movimientos = [];

foreach ($ingresos as $ing) {
    $movimientos[] = [
        'fecha' => $ing['fecha'],
        'detalle' => 'VENTA - ' . $ing['fecha'],
        'ingreso' => $ing['ingreso'],
        'gasto' => 0
    ];
    $total_ingresos += $ing['ingreso'];
}

foreach ($gastos as $gas) {
    $movimientos[] = [
        'fecha' => $gas['fecha'],
        'detalle' => 'INSUMO - ' . $gas['name'] . ' (' . $gas['fecha'] . ')',
        'ingreso' => 0,
        'gasto' => $gas['gasto']
    ];
    $total_gastos += $gas['gasto'];
}

// Ordenar cronológicamente por fecha
usort($movimientos, function($a, $b) {
    return strcmp($a['fecha'], $b['fecha']);
});

// Pintar filas en orden
foreach ($movimientos as $mov) {
    $html .= '<tr>
        <td>' . htmlspecialchars($mov['detalle']) . '</td>
        <td>' . ($mov['ingreso'] > 0 ? '$' . number_format($mov['ingreso'], 2) : '-') . '</td>
        <td>' . ($mov['gasto'] > 0 ? '$' . number_format($mov['gasto'], 2) : '-') . '</td>
    </tr>';
}



    $html .= '
        </tbody>
        <tfoot>
            <tr>
                <td class="total">TOTAL:</td>
                <td class="total">$' . number_format($total_ingresos, 2) . '</td>
                <td class="total">$' . number_format($total_gastos, 2) . '</td>
            </tr>
            <tr>
                <td colspan="3" class="total">BALANCE: $' . number_format($total_ingresos - $total_gastos, 2) . '</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">Generado por Stocky</div>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("Informe_General_$fecha_inicio-$fecha_fin.pdf", ["Attachment" => true]);
    exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Informes - Stocky</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<main class="container my-5">
    <div class="text-center mb-4">
  <h1 class="fw-bold">Informes</h1>
</div>
  <div class="d-flex justify-content-between align-items-center mb-3">
    
    <a href="dashboard.php" class="btn btn-dashboard btn-sm">⬅ Volver al Inicio</a>
  </div>


  <?php if (!empty($error)): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= $error ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

  <!-- Informe del Día -->
  <section class="mb-5">
    <h4>Informe del Día</h4>
    <form method="post" class="card p-3 shadow-sm">
      <div class="mb-3">
        <label for="fecha" class="form-label">Seleccionar Fecha</label>
        <input type="date" name="fecha" id="fecha" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-success">Generar Informe PDF</button>
    </form>
  </section>

  <!-- Informe General -->
  <section>
    <h4>Informe General</h4>
    <form method="post" class="card p-3 shadow-sm">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="fecha_inicio" class="form-label">Fecha Inicial</label>
          <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
          <label for="fecha_fin" class="form-label">Fecha Final</label>
          <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Generar Informe General PDF</button>
    </form>
  </section>
</main>

<?php include 'footer.php'; ?>
</body>
</html>
