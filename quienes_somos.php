<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Quiénes Somos - Stocky</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/png" href="favicon.png">
  <link rel="shortcut icon" href="img/favicon.png">
 <style>
    .hero {
  background: linear-gradient(135deg, #00394f, #00a6a6);
  color: white;
  text-align: center;
  padding: 100px 20px 80px;
  position: relative;
}

.hero h1 {
  font-weight: 700;
  margin-bottom: 10px;
}

.hero p {
  font-size: 1.1rem;
  opacity: 0.9;
}

.btn-light {
  border-radius: 30px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-light:hover {
  background-color: #f8f9fa;
  transform: scale(1.05);
}

    
    .section h2 {
      color: #00394f;
      font-weight: 700;
      margin-bottom: 20px;
    }
    .section p {
      font-size: 1.1rem;
      color: #333;
    }
  </style>
</head>
<body>


  <div class="hero position-relative">
  <a href="index.php" class="btn btn-light position-absolute top-0 start-0 m-3 shadow-sm">
    ⬅ Volver al inicio
  </a>
  <h1>Sobre Nosotros</h1>
  <p>Conoce la historia y el propósito detrás de <strong>Stocky</strong>.</p>
</div>


  <main class="container section">
    <div class="text-center mb-5">
      <img src="Logo2.png" alt="Stocky Logo" height="180" class="mb-3">
      <p class="lead">Somos un equipo apasionado por hacer que la gestión de negocios sea más simple, accesible y eficiente.</p>
      
    </div>
 <hr class="my-5">
    <div class="text-center">
      <h3>¿Qué es Stocky?</h3>
      <p class="mt-3">
        <strong>Stocky</strong> es una aplicación creada para gestionar ventas, inventario y contabilidad de forma intuitiva.
        Buscamos ofrecer una plataforma fácil de usar, pensada especialmente para los negocios que quieren dar un paso hacia la digitalización,
        sin necesidad de conocimientos técnicos ni grandes inversiones.
      </p>
      <p>Porque creemos que <strong>cada emprendedor merece herramientas profesionales</strong> para crecer.</p>
    </div>
 <hr class="my-5">
    <div class="row align-items-center mb-5">
      <div class="col-md-6">
        
        <p>
          Nuestra misión es ayudar a pequeños y medianos emprendedores a tener el control total de sus negocios de manera práctica,
          moderna y desde cualquier lugar. Con <strong>Stocky</strong>, puedes administrar tus ventas, inventario y finanzas sin
          complicaciones, ahorrando tiempo y aumentando la productividad.
        </p>
      </div>
      <div class="col-md-6 text-center">
        <img src="img/mision.png" alt="Misión" class="img-fluid" style="max-width: 350px;">
      </div>
    </div>

    <div class="row align-items-center flex-md-row-reverse">
      <div class="col-md-6">
        
        <p>
          Aspiramos a convertirnos en la herramienta digital más confiable para la gestión integral de negocios locales,
          impulsando la transformación digital de los emprendedores y fomentando el crecimiento económico de nuestra región.
        </p>
      </div>
      <div class="col-md-6 text-center">
        <img src="img/vision.png" alt="Visión" class="img-fluid" style="max-width: 350px;">
      </div>
    </div>

   

    
  </main>

  <?php include 'footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
