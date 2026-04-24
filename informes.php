<?php
session_start();

require 'verificar_sesion.php';
require_once 'dompdf/autoload.inc.php';
$conexion->set_charset("utf8mb4");
use Dompdf\Dompdf;

$user_id = $_SESSION['user_id'];
$store_id = $_SESSION['store_id'];

function generarInforme($conexion, $store_id, $fecha_inicio, $fecha_fin)
{
    // INGRESOS
    $stmt = $conexion->prepare("
        SELECT SUM(total) AS total
        FROM sales
        WHERE store_id = ? 
        AND DATE(created_at) BETWEEN ? AND ?
        AND status COLLATE utf8mb4_unicode_ci != 'Canceled'
    ");
    $stmt->bind_param("iss", $store_id, $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $total_ingresos = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

    // MÉTODOS DE PAGO
    $stmt = $conexion->prepare("
        SELECT payment_method, SUM(total) AS total
        FROM sales
        WHERE store_id = ?
        AND DATE(created_at) BETWEEN ? AND ?
        AND status COLLATE utf8mb4_unicode_ci != 'Canceled'
        AND payment_method COLLATE utf8mb4_unicode_ci != 'Cancelado'
        GROUP BY payment_method
    ");
    $stmt->bind_param("iss", $store_id, $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $metodos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // PRODUCTOS
    $stmt = $conexion->prepare("
        SELECT p.name, SUM(si.qty) AS cantidad, SUM(si.qty * si.unit_price) AS subtotal
        FROM sales_items si
        INNER JOIN products p ON si.product_id = p.id
        INNER JOIN sales s ON si.sale_id = s.id
        WHERE s.store_id = ?
        AND DATE(s.created_at) BETWEEN ? AND ?
        AND s.status COLLATE utf8mb4_unicode_ci != 'Canceled'
        GROUP BY p.name
    ");
    $stmt->bind_param("iss", $store_id, $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // GASTOS
    $stmt = $conexion->prepare("
        SELECT name, price, action
        FROM inventory_logs
        WHERE store_id = ?
        AND DATE(changed_at) BETWEEN ? AND ?
        AND action COLLATE utf8mb4_unicode_ci IN ('insert','update','gasto')
    ");
    $stmt->bind_param("iss", $store_id, $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $gastos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $total_gastos = 0;
    foreach ($gastos as $g) {
        $total_gastos += $g['price'];
    }

    // CANCELADOS
    $stmt = $conexion->prepare("
        SELECT client, total, comment
        FROM sales
        WHERE store_id = ?
        AND DATE(created_at) BETWEEN ? AND ?
        AND status = 'Canceled'
    ");
    $stmt->bind_param("iss", $store_id, $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $cancelados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // TIENDA
    $stmt = $conexion->prepare("SELECT name FROM stores WHERE id = ?");
    $stmt->bind_param("i", $store_id);
    $stmt->execute();
    $store = $stmt->get_result()->fetch_assoc();
    $store_name = $store['name'] ?? 'Mi Tienda';

    // PDF
    $dompdf = new Dompdf();

    $html = '
    <style>
        body { font-family: DejaVu Sans; font-size: 12px; }
        h1 { text-align:center; color:#2c7a7b; }
        h3 { margin-top:20px; }
        table { width:100%; border-collapse: collapse; margin-top:10px; }
        th, td { border:1px solid #ccc; padding:6px; text-align:center; }
        th { background:#2c7a7b; color:white; }
        .total { font-weight:bold; background:#f2f2f2; }
    </style>

    <h1>Informe</h1>
    <p><strong>Tienda:</strong> ' . $store_name . '</p>
    <p><strong>Periodo:</strong> ' . $fecha_inicio . ' a ' . $fecha_fin . '</p>

    <h3>Ingresos vs Gastos</h3>
    <table>
    <tr><th>Detalle</th><th>Valor</th></tr>
    <tr><td>Ingresos</td><td>$' . number_format($total_ingresos, 2) . '</td></tr>
    <tr><td>Gastos</td><td>$' . number_format($total_gastos, 2) . '</td></tr>
    <tr class="total"><td>Balance</td><td>$' . number_format($total_ingresos - $total_gastos, 2) . '</td></tr>
    </table>

    <h3>Métodos de Pago</h3>
    <table>
    <tr><th>Método</th><th>Total</th></tr>';

    foreach ($metodos as $m) {
        $html .= '<tr>
        <td>' . $m['payment_method'] . '</td>
        <td>$' . number_format($m['total'], 2) . '</td>
        </tr>';
    }

    $html .= '<tr class="total"><td>Total</td><td>$' . number_format($total_ingresos, 2) . '</td></tr>';
    $html .= '</table>';

    // PRODUCTOS
    $html .= '<h3>Productos Vendidos</h3>
    <table>
    <tr><th>Producto</th><th>Cantidad</th><th>Subtotal</th></tr>';

    $total_productos = 0;
    foreach ($productos as $p) {
        $total_productos += $p['subtotal'];
        $html .= '<tr>
        <td>' . $p['name'] . '</td>
        <td>' . $p['cantidad'] . '</td>
        <td>$' . number_format($p['subtotal'], 2) . '</td>
        </tr>';
    }

    $html .= '<tr class="total"><td colspan="2">Total</td><td>$' . number_format($total_productos, 2) . '</td></tr>';
    $html .= '</table>';

    // GASTOS
    $html .= '<h3>Gastos</h3>
    <table>
    <tr><th>Detalle</th><th>Valor</th></tr>';

    foreach ($gastos as $g) {
        $tipo = ($g['action'] == 'gasto') ? 'GASTO' : 'INSUMO';
        $html .= '<tr>
        <td>' . $tipo . ' - ' . $g['name'] . '</td>
        <td>$' . number_format($g['price'], 2) . '</td>
        </tr>';
    }

    $html .= '<tr class="total"><td>Total</td><td>$' . number_format($total_gastos, 2) . '</td></tr>';
    $html .= '</table>';

    // CANCELADOS
    $html .= '<h3>Pedidos Cancelados</h3>
    <table>
    <tr><th>Cliente</th><th>Total</th><th>Comentario</th></tr>';

    foreach ($cancelados as $c) {
        $html .= '<tr>
        <td>' . $c['client'] . '</td>
        <td>$' . number_format($c['total'], 2) . '</td>
        <td>' . $c['comment'] . '</td>
        </tr>';
    }

    $html .= '</table>';

    $html .= '<p style="margin-top:20px;text-align:center;color:#777;">Generado por Stocky</p>';

    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream("Informe.pdf", ["Attachment" => true]);
    exit;
}

// INFORME DÍA
if (isset($_POST['fecha'])) {
    generarInforme($conexion, $store_id, $_POST['fecha'], $_POST['fecha']);
}

// INFORME ENTRE FECHAS
if (isset($_POST['fecha_inicio']) && isset($_POST['fecha_fin'])) {
    generarInforme($conexion, $store_id, $_POST['fecha_inicio'], $_POST['fecha_fin']);
}


// ===== DASHBOARD DEL MES =====
$mes_actual = date('Y-m');

// INGRESOS DEL MES
$stmt = $conexion->prepare("
    SELECT DATE(created_at) as fecha, SUM(total) as total
    FROM sales
    WHERE store_id = ?
    AND DATE_FORMAT(created_at, '%Y-%m') = ?
    AND status COLLATE utf8mb4_unicode_ci != 'Canceled'
    GROUP BY DATE(created_at)
");
$stmt->bind_param("is", $store_id, $mes_actual);
$stmt->execute();
$result = $stmt->get_result();

$ventas_dias = [];
$fechas = [];
while ($row = $result->fetch_assoc()) {
    $fechas[] = $row['fecha'];
    $ventas_dias[] = $row['total'];
}

// MÉTODOS DE PAGO (MES)
$stmt = $conexion->prepare("
    SELECT payment_method, SUM(total) as total
    FROM sales
    WHERE store_id = ?
    AND DATE_FORMAT(created_at, '%Y-%m') = ?
    AND status COLLATE utf8mb4_unicode_ci != 'Canceled'
    AND payment_method COLLATE utf8mb4_unicode_ci != 'Cancelado'
    GROUP BY payment_method
");
$stmt->bind_param("is", $store_id, $mes_actual);
$stmt->execute();
$metodos_mes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// TOP PRODUCTOS
$stmt = $conexion->prepare("
    SELECT p.name, SUM(si.qty) as cantidad
    FROM sales_items si
    INNER JOIN products p ON si.product_id = p.id
    INNER JOIN sales s ON si.sale_id = s.id
    WHERE s.store_id = ?
    AND DATE_FORMAT(s.created_at, '%Y-%m') = ?
    AND s.status COLLATE utf8mb4_unicode_ci != 'Canceled'
    GROUP BY p.name
    ORDER BY cantidad DESC
    LIMIT 5
");
$stmt->bind_param("is", $store_id, $mes_actual);
$stmt->execute();
$top_productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Informes - Stocky</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        <!-- DASHBOARD -->
        <section class="mb-5">
            <h4 class="mb-3">Resumen del Mes</h4>

            <div class="row">
                <!-- Gráfico ventas -->
                <div class="col-12 col-lg-8">
                    <div class="card p-3 shadow-sm mb-3">
                        <h6>Ventas por día</h6>
                        <canvas id="ventasChart" style="height:300px;"></canvas>
                    </div>
                </div>

                <!-- Métodos de pago -->
                <div class="col-12 col-lg-4">
                    <div class="card p-3 shadow-sm mb-3">
                        <h6>Métodos de Pago</h6>
                        <canvas id="metodosChart" style="height:300px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top productos -->
            <div class="card p-3 shadow-sm mt-4">
                <h6>Top Productos del Mes</h6>
                <div class="table-responsive">
                    <table class="table text-center align-middle">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_productos as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td><?= $p['cantidad'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
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

    <script>
        const ventasChart = new Chart(document.getElementById('ventasChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($fechas) ?>,
                datasets: [{
                    label: 'Ventas',
                    data: <?= json_encode($ventas_dias) ?>
                }]
            }
        });

        const metodosChart = new Chart(document.getElementById('metodosChart'), {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($metodos_mes, 'payment_method')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($metodos_mes, 'total')) ?>
                }]
            }
        });
    </script>
</body>

</html>