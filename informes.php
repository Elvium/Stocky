<?php
session_start();

require 'verificar_sesion.php';
require_once 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;

$user_id = $_SESSION['user_id'];
$store_id = $_SESSION['store_id'];

// --- INFORME DEL DÍA ---
if (isset($_POST['fecha'])) {
    $fecha = $_POST['fecha'];

    // TOTAL INGRESOS
    $stmt = $conexion->prepare("
        SELECT SUM(total) AS total_dia
        FROM sales
        WHERE store_id = ? AND DATE(created_at) = ? AND status != 'Cancelado'
    ");
    $stmt->bind_param("is", $store_id, $fecha);
    $stmt->execute();
    $total_dia = $stmt->get_result()->fetch_assoc()['total_dia'] ?? 0;

    // PEDIDOS
    $stmt = $conexion->prepare("
        SELECT client, total, created_at, payment_method
        FROM sales
        WHERE store_id = ? AND DATE(created_at) = ? AND status != 'Cancelado'
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("is", $store_id, $fecha);
    $stmt->execute();
    $pedidos = $stmt->get_result();

    // MÉTODOS DE PAGO
    $stmt = $conexion->prepare("
        SELECT payment_method, SUM(total) AS total
        FROM sales
        WHERE store_id = ? AND DATE(created_at) = ? AND status != 'Cancelado'
        GROUP BY payment_method
    ");
    $stmt->bind_param("is", $store_id, $fecha);
    $stmt->execute();
    $metodos = $stmt->get_result();

    // PRODUCTOS
    $stmt = $conexion->prepare("
        SELECT p.name, SUM(si.qty) AS cantidad, SUM(si.qty * si.unit_price) AS subtotal
        FROM sales_items si
        INNER JOIN products p ON si.product_id = p.id
        INNER JOIN sales s ON si.sale_id = s.id
        WHERE s.store_id = ? AND DATE(s.created_at) = ? AND s.status != 'Cancelado'
        GROUP BY p.name
        ORDER BY cantidad DESC
    ");
    $stmt->bind_param("is", $store_id, $fecha);
    $stmt->execute();
    $productos = $stmt->get_result();

    // GASTOS
    $stmt = $conexion->prepare("
        SELECT name, price, action
        FROM inventory_logs
        WHERE store_id = ? 
        AND DATE(changed_at) = ? 
        AND action IN ('insert','update','gasto')
    ");
    $stmt->bind_param("is", $store_id, $fecha);
    $stmt->execute();
    $gastos = $stmt->get_result();

    $total_gastos = 0;

    // CANCELADOS
    $stmt = $conexion->prepare("
        SELECT client, total, comment
        FROM sales
        WHERE store_id = ? AND DATE(created_at) = ? AND status = 'Cancelado'
    ");
    $stmt->bind_param("is", $store_id, $fecha);
    $stmt->execute();
    $cancelados = $stmt->get_result();

    // NOMBRE TIENDA
    $stmt = $conexion->prepare("SELECT name FROM stores WHERE id = ?");
    $stmt->bind_param("i", $store_id);
    $stmt->execute();
    $store = $stmt->get_result()->fetch_assoc();
    $store_name = $store['name'] ?? 'Mi Tienda';

    // PDF
    $dompdf = new Dompdf();
    $html = '
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    h1 { color: #2c7a7b; text-align: center; }
    h3 { margin-top: 20px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ccc; padding: 6px; text-align: center; }
    th { background-color: #2c7a7b; color: white; }
    .total { font-weight: bold; }
</style>

<h1>Informe Diario</h1>
<p><strong>Tienda:</strong> ' . htmlspecialchars($store_name) . '</p>
<p><strong>Fecha:</strong> ' . htmlspecialchars($fecha) . '</p>

<h3>Ingresos vs Gastos</h3>
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

    while ($m = $metodos->fetch_assoc()) {
        $total_ingresos += $m['total'];

        $html .= '<tr>
        <td>VENTAS - ' . htmlspecialchars($m['payment_method']) . '</td>
        <td>$' . number_format($m['total'], 2) . '</td>
        <td>-</td>
    </tr>';
    }
    while ($g = $gastos->fetch_assoc()) {
        $total_gastos += $g['price'];

        $tipo = ($g['action'] === 'gasto') ? 'GASTO' : 'INSUMO';

        $html .= '<tr>
        <td>' . $tipo . ' - ' . htmlspecialchars($g['name']) . '</td>
        <td>-</td>
        <td>$' . number_format($g['price'], 2) . '</td>
    </tr>';
    }
    while ($row = $pedidos->fetch_assoc()) {
        $hora = date("H:i", strtotime($row['created_at']));
        $html .= '<tr>
                <td>' . $hora . '</td>
                <td>' . htmlspecialchars($row['client']) . '</td>
                <td>' . htmlspecialchars($row['payment_method']) . '</td>
                <td>$' . number_format($row['total'], 2) . '</td>
            </tr>';
    }
    $html .= '</tbody></table>';

    // MÉTODOS DE PAGO
    $html .= '<h3>Totales por Método de Pago</h3><table>
        <thead><tr><th>Método</th><th>Total</th></tr></thead><tbody>';
    while ($m = $metodos->fetch_assoc()) {
        $html .= '<tr>
            <td>' . htmlspecialchars($m['payment_method']) . '</td>
            <td>$' . number_format($m['total'], 2) . '</td>
        </tr>';
    }
    $html .= '</tbody></table>';

    // PRODUCTOS
    $html .= '<h3>Productos Vendidos</h3><table>
        <thead><tr><th>Producto</th><th>Cantidad</th><th>Subtotal</th></tr></thead><tbody>';
    $total_productos = 0;
    while ($row = $productos->fetch_assoc()) {
        $total_productos += $row['subtotal'];
        $html .= '<tr>
            <td>' . htmlspecialchars($row['name']) . '</td>
            <td>' . $row['cantidad'] . '</td>
            <td>$' . number_format($row['subtotal'], 2) . '</td>
        </tr>';
    }
    $html .= '</tbody></table>';

    // GASTOS
    $html .= '<h3>Gastos</h3><table>
        <thead><tr><th>Detalle</th><th>Valor</th></tr></thead><tbody>';
    while ($g = $gastos->fetch_assoc()) {
        $total_gastos += $g['price'];
        $tipo = ($g['action'] === 'gasto') ? 'GASTO' : 'INSUMO';
        $html .= '<tr>
            <td>' . $tipo . ' - ' . htmlspecialchars($g['name']) . '</td>
            <td>$' . number_format($g['price'], 2) . '</td>
        </tr>';
    }
    $html .= '</tbody></table>';

    // CANCELADOS
    $html .= '<h3>Pedidos Cancelados</h3>
<table>
    <thead>
        <tr>
            <th>Cliente</th>
            <th>Total</th>
            <th>Comentario</th>
        </tr>
    </thead>
    <tbody>';

    while ($c = $cancelados->fetch_assoc()) {
        $html .= '<tr>
        <td>' . htmlspecialchars($c['client']) . '</td>
        <td>$' . number_format($c['total'], 2) . '</td>
        <td>' . htmlspecialchars($c['comment']) . '</td>
    </tr>';
    }

    $html .= '</tbody></table>

<div style="margin-top:20px;text-align:center;font-size:11px;color:#777;">
Generado por Stocky
</div>';

    $html .= '
    <p class="total">Ingresos: $' . number_format($total_dia, 2) . '</p>
    <p class="total">Gastos: $' . number_format($total_gastos, 2) . '</p>
    <p class="total">Balance: $' . number_format($total_dia - $total_gastos, 2) . '</p>

    <div class="footer">Generado por Stocky</div>';

    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream("Ventas_$fecha.pdf", ["Attachment" => true]);
    exit;
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