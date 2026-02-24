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
  <link rel="icon" type="image/png" href="img/favicon.png">
<link rel="shortcut icon" href="img/favicon.png">
</head>
<body>
<?php include 'navbar.php'; ?>
<main class="container my-5">
<div class="container mt-5 pt-4">
  <div class="text-center mb-4">
    <img src="Logo2.png" alt="Stocky Logo" height="180" class="mb-3">
    <h2 class="fw-bold">Guía de uso de Stocky</h2>
    <p class="text-muted">Sigue estos pasos para administrar correctamente tu inventario, productos y ventas.</p>
  </div>

  <div class="accordion" id="instruccionesAccordion">

    <!-- Paso 1 -->
<div class="accordion-item">
  <h2 class="accordion-header" id="heading1">
    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
      1️⃣ <strong>INVENTARIO</strong>:  Ingresa los insumos de tu tienda
    </button>
  </h2>
  <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#instruccionesAccordion">
    <div class="accordion-body">

<!-- Video tutorial  -->
<div class="video-wrapper my-3">
  <video controls preload="metadata" poster="img/poster-inventario.jpg">
    <source src="videos/inventario.mp4" type="video/mp4">
    Tu navegador no soporta videos HTML5.
  </video>

</div>

<small class="text-muted d-block mt-2 text-center">
    ▶️ Video: Cómo registrar y administrar tu inventario en Stocky
  </small>
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
            <li><em>Límite de notificación:</em> cantidad mínima a partir de la cual el sistema mostrará un aviso de que el insumo está por acabarse.</li>
          </ul>
        </li>
      </ul>
      <div class="alert alert-info mt-2">
        ℹ️ <strong>Tip:</strong> para un mejor control, siempre registra los insumos en la unidad de medida más pequeña que utilices en tus productos.  
        Ejemplo: si usas leche en tazas, registra en mililitros.
      </div>
      <div class="alert alert-warning mt-2">
        ⚠️ <strong>Importante:</strong> cuando un insumo baje del <em>límite de notificación</em>, en la página de <strong>Inicio</strong> aparecerá una alerta con el nombre del insumo y la cantidad actual, para recordarte que necesitas reponerlo.
      </div>
      <div class="alert alert-primary mt-2">
        ✏️ <strong>Editar:</strong> utiliza este botón cuando hayas cometido un error al digitar la información de un insumo (por ejemplo, precio o cantidad).  
        De esta forma, corriges el registro sin afectar la trazabilidad de tus finanzas.
      </div>
      <div class="alert alert-danger mt-2">
        🗑️ <strong>Eliminar:</strong> usa este botón únicamente cuando un insumo se haya perdido o dañado, pero aún así representó un costo para tu negocio.  
        Así, los reportes financieros reflejarán claramente la pérdida y mantendrán la coherencia de tu inventario.
      </div>
    </div>
  </div>
</div>




<!-- Paso 23 -->
<div class="accordion-item">
  <h2 class="accordion-header" id="heading2">
    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
      2️⃣ <strong>RECETAS:</strong>  Ingresa los ingredientes para crear las recetas de tu menú
    </button>
  </h2>
  <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#instruccionesAccordion">
    <div class="accordion-body">

<div class="video-wrapper my-3">
  <video controls preload="metadata" >
    <source src="videos/Recetas.mp4" type="video/mp4">
    Tu navegador no soporta videos HTML5.
  </video>
  
</div>
<small class="text-muted d-block mt-2 text-center">
    ▶️ Video: Cómo crear tus recetas en Stocky
  </small>
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

<!-- Paso 6 -->
<div class="accordion-item">
  <h2 class="accordion-header" id="heading6">
    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6">
      📖 <strong>GUIA DE PREPARACION:</strong>  Consulta Recetas y Composición de Productos
    </button>
  </h2>
  <div id="collapse6" class="accordion-collapse collapse" data-bs-parent="#instruccionesAccordion">
    <div class="accordion-body">

    <div class="video-wrapper my-3">
  <video controls preload="metadata" >
    <source src="videos/Guia.mp4" type="video/mp4">
    Tu navegador no soporta videos HTML5.
  </video>
  
</div>

