<?php
session_start();
require 'verificar_sesion.php'; 


$user_id = $_SESSION['user_id'];
$store_id = $_SESSION['store_id'];

// --- Obtener productos con stock disponible ---
$productos_stmt = $conexion->prepare("
    SELECT p.id, p.name, p.price,
        (SELECT MIN(FLOOR(i.quantity / pm.qty_needed))
         FROM product_materials pm
         JOIN inventory i ON pm.inventory_id = i.id
         WHERE pm.product_id = p.id
           AND i.store_id = ? ) AS stock_disponible
    FROM products p
    WHERE p.store_id = ?
");
$productos_stmt->bind_param("ii", $store_id, $store_id);
$productos_stmt->execute();
$productos_result = $productos_stmt->get_result();

// --- Procesar pedido ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pedido'])) {
    $pedido = json_decode($_POST['pedido'], true); 
    $comment = $_POST['comment'] ?? '';
    $client  = $_POST['client'] ?? ''; // 🔹 nuevo campo cliente

    if (!$pedido || !is_array($pedido)) {
        echo "Pedido vacío o mal formado";
        exit;
    }

    // Calcular total
    $total = 0;
    foreach ($pedido as $item) {
        $total += $item['qty'] * $item['price'];
    }

    // Insertar en sales con cliente
    $stmt = $conexion->prepare("INSERT INTO sales (user_id, store_id, client, comment, total) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iissd", $user_id, $store_id, $client, $comment, $total);
    $stmt->execute();
    $sale_id = $stmt->insert_id;

    // Insertar productos en sales_items
    $item_stmt = $conexion->prepare("INSERT INTO sales_items (sale_id, product_id, qty, store_id, unit_price) VALUES (?, ?, ?, ?, ?)");
    foreach ($pedido as $item) {
        $unit_price = $item['price'];
        $item_stmt->bind_param("iiidi", $sale_id, $item['id'], $item['qty'], $store_id, $unit_price);
        $item_stmt->execute();
    }

    // Marcar variable para saltar trigger
    $conexion->query("SET @SKIP_INVENTORY_LOG = 1");

    // --- Descontar insumos ---
    foreach ($pedido as $item) {
        $product_id = $item['id'];
        $qty_ordered = $item['qty'];

        $insumos_stmt = $conexion->prepare("
            SELECT inventory_id, qty_needed 
            FROM product_materials 
            WHERE product_id = ?
        ");
        $insumos_stmt->bind_param("i", $product_id);
        $insumos_stmt->execute();
        $insumos_result = $insumos_stmt->get_result();

        while ($insumo = $insumos_result->fetch_assoc()) {
            $inventory_id = $insumo['inventory_id'];
            $qty_needed = $insumo['qty_needed'];

            $total_to_deduct = $qty_needed * $qty_ordered;

            $update_inv = $conexion->prepare("
                UPDATE inventory 
                SET quantity = quantity - ? 
                WHERE id = ? AND store_id = ?
            ");
            $update_inv->bind_param("iii", $total_to_deduct, $inventory_id, $store_id);
            $update_inv->execute();
        }
    }

    $conexion->query("SET @SKIP_INVENTORY_LOG = NULL");

    header("Location: pedidos.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pedidos - Stocky</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<main class="container my-5">
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Realizar Pedidos</h2>
        <a href="dashboard.php" class="btn btn-dashboard btn-sm">⬅ Volver al Inicio</a>
  </div>

  <!-- Cliente -->
  <div class="mb-3">
    <label for="client" class="form-label">Nombre cliente/Mesa</label>
    <input type="text" id="client" class="form-control" placeholder="Ingrese el nombre del cliente" required>
  </div>

  <!-- Observaciones -->
  <div class="mb-4">
    <label for="comment" class="form-label">Observaciones</label>
    <textarea id="comment" class="form-control" rows="2" placeholder="Notas especiales para este pedido..."></textarea>
  </div>

  <!-- Tabla de productos -->
    <div class="table-responsive">
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Producto</th>
        <th>Precio</th>
        <th>Stock disponible</th>
        <th>Acción</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($p = $productos_result->fetch_assoc()): ?>
        <tr data-product-id="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>" data-stock="<?= $p['stock_disponible'] ?>">
          <td><?= htmlspecialchars($p['name']) ?></td>
          <td>$<?= number_format($p['price'], 2) ?></td>
          <td class="stock"><?= $p['stock_disponible'] ?? 0 ?></td>
          <td class="action">
            <?php if ($p['stock_disponible'] > 0): ?>
              <button class="btn btn-primary btn-sm agregar-btn">Agregar</button>
            <?php else: ?>
              <button class="btn btn-secondary btn-sm" disabled>No disponible</button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
            </div>

  <!-- Pedido actual -->
  <h3 class="mt-5">Pedido actual</h3>
  <div class="table-responsive">
  <table class="table table-bordered table-striped" id="pedidoTable">
    <thead>
      <tr>
        <th>Producto</th>
        <th>Cantidad</th>
        <th>Precio unitario</th>
        <th>Subtotal</th>
        <th>Acción</th>
      </tr>
    </thead>
    <tbody></tbody>
    <tfoot>
      <tr>
        <th colspan="3">Total</th>
        <th id="totalPedido">$0.00</th>
        <th></th>
      </tr>
    </tfoot>
  </table>
</div>
  <button id="finalizarPedido" class="btn btn-primary mt-3">Finalizar Pedido</button>
</div>
</main>
<?php include 'footer.php'; ?>

<script>
// --- Datos de insumos ---
const productMaterials = {};
<?php
$insumos_stmt = $conexion->prepare("SELECT product_id, inventory_id, qty_needed FROM product_materials");
$insumos_stmt->execute();
$insumos_res = $insumos_stmt->get_result();
while($row = $insumos_res->fetch_assoc()): ?>
if(!productMaterials[<?= $row['product_id'] ?>]) productMaterials[<?= $row['product_id'] ?>] = [];
productMaterials[<?= $row['product_id'] ?>].push({inventory_id: <?= $row['inventory_id'] ?>, qty_needed: <?= $row['qty_needed'] ?>});
<?php endwhile; ?>

// --- Inventario inicial ---
const inventoryStock = {};
<?php
$inventory_stmt = $conexion->prepare("SELECT id, quantity FROM inventory WHERE store_id = ?");
$inventory_stmt->bind_param("i", $store_id);
$inventory_stmt->execute();
$inventory_res = $inventory_stmt->get_result();
while($inv = $inventory_res->fetch_assoc()): ?>
inventoryStock[<?= $inv['id'] ?>] = <?= $inv['quantity'] ?>;
<?php endwhile; ?>

// ---------------- JS de lógica de pedido ----------------
const pedido = {}; // {id: {id, name, price, qty}}

// 🔹 Recalcular inventario restante global según el pedido
function calcularInventarioRestante() {
    const restante = {...inventoryStock};
    Object.values(pedido).forEach(item => {
        if (productMaterials[item.id]) {
            productMaterials[item.id].forEach(pm => {
                restante[pm.inventory_id] -= pm.qty_needed * item.qty;
            });
        }
    });
    return restante;
}

// 🔹 Stock máximo disponible para un producto
function calcularStockProducto(pid, inventario = null) {
    if (!productMaterials[pid]) return 0;
    if (!inventario) inventario = calcularInventarioRestante();

    let maxStock = Infinity;
    productMaterials[pid].forEach(pm => {
        const available = inventario[pm.inventory_id] || 0;
        maxStock = Math.min(maxStock, Math.floor(available / pm.qty_needed));
    });

    return Math.max(0, maxStock);
}

// 🔹 Actualizar stock en la tabla principal
function actualizarStockTabla() {
    const inventarioRestante = calcularInventarioRestante();

    document.querySelectorAll('tr[data-product-id]').forEach(tr => {
        const pid = tr.dataset.productId;
        const stockDisponible = calcularStockProducto(pid, inventarioRestante);

        tr.dataset.stock = stockDisponible;
        tr.querySelector('.stock').textContent = stockDisponible;

        const btn = tr.querySelector('.agregar-btn');
        if (btn) {
            btn.disabled = stockDisponible <= 0 || pedido[pid];
            btn.textContent = pedido[pid] ? "Ya agregado" : "Agregar";
            btn.classList.toggle("btn-secondary", pedido[pid] || stockDisponible <= 0);
            btn.classList.toggle("btn-primary", !pedido[pid] && stockDisponible > 0);
        }
    });
}

// 🔹 Actualizar la tabla del pedido
function actualizarTablaPedido(){
    const tbody = document.querySelector('#pedidoTable tbody');
    tbody.innerHTML = '';
    let total = 0;

    const inventarioRestante = calcularInventarioRestante();

    Object.values(pedido).forEach(item => {
        const subtotal = item.price * item.qty;
        total += subtotal;

        const tr = document.createElement('tr');
        const stockDisponible = calcularStockProducto(item.id, inventarioRestante);

        tr.innerHTML = `
            <td>${item.name}</td>
            <td>${item.qty}</td>
            <td>$${item.price.toFixed(2)}</td>
            <td>$${subtotal.toFixed(2)}</td>
            <td>
                <button class="btn btn-sm btn-success plus-btn" ${stockDisponible <= 0 ? "disabled" : ""}>+</button>
                <button class="btn btn-sm btn-danger minus-btn">-</button>
            </td>
        `;
        tbody.appendChild(tr);

        // "+"
        tr.querySelector('.plus-btn').addEventListener('click', () => {
            const inventarioRestante = calcularInventarioRestante();
            if (calcularStockProducto(item.id, inventarioRestante) > 0) {
                item.qty++;
                actualizarTablaPedido();
            }
        });

        // "-"
        tr.querySelector('.minus-btn').addEventListener('click', () => {
            if (item.qty > 1) {
                item.qty--;
            } else {
                delete pedido[item.id];
            }
            actualizarTablaPedido();
        });
    });

    document.getElementById('totalPedido').textContent = `$${total.toFixed(2)}`;
    actualizarStockTabla();
}


// 🔹 Agregar producto desde tabla principal
document.querySelectorAll('.agregar-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
        const tr = btn.closest('tr');
        const id = tr.dataset.productId;
        const name = tr.cells[0].textContent;
        const price = parseFloat(tr.dataset.price);

        const inventarioRestante = calcularInventarioRestante();
        if(calcularStockProducto(id, inventarioRestante) > 0){
            if(!pedido[id]){
                pedido[id] = {id, name, price, qty:1};
                actualizarTablaPedido();
            }
        }
    });
});

// 🔹 Finalizar pedido
document.getElementById('finalizarPedido').addEventListener('click', ()=>{
    if(Object.keys(pedido).length === 0){
        alert('No hay productos en el pedido.');
        return;
    }
    const clientName = document.getElementById('client').value.trim();
    if(!clientName){
        alert('Por favor ingresa el nombre del cliente.');
        return;
    }

    document.getElementById('pedidoInput').value = JSON.stringify(Object.values(pedido));
    document.getElementById('commentInput').value = document.getElementById('comment').value;
    document.getElementById('clientInput').value  = clientName;
    document.getElementById('pedidoForm').submit();
});
</script>

<form id="pedidoForm" method="POST" style="display:none;">
  <input type="hidden" name="pedido" id="pedidoInput">
  <input type="hidden" name="comment" id="commentInput">
  <input type="hidden" name="client" id="clientInput"><!-- 🔹 nuevo campo -->
</form>
</body>
</html>
