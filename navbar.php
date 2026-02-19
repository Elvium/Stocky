<nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-custom">
  <div class="container-fluid">
    <!-- Logo sin texto -->
    <a class="navbar-brand" href="dashboard.php">
      <img src="Logo.PNG" alt="Stocky Logo" height="40" class="d-inline-block align-text-top">
    </a>

    <!-- Botón responsive -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" 
            aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <!-- Menú a la izquierda -->
      <ul class="navbar-nav me-auto">
        
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Inicio</a></li>
          <li class="nav-item"><a class="nav-link" href="instrucciones.php">Como usar tu Stocky</a></li>
          <li class="nav-item"><a class="nav-link" href="contacto.php">Contacto</a></li>
        
      </ul>

      <!-- Botón a la derecha -->
      <a href="dashboard.php?logout=1" class="btn btn-danger btn-sm">Cerrar sesión</a>
    </div>
  </div>
</nav>
