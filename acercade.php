<?php
// acercade.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acerca de nosotros</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <style>
    body {
      background-color: #f4f6f9;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .card {
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .card-header {
      background: linear-gradient(135deg, #4a5568, #2d3748);
      color: #fff;
      border-top-left-radius: 15px;
      border-top-right-radius: 15px;
      text-align: center;
      font-weight: bold;
      font-size: 1.3rem;
      padding: 1rem;
    }
    .card-body p {
      text-align: justify;
      color: #333;
    }
    .btn-back {
      background-color: #2d3748;
      color: #fff;
      border-radius: 8px;
      padding: 0.5rem 1.2rem;
      text-decoration: none;
      transition: 0.3s;
    }
    .btn-back:hover {
      background-color: #4a5568;
      color: #fff;
    }
  </style>
</head>
<body>
  <div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card col-md-8">
      <div class="card-header">
        Acerca de Nosotros
      </div>
      <div class="card-body">
        <h5 class="mb-3">Nuestro Propósito</h5>
        <p>
          Esta aplicación fue creada con el objetivo de brindar a pequeños y medianos negocios una herramienta sencilla y práctica para gestionar
          de manera eficiente su inventario, ventas y finanzas. Sabemos lo retador que puede ser mantener el control diario de insumos y movimientos, por eso buscamos simplificar este proceso.
        </p>
        <h5 class="mb-3">¿Qué queremos lograr?</h5>
        <p>
          Queremos ayudarte a optimizar el tiempo que dedicas a la gestión de tu tienda, mejorar la claridad en los reportes financieros y ofrecerte una visión clara del estado de tu negocio para que tomes mejores decisiones.
        </p>
        <h5 class="mb-3">¿A quién está dirigida?</h5>
        <p>
          La aplicación está pensada principalmente para emprendedores, pequeños comerciantes y dueños de negocios locales que desean
          tener el control de sus operaciones sin necesidad de sistemas complejos o costosos.
        </p>
        <div class="text-center mt-4">
          <a href="index.php" class="btn-back">⬅ Volver al inicio de sesión</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