<small class="text-muted d-block mt-2 text-center">
    ▶️ Video: Cómo visualizar tus recetas en Stocky
  </small>

      <p>
        En la sección de <strong>Recetas</strong> puedes consultar en detalle cómo está compuesto cada producto de tu menú.  
        Allí verás qué insumos utiliza, en qué cantidades y cómo impacta en el costo total de producción.
      </p>
      <ul>
        <li>Desde el menú <strong>Inicio</strong>, accede a la sección <strong>Recetas</strong>.</li>
        <li>Selecciona un producto para visualizar su receta.</li>
        <li>La receta mostrará los insumos exactos usados y sus cantidades.</li>
        <li>Esto permite analizar <strong>costos de producción</strong> y garantizar consistencia en la preparación.</li>
      </ul>
      <div class="alert alert-info mt-2">
        ℹ️ <strong>Tip:</strong> Usa esta sección para capacitar a tu equipo en la preparación de los productos y asegurar que todos utilicen las mismas cantidades de insumos.
      </div>
    </div>
  </div>
</div>




<!-- Paso 3 -->
<div class="accordion-item">
  <h2 class="accordion-header" id="heading3">
    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
      3️⃣ <strong>PEDIDOS:</strong>  Registra los pedidos de los clientes
    </button>
  </h2>
  <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#instruccionesAccordion">
    <div class="accordion-body">

<div class="video-wrapper my-3">
  <video controls preload="metadata" >
    <source src="videos/Realizar pedidos.mp4" type="video/mp4">
    Tu navegador no soporta videos HTML5.
  </video>
 
</div>
 <small class="text-muted d-block mt-2 text-center">
    ▶️ Video: Cómo tomar pedidos en Stocky
  </small>
      <p>
        En la sección de <strong>Pedidos</strong> registras los pedidos que realizan tus clientes. 
        Aquí seleccionas productos, ajustas cantidades y confirmas la venta.
      </p>
      <ul>
        <li>Desde el menú <strong>Inicio</strong>, ingresa a la sección <strong>Pedidos</strong>.</li>
        <li>Digita el <em>nombre del cliente</em> <span class="text-danger">⚠️ Es obligatorio digitar el nombre del cliente para el registro de la venta.</span></li>
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
        ℹ️ <strong>Tip:</strong> cada pedido confirmado quedará guardado en el historial, lo que te permitirá consultar ventas anteriores.
      </div>
    </div>
  </div>
</div>



<div class="accordion-item">
  <h2 class="accordion-header" id="heading4">
    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
      4️⃣ <strong>ESTADO DE PEDIDOS:</strong>  Controla los estados de los pedidos
    </button>
  </h2>
  <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#instruccionesAccordion">
    <div class="accordion-body">

    <div class="video-wrapper my-3">
  <video controls preload="metadata" >
    <source src="videos/Estado de pedidos.mp4" type="video/mp4">
    Tu navegador no soporta videos HTML5.
  </video>
 
</div>
 <small class="text-muted d-block mt-2 text-center">
    ▶️ Video: Cómo visualizar tus pedidos en Stocky
  </small>
      <p>
        En la sección de <strong>Estado de Pedidos</strong> puedes gestionar el estado de los pedidos recientes en dos aspectos:<span class="badge bg-warning text-dark">Entregado</span> y <span class="badge bg-warning text-dark">Pagodo</span>, de manera independiente.. 
    
      </p>
     

      <ul>
        <li>Desde el menú <strong>Inicio</strong>, ingresa a la sección <strong>Estado de Pedidos</strong>.</li>
        <li>Cambia los estados del pedido haicendo uso de los botones en la columna de accion asi:.</li>
        <li><strong>NO ENTREGADO</strong> → Usar botón <em>Entregado</em> para cambiar el estado. <strong>Se entrego el pedido</strong></li>
        <li><strong>NO PAGADO</strong> → Usar botón <em>Pagado</em> para cambiar el estado. <strong>Se cobro el pedido</strong></li>
        <li>Cuando cambia a <span class="badge bg-success">ENTREGADO</span> o <span class="badge bg-success">PAGADO</span>, el botón se desactiva.</li>
      </ul>
 <p class="alert alert-primary mt-3">
        📌 <strong>Novedad:</strong> Ahora cada pedido muestra los <em>productos incluidos</em>
      </p>
      <p class="alert alert-info mt-2">
        ℹ️ <strong>Tip:</strong> Usa el filtro superior para ver únicamente pedidos <em>Pendientes</em>, <em>Entregados</em>, <em>Pagados</em> o <em>Todos</em>.  
        Así tendrás una vista rápida de qué pedidos requieren acción inmediata.
      </p>
    </div>
  </div>
