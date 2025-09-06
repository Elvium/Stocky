<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['store_id'])) {
    header("Location: login.php");
    exit();
}

require_once "conexion.php";
$user_id = $_SESSION['user_id'];
$store_id = $_SESSION['store_id'];

$message = "";

// --- Procesar eliminación de producto ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);

    // Eliminar producto, los insumos asociados se eliminan en cascade
    $delete = $conexion->prepare("DELETE FROM products WHERE id = ? AND store_id = ?");
    $delete->bind_param("ii", $delete_id, $store_id);
    $delete->execute();

    // Redirigir para evitar reenvío de formulario
    header("Location: productos.php");
    exit();
}


// --- Procesar creación de producto ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['product_name'])) {
    $name = strtoupper(trim($_POST['product_name']));
    $price = floatval($_POST['price']);

    // Insertar en products
    $insert_product = $conexion->prepare(
        "INSERT INTO products (store_id, user_id, name, price) VALUES (?, ?, ?, ?)"
    );
    $insert_product->bind_param("iisd", $store_id, $user_id, $name, $price);

    if ($insert_product->execute()) {
        $product_id = $insert_product->insert_id;
        $insert_product->close();

        // Insertar en product_materials
        $insert_material = $conexion->prepare(
            "INSERT INTO product_materials (product_id, inventory_id, qty_needed) VALUES (?, ?, ?)"
        );

        $insumo_ids = $_POST['insumo_id'] ?? [];
        $insumo_qtys = $_POST['insumo_qty'] ?? [];

        foreach ($insumo_ids as $i => $inventory_id) {
            $inventory_id = intval($inventory_id);
            $qty_needed = floatval($insumo_qtys[$i] ?? 0);

            if ($inventory_id > 0 && $qty_needed > 0) {
                $insert_material->bind_param("iid", $product_id, $inventory_id, $qty_needed);
                $insert_material->execute();
            }
        }
        $insert_material->close();

        $message = "✅ Producto y receta guardados correctamente.";
    } else {
        $message = "❌ Error al guardar el producto: " . $insert_product->error;
    }
}

// --- Obtener insumos existentes de la tienda ---
$insumos = $conexion->prepare("SELECT id, name, brand, unit FROM inventory WHERE store_id = ? AND activo = 1");
$insumos->bind_param("i", $store_id);
$insumos->execute();
$insumos_result = $insumos->get_result();

// Guardamos los insumos en un array para usar en PHP y JS
$insumos_array = [];
while ($ins = $insumos_result->fetch_assoc()) {
    $insumos_array[] = $ins;
}

// --- Obtener productos existentes de la tienda ---
$productos = $conexion->prepare("SELECT id, name, price FROM products WHERE store_id = ?");
$productos->bind_param("i", $store_id);
$productos->execute();
$productos_result = $productos->get_result();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<main class="container my-5">
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Gestión de Productos</h2>
        <a href="dashboard.php" class="btn btn-dashboard btn-sm">⬅ Volver al Inicio</a>

    </div>


    <?php if (!empty($message)): ?>
    <div class="alert alert-info"><?= $message ?></div>
<?php endif; ?>
    <!-- Formulario para crear producto -->
    <form method="POST"  class="p-4 bg-light rounded shadow-sm">
        <input type="hidden" name="user_id" value="<?= $user_id ?>">
        <input type="hidden" name="store_id" value="<?= $store_id ?>">

        <div class="row mb-3">
            <div class="col-md-8">
                <label class="form-label">Nombre del producto</label>
                <input type="text" name="product_name" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Precio</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>
        </div>

        <h5 class="mt-4">Insumos necesarios</h5>
        <?php for ($i = 1; $i <= 10; $i++): ?>
    <div class="row mb-2">
        <div class="col-md-8">
            <select name="insumo_id[]" class="form-select">
                <option value="">-- Seleccione insumo --</option>
                <?php foreach ($insumos_array as $ins): ?>
                    <option value="<?= $ins['id'] ?>">
                        <?= htmlspecialchars($ins['name']) ?> (<?= htmlspecialchars($ins['brand']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 d-flex">
            <input type="number" name="insumo_qty[]" class="form-control me-2" placeholder="Cantidad" min="0">
            <span class="unit-label"></span>
        </div>
    </div>
<?php endfor; ?>


        <button type="submit" class="btn btn-primary mt-3">Guardar producto</button>
    </form>

    <!-- Tabla de productos -->
    <h3 class="mt-5">Productos existentes</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($p = $productos_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td>$<?= number_format($p['price'], 2) ?></td>
                <td>
                    <a href="editar_producto.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning me-2">Editar</a>
                    <form method="POST" class="d-inline m-0 p-0" 
                          onsubmit="return confirm('¿Seguro que deseas eliminar este producto?');">
                        <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</main>
<?php include 'footer.php'; ?>


<script>
// Convertir insumos PHP a JS usando JSON
const insumoUnits = <?= json_encode(array_column($insumos_array, 'unit', 'id')) ?>;

document.querySelectorAll('select[name="insumo_id[]"]').forEach(select => {
    select.addEventListener('change', function() {
        const selectedId = this.value;
        const unitSpan = this.closest('.row').querySelector('.unit-label');
        unitSpan.textContent = insumoUnits[selectedId] || '';
    });
});
</script>



</body>
</html>
