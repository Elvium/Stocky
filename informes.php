<?php
session_start();
require 'conexion.php';
require 'verificar_sesion.php';
require_once 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;

$user_id = $_SESSION['user_id'];
$store_id = $_SESSION['store_id'];

// --- Cuando el usuario selecciona fecha ---
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

    // 2. Pedidos del día (solo hora, cliente, total)
    $stmt = $conexion->prepare("
        SELECT client, total, created_at
        FROM sales
        WHERE store_id = ? AND DATE(created_at) = ?
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("is", $store_id, $fecha);
    $stmt->execute();
    $pedidos = $stmt->get_result();

    // 3. Productos vendidos ese día
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

    // --- Obtener nombre de la tienda ---
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Informe del Día - Stocky</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<main class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="m-0">Informe del Día</h2>
    <a href="dashboard.php" class="btn btn-primary btn-sm">⬅ Volver al Inicio</a>
  </div>

  <form method="post" class="card p-3 shadow-sm">
    <div class="mb-3">
      <label for="fecha" class="form-label">Seleccionar Fecha</label>
      <input type="date" name="fecha" id="fecha" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-success">Generar Informe PDF</button>
  </form>
</main>

<?php include 'footer.php'; ?>
</body>
</html>