</div>


<!-- Paso 2.1 -->
<div class="accordion-item">
  <h2 class="accordion-header" id="heading2_1">
    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2_1">
      💰 <strong>GASTOS:</strong>  Registra Gastos diarios
    </button>
  </h2>
  <div id="collapse2_1" class="accordion-collapse collapse" data-bs-parent="#instruccionesAccordion">
    <div class="accordion-body">


    <div class="video-wrapper my-3">
  <video controls preload="metadata" >
    <source src="videos/Registro de gastos.mp4" type="video/mp4">
    Tu navegador no soporta videos HTML5.
  </video>
 
</div>

 <small class="text-muted d-block mt-2 text-center">
    ▶️ Video: Cómo administrar tu gastos adicionales en Stocky
  </small>
      <p>
        En esta sección podrás registrar <strong>gastos adicionales</strong> (como servicios, domiciliarios, pagos a empleados o compras menores) 
        que requiere tu negocio.  
        Esto ayuda a que los informes reflejen no solo los ingresos por ventas y el costo de los insumos, sino también los gastos diarios de tu negocio.
      </p>
      <ul>
        <li>Desde el menú <strong>Inicio</strong>, accede a la sección <strong>Gastos</strong>.</li>
        <li>Selecciona el concepto del gasto (si no tienes ningun concepto de gasto puedes crearlo en el mismo formulario y se guardara):
          <ul>
            <li><span class="badge bg-danger">GASTO</span> → cuando registras un gasto general (ejemplo: servicios públicos, pago de personal, etc.).</li>
          </ul>
        </li>
        <li>Completa el campo de <strong>costo</strong> y oprime <strong>Registrar Gasto</strong>.</li>
      </ul>
      <div class="alert alert-warning mt-2">
        ⚠️ <strong>Importante:</strong> Registrar correctamente los gastos asegura que los informes contables reflejen la <em>realidad financiera</em> de tu negocio.
      </div>
      <div class="alert alert-info mt-2">
        ℹ️ <strong>Tip:</strong> Usa esta sección cada vez que hagas compras adicionales o pagos fijos para que tu balance sea más exacto. Tambien podras visualizar una tabla con los ultimos gastos registrados
      </div>
    </div>
  </div>
</div>





<!-- Paso 5 -->
<div class="accordion-item">
  <h2 class="accordion-header" id="heading5">
    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5">
      5️⃣ <strong>INFORMES:</strong>  Descarga informes mensuales y diarios para la contabilidad de tu negocio
    </button>
  </h2>
  <div id="collapse5" class="accordion-collapse collapse" data-bs-parent="#instruccionesAccordion">
    <div class="accordion-body">

<div class="video-wrapper my-3">
  <video controls preload="metadata" >
    <source src="videos/Informes de gastos.mp4" type="video/mp4">
    Tu navegador no soporta videos HTML5.
  </video>
  
</div>
<small class="text-muted d-block mt-2 text-center">
    ▶️ Video: Cómo obtener tu cierre de caja y reporte mensual en Stocky
  </small>
      <p>
        En esta sección podrás generar <strong>informes detallados</strong> de las ventas y el consumo de insumos en formato PDF. 
        Estos reportes te ayudarán a llevar un control contable más organizado y a identificar tendencias en tu negocio.
      </p>
      <ul>
        <li>Desde el menú <strong>Inicio</strong>, ingresa a la sección <strong>Informes</strong>.</li>
        <li>Selecciona la fecha que quieras obtener el <strong>Informe del Dia</strong> para conocer los pedidos realizados y tus productos mas vendidos ese dia.</li>
        <li>Selecciona el rango de fechas para obtener un <strong>Informe General</strong> para obtener un reporte de gastos vs ingresos de tu negocio durante ese periodo.</li>
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

</body>
</html>
