<?php
session_start();
require 'verificar_sesion.php'; 
require_once "conexion.php";

$user_id = $_SESSION['user_id'];
$store_id = $_SESSION['store_id'];

// Obtener productos con stock disponible
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
<div class="container mt-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Realizar Pedidos</h2>
        <a href="dashboard.php" class="btn btn-primary btn-sm">⬅ Volver al Inicio</a>
  </div>

  <!-- Tabla de productos -->
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

  <!-- Observaciones -->
  <div class="mb-4">
    <label for="comment" class="form-label">Observaciones</label>
    <textarea id="comment" class="form-control" rows="2" placeholder="Notas especiales para este pedido..."></textarea>
  </div>

  <!-- Pedido actual -->
  <h3 class="mt-5">Pedido actual</h3>
  <table class="table table-bordered" id="pedidoTable">
    <thead>
      <tr>
        <th>Producto</th>
        <th>Cantidad</th>
        <th>Precio unitario</th>
        <th>Subtotal</th>
        <th>Acción</th>
      </tr>
    </thead>
    <tbody>
      <!-- Filas dinámicas -->
    </tbody>
    <tfoot>
      <tr>
        <th colspan="3">Total</th>
        <th id="totalPedido">$0.00</th>
        <th></th>
      </tr>
    </tfoot>
  </table>

  <button id="finalizarPedido" class="btn btn-success mt-3">Finalizar Pedido</button>

</div>
</main>
<?php include 'footer.php'; ?>

<script>
// Manejo del pedido
const pedido = {}; // {productId: {id, name, price, qty, stock}}

function actualizarTablaPedido() {
  const tbody = document.querySelector('#pedidoTable tbody');
  tbody.innerHTML = '';
  let total = 0;

  Object.values(pedido).forEach(item => {
    const subtotal = item.price * item.qty;
    total += subtotal;

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${item.name}</td>
      <td>${item.qty}</td>
      <td>$${item.price.toFixed(2)}</td>
      <td>$${subtotal.toFixed(2)}</td>
      <td>
        <button class="btn btn-sm btn-success plus-btn">+</button>
        <button class="btn btn-sm btn-danger minus-btn">-</button>
      </td>
    `;
    tbody.appendChild(tr);

    tr.querySelector('.plus-btn').addEventListener('click', () => {
      if(item.qty < item.stock) {
        item.qty++;
        actualizarTablaPedido();
      }
    });
    tr.querySelector('.minus-btn').addEventListener('click', () => {
      item.qty--;
      if(item.qty <= 0) {
        delete pedido[item.id];
      }
      actualizarTablaPedido();
    });
  });

  document.getElementById('totalPedido').textContent = `$${total.toFixed(2)}`;
}

// Agregar producto desde tabla principal
document.querySelectorAll('.agregar-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const tr = btn.closest('tr');
    const id = tr.dataset.productId;
    const name = tr.cells[0].textContent;
    const price = parseFloat(tr.dataset.price);
    const stock = parseInt(tr.dataset.stock);

    if(!pedido[id]) {
      pedido[id] = {id, name, price, qty: 1, stock};
    }
    actualizarTablaPedido();
  });
});

// Finalizar pedido (insertar en BD)
document.getElementById('finalizarPedido').addEventListener('click', () => {
  if(Object.keys(pedido).length === 0) {
    alert('No hay productos en el pedido.');
    return;
  }

  const comment = document.getElementById('comment').value;

  fetch('guardar_pedido.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({user_id: <?= $user_id ?>, store_id: <?= $store_id ?>, comment, items: Object.values(pedido)})
  })
  .then(res => res.json())
  .then(data => {
    if(data.success) {
      alert('Pedido registrado correctamente.');
      location.reload();
    } else {
      alert('Error al registrar el pedido: ' + data.error);
    }
  });
});
</script>
</body>
</html>
