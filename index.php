<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Stocky | Organiza tu negocio y tus ventas en un solo lugar</title>
    <meta name="description"
        content="Stocky te ayuda a gestionar productos, ventas, inventario, pedidos y reportes desde un solo lugar.">

    <link rel="icon" type="image/png" href="img/favicon.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="landing.css">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg stocky-navbar fixed-top">
        <div class="container">

            <a class="navbar-brand d-flex align-items-center gap-2" href="#inicio">
                <img src="img/favicon.png" alt="Stocky" class="brand-logo">
                <span>STOCKY</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarStocky"
                aria-controls="navbarStocky" aria-expanded="false" aria-label="Abrir navegación">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarStocky">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="#inicio">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#funciones">Funciones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#modos">¿Cómo usar Stocky?</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#precios">Precios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contacto">Contacto</a>
                    </li>
                </ul>

                <div class="d-flex gap-2">
                    <a href="login.php" class="btn btn-login">Iniciar sesión</a>
                    <a href="#precios" class="btn btn-primary-stocky">Comenzar</a>
                </div>
            </div>
        </div>
    </nav>


    <!-- HERO -->
    <section id="inicio" class="hero-section">
        <div class="container">
            <div class="row align-items-center g-5">

                <div class="col-lg-6">
                    <div class="hero-badge">
                        <span class="badge-dot"></span>
                        Pensado para emprendedores y pequeños negocios
                    </div>

                    <h1>
                        Organiza tu negocio
                        <span>y tus ventas en un solo lugar.</span>
                    </h1>

                    <p class="hero-text">
                        Stocky te ayuda a dejar atrás el papel y tener una visión más clara de lo
                        que ocurre en tu negocio. Organiza tus productos, ventas,
                        inventario, pedidos y reportes desde un solo lugar.
                    </p>

                    <div class="hero-actions">
                        <a href="https://wa.me/573013620090?text=Hola%20Stocky%2C%20quiero%20conocer%20la%20aplicaci%C3%B3n."
                            class="btn btn-primary" target="_blank">
                            Comienza aquí
                        </a>

                        <a href="https://wa.me/573013626090?text=Hola%20Stocky%2C%20quiero%20conocer%20la%20aplicaci%C3%B3n."
                            target="_blank" rel="noopener noreferrer" class="btn btn-whatsapp btn-lg">
                            Contáctame por WhatsApp
                        </a>
                    </div>

                    <div class="hero-trust">
                        <div>
                            <strong>Fácil</strong>
                            <span>de utilizar</span>
                        </div>
                        <div>
                            <strong>Organizado</strong>
                            <span>y centralizado</span>
                        </div>
                        <div>
                            <strong>Adaptable</strong>
                            <span>a tu negocio</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="dashboard-preview">

                        <div class="preview-topbar">
                            <div class="preview-brand">
                                <img src="img/favicon.png" alt="">
                                <span>Stocky</span>
                            </div>
                            <span class="preview-user">Mi negocio</span>
                        </div>

                        <div class="preview-body">
                            <div class="preview-title">
                                <div>
                                    <small>Panel principal</small>
                                    <h4>Bienvenido a Stocky</h4>
                                </div>
                                <span class="preview-status">Activo</span>
                            </div>

                            <div class="preview-cards">
                                <div class="preview-card">
                                    <span class="preview-icon">📦</span>
                                    <small>Productos</small>
                                    <strong>124</strong>
                                </div>

                                <div class="preview-card">
                                    <span class="preview-icon">🛒</span>
                                    <small>Ventas</small>
                                    <strong>38</strong>
                                </div>

                                <div class="preview-card">
                                    <span class="preview-icon">📊</span>
                                    <small>Reportes</small>
                                    <strong>12</strong>
                                </div>
                            </div>

                            <div class="preview-chart">
                                <div class="chart-header">
                                    <span>Actividad reciente</span>
                                    <span>Últimos días</span>
                                </div>

                                <div class="fake-chart">
                                    <span style="height: 35%"></span>
                                    <span style="height: 55%"></span>
                                    <span style="height: 45%"></span>
                                    <span style="height: 75%"></span>
                                    <span style="height: 60%"></span>
                                    <span style="height: 88%"></span>
                                    <span style="height: 72%"></span>
                                </div>
                            </div>

                            <div class="preview-row">
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <!-- FUNCIONES -->
    <section id="funciones" class="section-padding">
        <div class="container">

            <div class="section-heading">
                <span class="section-label">TODO LO QUE NECESITAS</span>
                <h2>Tu negocio bajo control</h2>
                <p>
                    Centraliza las tareas importantes de tu negocio y deja de depender
                    de hojas de cálculo, notas o procesos desordenados.
                </p>
            </div>

            <div class="row g-4">

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">📦</div>
                        <h3>Inventario</h3>
                        <p>
                            Registra tus insumos, controla existencias y mantén
                            organizada la información de tu inventario.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">🛒</div>
                        <h3>Ventas</h3>
                        <p>
                            Registra tus ventas de forma rápida y conserva un historial
                            organizado de tus operaciones.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">📊</div>
                        <h3>Reportes</h3>
                        <p>
                            Consulta la información de tu negocio y genera reportes
                            para tomar mejores decisiones.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">🍽️</div>
                        <h3>Pedidos</h3>
                        <p>
                            Organiza los pedidos de tus clientes y gestiona fácilmente
                            sus diferentes estados.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">📋</div>
                        <h3>Productos y recetas</h3>
                        <p>
                            Define tus productos y, cuando lo necesites, relaciona
                            los insumos utilizados en cada preparación.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">📄</div>
                        <h3>Documentos PDF</h3>
                        <p>
                            Obtén informes y documentos listos para consultar,
                            guardar o compartir.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <!-- MODOS -->
    <section id="modos" class="modes-section section-padding">
        <div class="container">

            <div class="section-heading">
                <span class="section-label">FLEXIBLE PARA TU NEGOCIO</span>
                <h2>Elige cómo quieres trabajar</h2>
                <p>
                    No todos los negocios funcionan de la misma manera.
                    Por eso Stocky se adapta a diferentes necesidades.
                </p>
            </div>

            <div class="row g-4">

                <div class="col-lg-4">
                    <div class="mode-card mode-controlled">
                        <div class="mode-number">01</div>
                        <div class="mode-icon">📦</div>
                        <h3>Modo Controlado</h3>
                        <p>
                            Para negocios que necesitan controlar sus insumos,
                            recetas y existencias.
                        </p>

                        <ul>
                            <li>Control de inventario</li>
                            <li>Recetas por producto</li>
                            <li>Descuento automático de insumos</li>
                            <li>Cálculo de productos disponibles</li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="mode-card mode-simple">
                        <div class="mode-number">02</div>
                        <div class="mode-icon">✨</div>
                        <h3>Modo Simple</h3>
                        <p>
                            Para negocios que quieren registrar productos y ventas
                            sin controlar automáticamente el stock.
                        </p>

                        <ul>
                            <li>Productos y ventas</li>
                            <li>Registro de insumos</li>
                            <li>Sin descuento automático de stock</li>
                            <li>Operación sencilla</li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="mode-card mode-orders">
                        <div class="mode-number">03</div>
                        <div class="mode-icon">🍽️</div>
                        <h3>Modo Pedidos</h3>
                        <p>
                            Pensado para restaurantes, cafeterías y negocios
                            que trabajan principalmente con pedidos.
                        </p>

                        <ul>
                            <li>Productos</li>
                            <li>Registro de pedidos</li>
                            <li>Estados de pedido</li>
                            <li>Gestión de cocina y meseros</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <!-- CTA -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-box">
                <div>
                    <span class="section-label">EMPIEZA A ORGANIZARTE</span>
                    <h2>Haz que administrar tu negocio sea más sencillo.</h2>
                    <p>
                        Lleva la información de tu negocio a un solo lugar y trabaja
                        con mayor claridad todos los días.
                    </p>
                </div>

                <a href="login.php" class="btn btn-light btn-lg">
                    Entrar a Stocky
                </a>
            </div>
        </div>
    </section>


    <!-- CÓMO EMPEZAR -->
    <section id="como-empezar" class="how-section section-padding">
        <div class="container">
            <div class="section-heading">
                <span class="section-label">COMIENZA DE FORMA SENCILLA</span>
                <h2>¿Cómo empezar con Stocky?</h2>
                <p>Por ahora la activación se realiza directamente contigo para acompañarte durante el inicio.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h3>Contáctanos</h3>
                        <p>Déjanos tus datos o escríbenos directamente por WhatsApp.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h3>Realiza el pago</h3>
                        <p>Te indicaremos el proceso de pago y resolveremos tus dudas.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h3>Recibe tu acceso</h3>
                        <p>Una vez confirmado el pago, habilitaremos tu usuario.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PRECIOS -->
    <section id="precios" class="section-padding">
        <div class="container">

            <div class="section-heading">
                <span class="section-label">PRECIO SIMPLE</span>
                <h2>Todo Stocky por un solo precio</h2>
                <p>
                    Accede a las herramientas disponibles de Stocky y a las nuevas
                    funciones que se incorporen, sin pagar más por cada nueva función.
                </p>
            </div>

            <div class="pricing-card">

                <div class="pricing-content">
                    <span class="pricing-badge">PLAN STOCKY</span>
                    <h3>Stocky</h3>
                    <p class="pricing-description">
                        Todas las herramientas que necesitas para comenzar
                        a organizar tu negocio.
                    </p>

                    <div class="price">
                        <span class="price-currency">$</span><span class="price-number">40.000</span><span
                            class="price-currency">COP</span>
                    </div>

                    <a href="#contacto" class="btn btn-primary-stocky btn-lg w-100">
                        Quiero Stocky
                    </a>
                </div>

                <div class="pricing-features">
                    <h4>Incluye:</h4>

                    <div class="pricing-feature">✓ Gestión de productos</div>
                    <div class="pricing-feature">✓ Ventas y pedidos</div>
                    <div class="pricing-feature">✓ Inventario</div>
                    <div class="pricing-feature">✓ Recetas y materiales</div>
                    <div class="pricing-feature">✓ Reportes y documentos PDF</div>
                    <div class="pricing-feature">✓ Usuarios y roles</div>
                    <div class="pricing-feature">✓ Los tres modos de Stocky</div>
                    <div class="pricing-feature">✓ Nuevas funciones que se incorporen</div>
                </div>

            </div>
        </div>
    </section>


    <!-- =========================================================
     SECCIÓN DE CONTACTO - DESACTIVADA TEMPORALMENTE
     
     Se conserva el código para implementarlo posteriormente
     cuando se configure el sistema de envío de correos.
     
     NO ELIMINAR.
