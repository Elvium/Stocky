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

// 🔹 Obtener estado actual del pedido
$check = $conexion->prepare("SELECT status, comment FROM sales WHERE id = ? AND store_id = ?");
$check->bind_param("ii", $sale_id, $store_id);
$check->execute();
$resCheck = $check->get_result();
$pedidoInfo = $resCheck->fetch_assoc();

if (!$pedidoInfo) {
    die("Pedido no encontrado");
}

// 🔒 BLOQUEAR SI ESTÁ CANCELADO
if ($pedidoInfo['status'] === 'Canceled') {
    echo "<div class='container mt-5'>
            <div class='alert alert-danger text-center'>
                ❌ Este pedido está cancelado y no puede ser editado.
            </div>
            <div class='text-center'>
                <a href='entregados.php' class='btn btn-secondary'>Volver</a>
            </div>
          </div>";
    exit;
}

// 🔴 GUARDAR CAMBIOS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $isCancel = isset($_POST['cancelar']);
    $comment = trim($_POST['comment'] ?? '');

    // 🔹 Mantener estado original si no cancela
    $status = $isCancel ? 'Canceled' : $pedidoInfo['status'];

    $pedido = json_decode($_POST['pedido'], true);

    if (!$pedido || !is_array($pedido)) {
        die("Pedido inválido");
    }

    // 🔴 SOLO editar productos si NO está cancelando
    if (!$isCancel) {

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

    } else {
        // 🔹 Si cancela, mantener total actual
        $totalQuery = $conexion->prepare("SELECT total FROM sales WHERE id = ? AND store_id = ?");
        $totalQuery->bind_param("ii", $sale_id, $store_id);
        $totalQuery->execute();
        $resTotal = $totalQuery->get_result()->fetch_assoc();
        $total = $resTotal['total'];
    }

   // 🔹 Definir método de pago si se cancela
$payment_method = $isCancel ? 'Cancelado' : null;

// 🔹 Actualizar pedido
if ($isCancel) {

    $up = $conexion->prepare("
        UPDATE sales 
        SET total = ?, status = ?, payment_method = ?, comment = ?
        WHERE id = ? AND store_id = ?
    ");
    $up->bind_param("dssssi", $total, $status, $payment_method, $comment, $sale_id, $store_id);

} else {

    $up = $conexion->prepare("
        UPDATE sales 
        SET total = ?, status = ?, comment = ?
        WHERE id = ? AND store_id = ?
    ");
    $up->bind_param("dssii", $total, $status, $comment, $sale_id, $store_id);
}

$up->execute();

    header("Location: entregados.php");
    exit;
}

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

    <div class="mb-3">
        <label class="form-label">Motivo (opcional)</label>
        <textarea id="comentario" class="form-control"
            placeholder="Ej: Cliente canceló, error en pedido..."><?= htmlspecialchars($pedidoInfo['comment'] ?? '') ?></textarea>
    </div>

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

                <?php while ($p = $productos_result->fetch_assoc()): ?>
                    <tr data-id="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>">
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td>$<?= number_format($p['price'], 2) ?></td>
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

    <div class="d-flex gap-2">
        <button id="guardar" class="btn btn-success">Guardar cambios</button>
        <button id="cancelarPedido" class="btn btn-danger">Cancelar pedido</button>
    </div>

</main>

<form id="formPedido" method="POST" style="display:none;">
    <input type="hidden" name="pedido" id="pedidoInput">
    <input type="hidden" name="comment" id="commentInput">
</form>

<script>
const pedidoInicial = <?= json_encode($pedidoData) ?>;
const pedido = {};

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
    document.getElementById('commentInput').value = document.getElementById('comentario').value;

    document.getElementById('formPedido').submit();
});

// 🔴 Cancelar pedido
document.getElementById('cancelarPedido').addEventListener('click', () => {

    if (!confirm("¿Seguro que quieres cancelar este pedido?")) return;

    const comentario = document.getElementById('comentario').value;

    document.getElementById('pedidoInput').value = JSON.stringify(Object.values(pedido));
    document.getElementById('commentInput').value = comentario;

    const form = document.getElementById('formPedido');

    const cancelInput = document.createElement('input');
    cancelInput.type = 'hidden';
    cancelInput.name = 'cancelar';
    cancelInput.value = '1';

    form.appendChild(cancelInput);

    form.submit();
});
</script>

</body>
</html>