<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['store_id'])) {
    header("Location: login.php");
    exit();
}

require_once "conexion.php"; // tu conexión a la BD
$user_id = $_SESSION['user_id'];
$store_id = $_SESSION['store_id'];

// --- Procesar eliminación ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);

    // En lugar de borrar, desactivar
    $del_item = $conexion->prepare("UPDATE inventory SET activo = 0 WHERE id = ? AND store_id = ?");
    $del_item->bind_param("ii", $delete_id, $store_id);
    $del_item->execute();

    header("Location: inventario.php");
    exit();
}

// --- Procesar el formulario de agregar ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_id'])) {
    $name = trim($_POST['name']);
    $brand = trim($_POST['brand']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $size = intval($_POST['size']); // usado para multiplicar cantidad
    $limit = isset($_POST['limite']) ? intval($_POST['limite']) : 0;
    $unit = trim($_POST['unit']);

    if ($size <= 0) $size = 1;
    $total_quantity = $quantity * $size;

    // Verificar si ya existe un insumo con mismo name + brand en esta tienda
    $check = $conexion->prepare("SELECT id, quantity FROM inventory WHERE name = ? AND brand = ? AND store_id = ?");
    $check->bind_param("ssi", $name, $brand, $store_id);
    $check->execute();
    $result = $check->get_result();

    if ($row = $result->fetch_assoc()) {
    // Ya existe → actualizar cantidad
    $new_quantity = $row['quantity'] + $total_quantity;
    $update = $conexion->prepare("UPDATE inventory SET quantity = ?, price = ?, limite = ? WHERE id = ?");
$update->bind_param("idii", $new_quantity, $price, $limit, $row['id']);
    $update->execute();

    // 🔹 Registrar en inventory_logs (UPDATE)
    $log = $conexion->prepare("INSERT INTO inventory_logs 
        (inventory_id, store_id, action, name, quantity, unit, price) 
        VALUES (?, ?, 'update', ?, ?, ?, ?)");
    $log->bind_param("iisdss", 
        $row['id'],        // inventory_id
        $store_id,         // store_id
        $name,             // name
        $new_quantity,     // quantity
        $unit,             // unit
        $price             // price
    );
    $log->execute();

} else {
    // No existe → insertar nuevo
    $insert = $conexion->prepare("INSERT INTO inventory 
    (store_id, user_id, name, brand, quantity, price, unit, limite) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$insert->bind_param("iissidsi", $store_id, $user_id, $name, $brand, $total_quantity, $price, $unit, $limit);

    $insert->execute();

    $new_id = $conexion->insert_id; // ID del nuevo insumo

    // 🔹 Registrar en inventory_logs (INSERT)
    $log = $conexion->prepare("INSERT INTO inventory_logs 
        (inventory_id, store_id, action, name, quantity, unit, price) 
        VALUES (?, ?, 'insert', ?, ?, ?, ?)");
    $log->bind_param("iisdss", 
        $new_id,           // inventory_id
        $store_id,         // store_id
        $name,             // name
        $total_quantity,   // quantity
        $unit,             // unit
        $price             // price
    );
    $log->execute();
}

}

// --- Obtener insumos existentes de la tienda ---
$products = $conexion->prepare("SELECT * FROM inventory WHERE store_id = ? AND activo = 1");
$products->bind_param("i", $store_id);
$products->execute();
$products_result = $products->get_result();

// Para selects dinámicos (nombres y marcas existentes)
$names = $conexion->prepare("SELECT DISTINCT name FROM inventory WHERE store_id = ?");
$names->bind_param("i", $store_id);
$names->execute();
$names_result = $names->get_result();

$brands = $conexion->prepare("SELECT DISTINCT brand FROM inventory WHERE store_id = ?");
$brands->bind_param("i", $store_id);
$brands->execute();
$brands_result = $brands->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
   

<?php include 'navbar.php'; ?>
<main class="container my-5">
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="m-0">Gestión de Inventario</h2>
    <a href="dashboard.php" class="btn btn-dashboard btn-sm">⬅ Volver al Inicio</a>

</div>

    <!-- Formulario -->
    <form method="POST" class="p-4 bg-light rounded shadow-sm">
        <div class="row mb-3">
            <!-- Nombre del insumo -->
            <div class="col-md-6">
                <label class="form-label">Nombre del insumo</label>
                <select class="form-select" name="name" id="nameSelect" onchange="toggleNameInput(this)">
                    <option value="">-- Seleccione --</option>
                    <?php while ($n = $names_result->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($n['name']) ?>"><?= htmlspecialchars($n['name']) ?></option>
                    <?php endwhile; ?>
                    <option value="other">Otro...</option>
                </select>
                <input type="text" name="name" id="nameInput" class="form-control mt-2 d-none" placeholder="Nuevo insumo">
            </div>

            <!-- Marca / Proveedor -->
            <div class="col-md-6">
                <label class="form-label">Marca / Proveedor</label>
                <select class="form-select" name="brand" id="brandSelect" onchange="toggleBrandInput(this)">
                    <option value="">-- Seleccione --</option>
                    <?php while ($b = $brands_result->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($b['brand']) ?>"><?= htmlspecialchars($b['brand']) ?></option>
                    <?php endwhile; ?>
                    <option value="other">Otro...</option>
                </select>
                <input type="text" name="brand" id="brandInput" class="form-control mt-2 d-none" placeholder="Nueva marca/proveedor">
            </div>
        </div>

        <div class="row mb-3">
            
            <div class="col-md-3">
                <label class="form-label">Tamaño del insumo unitario</label>
                <input type="number" name="quantity" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Medida</label>
                <select name="unit" class="form-select no-uppercase"  required>
                    <option value="ml">ml</option>
                    <option value="gr">gr</option>
                    <option value="lb">lb</option>
                    <option value="lt">lt</option>
                    <option value="kg">kg</option>
                    <option value="unid">unidad</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Cantidad de empaques del insumo</label>
                <input type="number" name="size" value="1" class="form-control">
            </div>
            
 <div class="col-md-2">
    <label class="form-label">Límite recompra</label>
    <input type="number" name="limite" class="form-control" value="0" min="0" required>
</div>

            <div class="col-md-2">
                <label class="form-label">Precio Total del Insumo</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>

           
        </div>

        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>

    <!-- Tabla de inventario -->
    <h3 class="mt-5">Inventario Actual</h3>
    <div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Insumo</th>
                <th>Marca/Proveedor</th>
                <th>Cantidad</th>
                <th>Medida</th>
                <th>Límite</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($p = $products_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= htmlspecialchars($p['brand']) ?></td>
                <td><?= number_format($p['quantity'], 0, ',', '.') ?></td>
                <td><?= htmlspecialchars($p['unit']) ?></td>
                <td><?= htmlspecialchars($p['limite']) ?></td>

                <td>
                    <a href="editar_inventario.php?id=<?= $p['id'] ?>" 
   class="btn btn-sm btn-warning me-2">Editar</a>

<form method="POST" class="d-inline m-0 p-0" 
      onsubmit="return confirm('¿Seguro que deseas eliminar este insumo?');">
    <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
</form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>
</main>
<!-- Footer -->
<?php include 'footer.php'; ?>

<script>
function toggleNameInput(select) {
    const input = document.getElementById('nameInput');
    if (select.value === 'other') {
        input.classList.remove('d-none');
        input.name = 'name';
        select.name = 'name_select';
    } else {
        input.classList.add('d-none');
        select.name = 'name';
        input.name = 'name_input';
    }
}

function toggleBrandInput(select) {
    const input = document.getElementById('brandInput');
    if (select.value === 'other') {
        input.classList.remove('d-none');
        input.name = 'brand';
        select.name = 'brand_select';
    } else {
        input.classList.add('d-none');
        select.name = 'brand';
        input.name = 'brand_input';
    }
}
</script>

</body>
</html>