========================================================= -->

    <!--
    <section id="contacto" class="contact-section section-padding">
        <div class="container">

            <div class="row g-5 align-items-center">

                <div class="col-lg-5">
                    <span class="section-label">COMIENZA AQUÍ</span>
                    <h2>Cuéntanos sobre tu negocio.</h2>
                    <p>
                        Déjanos tus datos y nos pondremos en contacto contigo para
                        explicarte cómo funciona Stocky, indicarte el proceso de pago
                        y ayudarte a comenzar.
                    </p>

                    <div class="contact-info">
                        <a href="https://wa.me/573013626090?text=Hola%20Stocky%2C%20quiero%20informaci%C3%B3n%20sobre%20la%20aplicaci%C3%B3n."
                            target="_blank" rel="noopener noreferrer" class="contact-item contact-whatsapp">
                            <span>💬</span>
                            <div>
                                <strong>Contáctame por WhatsApp</strong>
                                <small>Escríbeme directamente y te atenderé personalmente.</small>
                            </div>
                        </a>
                        <div class="contact-item">
                            <span>🔒</span>
                            <div>
                                <strong>Activación personalizada</strong>
                                <small>Primero hablamos, realizas el pago y luego habilitamos tu usuario.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="contact-card">
                        <div id="form-message" class="form-message" style="display:none;"></div>
                        <form id="contactForm" action="solicitud_contacto.php" method="POST">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" id="nombre" class="form-control" placeholder="Tu nombre">
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Correo</label>
                                    <input type="email" id="email" class="form-control" placeholder="tu@correo.com">
                                </div>

                                <div class="col-md-6">
                                    <label for="negocio" class="form-label">Nombre de tu negocio</label>
                                    <input type="text" id="negocio" name="negocio" class="form-control"
                                        placeholder="Ej. Mi Cafetería" maxlength="120" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="telefono" class="form-label">WhatsApp / Teléfono</label>
                                    <input type="tel" id="telefono" name="telefono" class="form-control"
                                        placeholder="300 000 0000" maxlength="30" required>
                                </div>

                                <div class="col-12">
                                    <label for="tipo_negocio" class="form-label">¿Qué tipo de negocio tienes?</label>
                                    <select id="tipo_negocio" name="tipo_negocio" class="form-select" required>
                                        <option value="">Selecciona una opción</option>
                                        <option value="Tienda / ventas">Tienda / ventas</option>
                                        <option value="Restaurante">Restaurante</option>
                                        <option value="Cafetería">Cafetería</option>
                                        <option value="Panadería">Panadería</option>
                                        <option value="Emprendimiento">Emprendimiento</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="mensaje" class="form-label">Cuéntame brevemente sobre tu negocio</label>
                                    <textarea id="mensaje" name="mensaje" class="form-control" rows="4" maxlength="1000"
                                        placeholder="Cuéntame qué necesitas organizar en tu negocio."
                                        required></textarea>
                                </div>

                                <div class="col-12">
                                    <button type="submit" id="submitContact" class="btn btn-primary-stocky">
                                        Enviar mis datos
                                    </button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>
