<?php
session_start();
require 'verificar_sesion.php';

$user_id = $_SESSION['user_id'];
$store_id = $_SESSION['store_id'];
$modo = $_SESSION['inventory_mode'] ?? 'controlado';

if (!isset($_GET['id'])) {
    die("Pedido no válido");
}

$sale_id = intval($_GET['id']);

// 🔹 Obtener productos del pedido
$stmt = $conexion->prepare("
    SELECT si.product_id, si.qty, si.unit_price, p.name
    FROM sales_items si
    JOIN products p ON si.product_id = p.id
    WHERE si.sale_id = ? AND si.store_id = ?
");
$stmt->bind_param("ii", $sale_id, $store_id);
$stmt->execute();
$res = $stmt->get_result();

$pedidoData = [];
while ($row = $res->fetch_assoc()) {
    $pedidoData[] = [
        'id' => $row['product_id'],
        'name' => $row['name'],
        'price' => $row['unit_price'],
        'qty' => $row['qty']
    ];
}

// 🔹 Obtener productos disponibles
$productos = $conexion->prepare("
    SELECT id, name, price 
    FROM products 
    WHERE store_id = ?
");
$productos->bind_param("i", $store_id);
$productos->execute();
$productos_result = $productos->get_result();


// 🔴 GUARDAR CAMBIOS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pedido = json_decode($_POST['pedido'], true);

    if (!$pedido || !is_array($pedido)) {
        die("Pedido inválido");
    }

    // 🔹 Eliminar productos actuales
    $del = $conexion->prepare("DELETE FROM sales_items WHERE sale_id = ?");
    $del->bind_param("i", $sale_id);
    $del->execute();

    // 🔹 Insertar nuevos
    $total = 0;

    foreach ($pedido as $item) {
        $subtotal = $item['qty'] * $item['price'];
        $total += $subtotal;

        $ins = $conexion->prepare("
            INSERT INTO sales_items (sale_id, product_id, qty, store_id, unit_price)
            VALUES (?, ?, ?, ?, ?)
        ");
        $ins->bind_param("iiidi", $sale_id, $item['id'], $item['qty'], $store_id, $item['price']);
        $ins->execute();
    }

    // 🔹 Actualizar total y reiniciar estado
    $up = $conexion->prepare("
        UPDATE sales 
        SET total = ?, status = 'Active' 
        WHERE id = ? AND store_id = ?
    ");
    $up->bind_param("dii", $total, $sale_id, $store_id);
    $up->execute();

    header("Location: entregados.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Modificar Pedido</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>

<body>

<?php include 'navbar.php'; ?>

<main class="container my-5">

<h2>Modificar Pedido</h2>

<!-- 🔹 Lista de productos -->
<div class="table-responsive mb-4">
<table class="table table-bordered">
<thead>
<tr>
<th>Producto</th>
<th>Precio</th>
<th>Acción</th>
</tr>
</thead>
<tbody>

<?php while($p = $productos_result->fetch_assoc()): ?>
<tr data-id="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>">
<td><?= htmlspecialchars($p['name']) ?></td>
<td>$<?= number_format($p['price'],2) ?></td>
<td>
<button class="btn btn-sm btn-primary agregar-btn">Agregar</button>
</td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>

<!-- 🔹 Pedido actual -->
<h4>Pedido</h4>
<table class="table table-bordered" id="pedidoTable">
<thead>
<tr>
<th>Producto</th>
<th>Cantidad</th>
<th>Precio</th>
<th>Subtotal</th>
<th>Acción</th>
</tr>
</thead>
<tbody></tbody>
<tfoot>
<tr>
<th colspan="3">Total</th>
<th id="total">$0</th>
<th></th>
</tr>
</tfoot>
</table>

<button id="guardar" class="btn btn-success">Guardar cambios</button>

</main>

<form id="formPedido" method="POST" style="display:none;">
<input type="hidden" name="pedido" id="pedidoInput">
</form>

<script>
const pedidoInicial = <?= json_encode($pedidoData) ?>;

const pedido = {};

// 🔹 Cargar pedido inicial
pedidoInicial.forEach(item => {
    pedido[item.id] = item;
});

function render() {
    const tbody = document.querySelector('#pedidoTable tbody');
    tbody.innerHTML = '';
    let total = 0;

    Object.values(pedido).forEach(item => {

        const subtotal = item.qty * item.price;
        total += subtotal;

        const tr = document.createElement('tr');

        tr.innerHTML = `
            <td>${item.name}</td>
            <td>${item.qty}</td>
            <td>$${item.price}</td>
            <td>$${subtotal}</td>
            <td>
                <button class="plus btn btn-success btn-sm">+</button>
                <button class="minus btn btn-danger btn-sm">-</button>
            </td>
        `;

        tbody.appendChild(tr);

        tr.querySelector('.plus').onclick = () => {
            item.qty++;
            render();
        };

        tr.querySelector('.minus').onclick = () => {
            if (item.qty > 1) {
                item.qty--;
            } else {
                delete pedido[item.id];
            }
            render();
        };
    });

    document.getElementById('total').textContent = '$' + total.toFixed(2);
}

render();

// 🔹 Agregar producto
document.querySelectorAll('.agregar-btn').forEach(btn => {
    btn.addEventListener('click', () => {

        const tr = btn.closest('tr');
        const id = tr.dataset.id;
        const name = tr.cells[0].textContent;
        const price = parseFloat(tr.dataset.price);

        if (!pedido[id]) {
            pedido[id] = { id, name, price, qty: 1 };
        }

        render();
    });
});

// 🔹 Guardar
document.getElementById('guardar').addEventListener('click', () => {

    if (Object.keys(pedido).length === 0) {
        alert("Pedido vacío");
        return;
    }

    document.getElementById('pedidoInput').value = JSON.stringify(Object.values(pedido));
    document.getElementById('formPedido').submit();
});
</script>

</body>
</html>