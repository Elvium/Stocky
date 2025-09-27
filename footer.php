<!-- Footer -->
<footer class="footer-custom text-white text-center py-3">
  <div class="container">
    <p class="mb-1">© 2025 Stocky. Todos los derechos reservados.</p>
    <small class="d-block">Desarrollado con 💻 por tu equipo de confianza</small>
    <small class="d-block">
      <a href="acercade.php" class="text-white text-decoration-underline">
        Acerca de Nosotros
      </a>
    </small>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script para formatear inputs numéricos -->
<?php
if (isset($conexion) && $conexion instanceof mysqli) {
    $conexion->close();
}
?>