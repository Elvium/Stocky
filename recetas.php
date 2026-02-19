<?php
session_start();

require 'verificar_sesion.php';

$user_id = $_SESSION['user_id'];
$store_id = $_SESSION['store_id'];

// Consultar recetas (productos) de la tienda
$sql = "
    SELECT p.id AS product_id, p.name AS product_name, p.price
    FROM products p
    WHERE p.store_id = ?
    ORDER BY p.name ASC
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $store_id);
$stmt->execute();
$productos = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Recetas - Stocky</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
 <link rel="icon" type="image/png" href="img/favicon.png">
<link rel="shortcut icon" href="img/favicon.png">
</head>
<body>
<?php include 'navbar.php'; ?>

<main class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="m-0 text-center flex-grow-1">📖 Recetas de la tienda</h2>
    <a href="dashboard.php" class="btn btn-dashboard btn-sm">⬅ Volver al Inicio</a>
  </div>

  <!-- Filtro de búsqueda -->
  <div class="mb-4">
    <input type="text" id="buscadorRecetas" class="form-control" placeholder="🔍 Buscar receta por nombre...">
  </div>

  <div class="accordion" id="recetasAccordion">
    <?php 
    $i = 1;
    while ($producto = $productos->fetch_assoc()): 
        $product_id = $producto['product_id'];

        // Consultar ingredientes de la receta
        $sqlIng = "
            SELECT i.name AS insumo, pm.qty_needed, i.unit
            FROM product_materials pm
            JOIN inventory i ON pm.inventory_id = i.id
            WHERE pm.product_id = ? AND i.store_id = ?
        ";
        $stmtIng = $conexion->prepare($sqlIng);
        $stmtIng->bind_param("ii", $product_id, $store_id);
        $stmtIng->execute();
        $ingredientes = $stmtIng->get_result();
    ?>
      <div class="accordion-item receta-item">
        <h2 class="accordion-header" id="heading<?= $i ?>">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $i ?>">
            <div class="receta-header w-100">
              <span class="receta-nombre"><?= htmlspecialchars($producto['product_name']) ?></span>
              <span class="receta-precio">$<?= number_format($producto['price'], 0) ?></span>
            </div>
          </button>
        </h2>
        <div id="collapse<?= $i ?>" class="accordion-collapse collapse" data-bs-parent="#recetasAccordion">
          <div class="accordion-body">
            <h6>Ingredientes:</h6>
            <ul>
              <?php while($ing = $ingredientes->fetch_assoc()): ?>
                <li><?= htmlspecialchars($ing['insumo']) ?> - <strong><?= $ing['qty_needed'] ?> <?= $ing['unit'] ?></strong></li>
              <?php endwhile; ?>
            </ul>
          </div>
        </div>
      </div>
    <?php 
      $i++;
    endwhile; 
    ?>
  </div>
</main>

<?php include 'footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const buscador = document.getElementById('buscadorRecetas');
  const items = Array.from(document.querySelectorAll('.receta-item'));

  // Normaliza texto para quitar acentos y comparar en minúsculas
  function normalizeString(str) {
    if (!str) return '';
    return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
  }

  buscador.addEventListener('input', function () {
    const q = normalizeString(this.value.trim());

    items.forEach(item => {
      const nameEl = item.querySelector('.receta-nombre');
      const name = nameEl ? normalizeString(nameEl.textContent) : '';

      if (!q || name.includes(q)) {
        // mostrar
        item.style.display = '';
      } else {
        // ocultar: si está abierto, cerrarlo antes (para evitar restos visuales)
        const collapse = item.querySelector('.accordion-collapse');
        const btn = item.querySelector('.accordion-button');
        if (collapse && collapse.classList.contains('show') && btn) {
          // Usamos bootstrap collapse programáticamente: disparar click en el botón lo cierra
          btn.click();
        }
        item.style.display = 'none';
      }
    });
  });

  // Opcional: limpiar búsqueda con Escape
  buscador.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      this.value = '';
      this.dispatchEvent(new Event('input'));
    }
  });
});
</script>

</body>