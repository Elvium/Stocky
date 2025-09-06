<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['store_id'])) {
    header("Location: login.php");
    exit();
}

require_once "conexion.php";
$store_id = $_SESSION['store_id'];

// --- Obtener ID del producto ---
if (!isset($_GET['id'])) {
    header("Location: productos.php");
    exit();
}

$product_id = intval($_GET['id']);

// --- Obtener información del producto ---
$stmt = $conexion->prepare("SELECT id, name,price FROM products WHERE id = ? AND store_id = ?");
$stmt->bind_param("ii", $product_id, $store_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "<div class='container mt-5 alert alert-danger'>Producto no encontrado.</div>";
    exit();
}

// --- Obtener insumos relacionados ---
$materials_stmt = $conexion->prepare("
    SELECT pm.id, i.name, pm.qty_needed, i.unit
    FROM product_materials pm
    INNER JOIN inventory i ON pm.inventory_id = i.id
    WHERE pm.product_id = ?
");
$materials_stmt->bind_param("i", $product_id);
$materials_stmt->execute();
$materials_result = $materials_stmt->get_result();

// --- Procesar actualización ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $new_name = strtoupper(trim($_POST['name']));
    $new_price = floatval($_POST['price']);

// Actualizar nombre y precio del producto
$update = $conexion->prepare("UPDATE products SET name = ?, price = ? WHERE id = ? AND store_id = ?");
$update->bind_param("sdii", $new_name, $new_price, $product_id, $store_id);
$update->execute();

    // Actualizar cantidades de insumos
    if (isset($_POST['materials'])) {
        foreach ($_POST['materials'] as $material_id => $quantity) {
            $quantity = floatval($quantity);
            $update_material = $conexion->prepare("
                UPDATE product_materials SET qty_needed = ? 
                WHERE id = ? AND product_id = ?
            ");
            $update_material->bind_param("dii", $quantity, $material_id, $product_id);
            $update_material->execute();
        }
    }

    header("Location: productos.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<main class="container my-5">
    <div class="container mt-4">
        <h2>Editar Producto</h2>

        <form method="POST" class="p-4 bg-light rounded shadow-sm">
            <div class="mb-3">
                <label class="form-label">Nombre del Producto</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>
            <div class="mb-3">
    <label class="form-label">Precio del Producto</label>
    <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($product['price']) ?>" required>
</div>

            <h4>Insumos relacionados</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Insumo</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($m = $materials_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['name']) ?></td>
                        <td>
                            <input type="number" step="0.01" name="materials[<?= $m['id'] ?>]" value="<?= $m['qty_needed'] ?>" class="form-control" required>
                        </td>
                        <td><?= htmlspecialchars($m['unit']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <button type="submit" name="update" class="btn btn-primary">Guardar Cambios</button>
            <a href="productos.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</main>

<?php include 'footer.php'; ?>
</body>
</html>
