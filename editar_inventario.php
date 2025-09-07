<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['store_id'])) {
    header("Location: login.php");
    exit();
}

require_once "conexion.php";
$user_id = $_SESSION['user_id'];
$store_id = $_SESSION['store_id'];

// --- Obtener producto por ID ---
if (!isset($_GET['id'])) {
    header("Location: inventario.php");
    exit();
}

$id = intval($_GET['id']);
$stmt = $conexion->prepare("SELECT id, name, brand, quantity, unit FROM inventory WHERE id = ? AND store_id = ?");
$stmt->bind_param("ii", $id, $store_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "<div class='container mt-5 alert alert-danger'>Producto no encontrado.</div>";
    exit();
}

// --- Procesar actualización ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['name']);
    $new_quantity = intval($_POST['quantity']);

    if ($new_quantity < 0) $new_quantity = 0;

    // Marcar variable para saltar trigger
    $conexion->query("SET @SKIP_INVENTORY_LOG = 1");

    // Ejecutar el UPDATE
    $update = $conexion->prepare("UPDATE inventory 
                                  SET name = ?, quantity = ? 
                                  WHERE id = ? AND store_id = ?");
    $update->bind_param("siii", $new_name, $new_quantity, $id, $store_id);
    $update->execute();

    // Limpiar variable
    $conexion->query("SET @SKIP_INVENTORY_LOG = NULL");

    header("Location: inventario.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Insumo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<main class="container my-5">
    <div class="container mt-4">
        <h2>Editar Insumo</h2>

        <form method="POST" class="p-4 bg-light rounded shadow-sm">
            <div class="mb-3">
                <label class="form-label">Nombre del insumo</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Cantidad actual</label>
                <input type="number" name="quantity" class="form-control" value="<?= htmlspecialchars($product['quantity']) ?>" min="0" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Marca / Proveedor</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($product['brand']) ?>" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Unidad de medida</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($product['unit']) ?>" disabled>
            </div>

            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="inventario.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</main>
<?php include 'footer.php'; ?>
</body>
</html>