-->

    <!-- =========================================================
     FIN SECCIÓN DE CONTACTO
========================================================= -->

    <!-- FOOTER -->
    <footer class="stocky-footer">
        <div class="container">

            <div class="footer-main">
                <div class="footer-brand">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <img src="img/favicon.png" alt="Stocky" class="footer-logo">
                        <strong>STOCKY</strong>
                    </div>

                    <p>
                        Organiza tu negocio y tus ventas en un solo lugar.
                    </p>
                </div>

                <div class="footer-links">
                    <h5>Stocky</h5>
                    <a href="#funciones">Funciones</a>
                    <a href="#modos">Modos</a>
                    <a href="#precios">Precios</a>
                    <a href="#contacto">Contacto</a>
                </div>

                <div class="footer-links">
                    <h5>Cuenta</h5>
                    <a href="login.php">Iniciar sesión</a>
                    <a href="#precios">Comenzar</a>
                </div>
            </div>

            <div class="footer-bottom">
                <span>© <?= date('Y') ?> Stocky. Todos los derechos reservados.</span>
                <span>Hecho para pequeños negocios.</span>
            </div>

        </div>
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById('contactForm').addEventListener('submit', async function (event) {
            event.preventDefault();
            const form = this;
            const button = document.getElementById('submitContact');
            const message = document.getElementById('form-message');

            button.disabled = true;
            button.textContent = 'Enviando...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();

                message.textContent = data.message || 'Solicitud procesada.';
                message.className = data.success ? 'form-message success' : 'form-message error';
                message.style.display = 'block';

                if (data.success) form.reset();
            } catch (error) {
                message.textContent = 'No fue posible enviar la solicitud. Puedes contactarme directamente por WhatsApp.';
                message.className = 'form-message error';
                message.style.display = 'block';
            }

            button.disabled = false;
            button.textContent = 'Enviar mis datos';
        });
    </script>

</body>

</html>