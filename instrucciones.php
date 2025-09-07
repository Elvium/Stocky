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
      <p>
        En esta sección configuras el <strong>Inventario</strong>, donde registras todos los insumos que tu negocio utiliza para preparar productos. 
        Este paso es fundamental porque a partir de aquí se controlará el consumo y stock de tu tienda.
      </p>
      <ul>
        <li>Desde el menú <strong>Inicio</strong>, accede a la sección <strong>Inventario</strong>.</li>
        <li>Registra cada insumo en el formulario completando los siguientes campos:
          <ul>
            <li><em>Nombre:</em> selecciona un insumo ya creado o crea uno nuevo si no existe.</li>
            <li><em>Marca / Proveedor:</em> selecciona uno existente o agrega un nuevo proveedor (también puedes dejarlo en blanco).</li>
            <li><em>Tamaño del Insumo:</em> capacidad del empaque (ejemplo: 900 ml por bolsa de leche).</li>
            <li><em>Medida:</em> unidad con la que trabajas tus productos (ml, gr, lt, kg, etc.).</li>
            <li><em>Cantidad de empaques:</em> número de unidades que trae el insumo (ejemplo: 6 bolsas de leche).</li>
            <li><em>Precio:</em> costo total de adquisición del insumo.</li>
          </ul>
        </li>
      </ul>
      <div class="alert alert-info mt-2">
        ℹ️ <strong>Importante:</strong> para un mejor control, siempre registra los insumos en la unidad de medida más pequeña que utilices en tus productos.  
        Ejemplo: si usas leche en tazas, registra en mililitros.
      </div>
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
      <p>
        Aquí defines los <strong>Productos</strong> que ofrecerás en tu negocio, vinculándolos a los insumos del inventario. 
        Esto asegura que cada venta descuente automáticamente los insumos utilizados.
      </p>
      <ul>
        <li>Desde el menú <strong>Inicio</strong>, accede a la sección <strong>Productos</strong>.</li>
        <li>Agrega cada producto indicando:
          <ul>
            <li><em>Nombre:</em> nombre del producto que vendes (ejemplo: Pan de chocolate, Café expreso).</li>
            <li><em>Precio de venta:</em> valor al que lo ofreces a tus clientes.</li>
            <li><em>Insumos necesarios:</em> selecciona hasta 10 insumos del inventario e indica la cantidad exacta usada por unidad.</li>
          </ul>
        </li>
      </ul>
      <div class="alert alert-info mt-2">
        ℹ️ <strong>Tip:</strong> en la tabla ubicada en la parte inferior podrás editar las cantidades de insumos asignados a un producto o eliminarlo si ya no lo ofreces.
      </div>
    </div>
  </div>
</div>


<!-- Paso 3 -->
<div class="accordion-item">
  <h2 class="accordion-header" id="heading3">
    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
      3️⃣ Realiza y registra los pedidos para los clientes
    </button>
  </h2>
  <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#instruccionesAccordion">
    <div class="accordion-body">
      <p>
        En la sección de <strong>Ventas</strong> registras los pedidos que realizan tus clientes. 
        Aquí seleccionas productos, ajustas cantidades y confirmas la venta.
      </p>
      <ul>
        <li>Desde el menú <strong>Inicio</strong>, ingresa a la sección <strong>Ventas</strong>.</li>
        <li>Digita el <em>nombre del cliente</em> (opcional, pero recomendable para llevar control).</li>
        <li>Agrega observaciones si el pedido tiene especificaciones (ejemplo: “sin cebolla”, “sin salsas”).</li>
        <li>Selecciona los productos con el botón <strong>Agregar</strong>. 
          <span class="text-danger">⚠️ Si un producto no puede prepararse por falta de insumos, aparecerá deshabilitado.</span>
        </li>
        <li>Ajusta las cantidades usando los botones <strong>“+”</strong> o <strong>“-”</strong>.</li>
        <li>Confirma el pedido con el botón <strong>Finalizar Pedido</strong>:
          <ul>
            <li>Los insumos se descuentan automáticamente del inventario.</li>
            <li>El pedido se registra en el historial de ventas.</li>
          </ul>
        </li>
      </ul>
      <div class="alert alert-info mt-2">
        ℹ️ <strong>Nota:</strong> cada pedido confirmado quedará guardado en el historial, lo que te permitirá consultar ventas anteriores.
      </div>
    </div>
  </div>
</div>



    <!-- Paso 4 -->
<div class="accordion-item">
  <h2 class="accordion-header" id="heading4">
    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
      4️⃣ Cambia el estado de los pedidos.
    </button>
  </h2>
  <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#instruccionesAccordion">
    <div class="accordion-body">
      <p>
        En esta sección podrás <strong>marcar los pedidos como entregados</strong>.  
        Esto significa que el cliente ya recibió su pedido y la venta ha finalizado.  
        ⚠️ Es importante cambiar el estado de los pedidos para llevar un control claro entre los que aún están pendientes y los que ya se completaron.
      </p>
      <ul>
        
        <li>Desde el menú <strong>Inicio</strong>, ingresa a la sección <strong>Estado de Pedidos</strong>.</li>
        <li>Se mostrará una lista con todos los pedidos realizados.</li>
        <li>Identifica el pedido que deseas cerrar y presiona el botón <strong>"Marcar como Entregado"</strong>.</li>
        <li>Automáticamente:
          <ul>
            <li>El estado del pedido cambiará de <span class="badge bg-warning text-dark">Pendiente</span> a <span class="badge bg-success">Entregado</span>.</li>
            <li>El pedido pasará a formar parte del historial de ventas cerradas.</li>
            <li>Esto permitirá distinguir fácilmente entre pedidos aún activos y pedidos finalizados.</li>
          </ul>
        </li>
      </ul>
      <p class="text-primary">
        💡 Consejo: Actualiza siempre el estado de los pedidos al momento de la entrega.  
        Así tendrás un registro exacto de las ventas completadas y evitarás confusiones en el seguimiento de pedidos.
      </p>
    </div>
  </div>
</div>




<!-- Paso 5 -->
<div class="accordion-item">
  <h2 class="accordion-header" id="heading5">
    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5">
      5️⃣ Descarga informes mensuales y diarios para la contabilidad de tu negocio
    </button>
  </h2>
  <div id="collapse5" class="accordion-collapse collapse" data-bs-parent="#instruccionesAccordion">
    <div class="accordion-body">
      <p>
        En esta sección podrás generar <strong>informes detallados</strong> de las ventas y el consumo de insumos. 
        Estos reportes te ayudarán a llevar un control contable más organizado y a identificar tendencias en tu negocio.
      </p>
      <ul>
        <li>Revisa el <strong>historial de ventas</strong> de cada día para conocer todos los pedidos realizados.</li>
        <li>Consulta las <strong>estadísticas de productos más vendidos</strong> para identificar qué ofrece mejores resultados.</li>
        <li>Monitorea el <strong>consumo de insumos en tiempo real</strong>, asegurando que siempre tengas stock disponible.</li>
      </ul>
      <div class="alert alert-info mt-2">
        ℹ️ <strong>Tip:</strong> Los reportes diarios te permiten llevar la caja del día, mientras que los reportes mensuales facilitan la contabilidad general y el análisis de tu negocio.
      </div>
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
