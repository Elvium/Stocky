<?php
session_start();
require 'verificar_sesion.php'; // asegúrate de que solo entren usuarios logueados
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Instrucciones - Stocky</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<main class="container my-5">
<div class="container mt-5 pt-4">
  <div class="text-center mb-4">
    <img src="Logo2.PNG" alt="Stocky Logo" height="180" class="mb-3">
    <h2 class="fw-bold">Guía de uso de Stocky</h2>
    <p class="text-muted">Sigue estos pasos para administrar correctamente tu inventario, productos y ventas.</p>
  </div>

  <div class="accordion" id="instruccionesAccordion">
    <!-- Paso 1 -->
    <div class="accordion-item">
      <h2 class="accordion-header" id="heading1">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
          1️⃣ Ingresa los insumos de tu tienda
        </button>
      </h2>
      <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#instruccionesAccordion">
        <div class="accordion-body">
          <ul>
            <li>Ingresa a la sección <strong>Inventario</strong>.</li>
            <li>Registra cada insumo con:
              <ul>
                <li><em>Nombre</em> (ej: Harina, Azúcar, Café en grano)</li>
                <li><em>Marca / Proveedor</em> (si no existe, lo puedes crear)</li>
                <li><em>Cantidad</em> (ej: 2.5)</li>
                <li><em>Tamaño</em> (ej: Bolsa 1kg, Botella 1lt)</li>
                <li><em>Peso/Unidad</em> (kg, gr, oz, lb)</li>
                <li><em>Precio</em> (costo de adquisición del insumo)</li>
              </ul>
            </li>
            <li>Este paso asegura que tu inventario inicial quede correctamente configurado.</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Paso 2 -->
    <div class="accordion-item">
      <h2 class="accordion-header" id="heading2">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
          2️⃣ Ingresa los ingredientes para los productos de tu menú
        </button>
      </h2>
      <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#instruccionesAccordion">
        <div class="accordion-body">
          <ul>
            <li>Accede a la sección <strong>Productos</strong>.</li>
            <li>Define cada producto indicando:
              <ul>
                <li><em>Nombre</em> (ej: Pan de chocolate, Café expreso)</li>
                <li><em>Precio de venta</em></li>
                <li><em>Receta</em>: selecciona hasta 10 insumos del inventario, indicando la cantidad exacta usada por unidad de producto.</li>
              </ul>
            </li>
            <li>El sistema verificará automáticamente la disponibilidad de insumos al momento de crear pedidos.</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Paso 3 -->
    <div class="accordion-item">
      <h2 class="accordion-header" id="heading3">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
          3️⃣ Realiza y registra los pedidos para los clientes.
        </button>
      </h2>
      <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#instruccionesAccordion">
        <div class="accordion-body">
          <ul>
            <li>Ingresa a la sección <strong>Ventas</strong>.</li>
            <li>Selecciona los productos disponibles. 
              <span class="text-danger">⚠️ Si un producto no puede prepararse por falta de insumos, aparecerá deshabilitado.</span>
            </li>
            <li>Indica la cantidad de cada producto en el pedido.</li>
            <li>Confirma la venta: 
              <ul>
                <li>Se descontarán automáticamente los insumos del inventario.</li>
                <li>El pedido quedará registrado en el historial de ventas.</li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Paso 4 -->
    <div class="accordion-item">
      <h2 class="accordion-header" id="heading4">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
          4️⃣ Descarga los informes mensuales y diarios para la contabilidad de tu negocio.
        </button>
      </h2>
      <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#instruccionesAccordion">
        <div class="accordion-body">
          <ul>
            <li>Podrás revisar el <strong>historial de ventas</strong> de cada día.</li>
            <li>Consultar las <strong>estadísticas de productos más vendidos</strong>.</li>
            <li>Ver el consumo de insumos en tiempo real.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
</main>
<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
