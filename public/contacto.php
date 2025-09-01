<?php
session_start();
include('conexion.php');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - Tienda Online</title>
    <link rel="icon" type="image/webp" href="logos/icono.webp">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-custom {
            background-color: #001f3f;
        }
        .navbar-custom .nav-link, .navbar-custom .navbar-brand {
            color: #ffffff;
        }
        .navbar-custom .nav-link:hover {
            color: #ffcc00;
        }
        .navbar-custom .navbar-brand {
            color: #ffcc00;
        }
        .navbar-custom .navbar-brand:hover {
            color: #ffff00;
        }
        .navbar-custom .navbar-nav .nav-item {
            margin-right: 15px;
        }
        .navbar-custom .nav-item .icono {
            margin-right: 5px;
        }
        .navbar-custom .search-bar {
            margin-left: auto;
            margin-right: 15px;
        }
        .navbar-custom .social-icons {
            display: flex;
            align-items: center;
        }
        .navbar-custom .social-icons a {
            margin-right: 10px;
            color: #ffffff;
        }
        .navbar-custom .social-icons a:hover {
            color: #25D366;
        }
        .navbar-toggler {
            border-color: white;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='white' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }
        .container-custom {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .section-title {
            margin-bottom: 20px;
            color: #343a40;
            font-size: 2rem;
            font-weight: bold;
        }
        .section-content p {
            margin: 10px 0;
            font-size: 1rem;
            line-height: 1.6;
        }
        .form-label {
            font-weight: bold;
        }
        .form-control {
            border-radius: 8px;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
        footer {
            background-color: #001f3f;
            color: #ffffff;
            padding: 20px 0;
            margin-top: 40px; /* Espacio entre el contenido principal y el footer */
        }
        footer .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        footer .footer-section {
            margin-bottom: 20px;
        }
        footer .footer-section h5 {
            margin-bottom: 10px;
        }
        footer .footer-section p {
            margin: 5px 0;
        }
        footer .map-container {
            height: 200px;
            width: 100%;
        }
        .footer-icons a {
            color: #ffffff;
            margin-right: 10px;
        }
        .footer-icons a:hover {
            color: #ffcc00;
        }
    </style>
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><h3><b><i>Tienda Online</i></b></h3></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="index.php">Productos</a></li>
                    <li class="nav-item"><a class="nav-link" href="empresa.php">Empresa</a></li>
                    <li class="nav-item"><a class="nav-link" href="asesores.php">Asesores</a></li>
                    <li class="nav-item"><a class="nav-link" href="sucursales.php">Sucursales</a></li>
                    <li class="nav-item"><a class="nav-link" href="contacto.php">Contacto</a></li>
                </ul>

                <!-- Barra de búsqueda -->
                <form class="search-bar d-flex" method="get" action="index.php">
                    <input class="form-control me-2" type="search" name="q" placeholder="Buscar..." aria-label="Buscar">
                    <button class="btn btn-outline-light" type="submit"><i class="fas fa-search"></i></button>
                </form>


                <!-- Usuario logueado -->
                <div class="social-icons">
                    <a href="https://wa.me/595983490370?text=Hola%2C+quiero+hacer+una+consulta" class="nav-link"><i class="fab fa-whatsapp"></i> Chat Online</a>
                    
                    <a href="carrito.php" class="nav-link">
                        <i class="fas fa-shopping-cart icono"></i> Mi Carrito [<?= isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : '0' ?>]
                    </a>

                    <!-- Reemplazo del icono de WhatsApp por imagen del usuario -->
                    <?php if (isset($_SESSION['usu_nombre'])): ?>
                        <a href="usuario.php" class="nav-link d-flex align-items-center">
                            <img src="../public/logos/<?= htmlspecialchars($_SESSION['usu_imagen']) ?>" alt="Perfil" class="rounded-circle" style="width: 30px; height: 30px; object-fit: cover; margin-right: 5px;">
                            <span><?= htmlspecialchars($_SESSION['usu_apellido']) ?><BR>CERRAR SESIÓN</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido de la página de contacto -->
    <div class="container-custom">
        <h1 class="section-title">Contáctanos</h1>
        <p>Por favor complete y envíe el formulario a continuación. Le responderemos a la brevedad posible.</p>
        <p>También puede ponerse en contacto con nuestra sucursal más cercana haciendo clic <a href="#">aquí</a>.</p>
        <form>
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre y Apellido:</label>
                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre y Apellido">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Email">
            </div>
            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono:</label>
                <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="Teléfono">
            </div>
            <div class="mb-3">
                <label for="motivo" class="form-label">Motivo:</label>
                <textarea class="form-control" id="motivo" name="motivo" rows="3" placeholder="Motivo"></textarea>
            </div>
            <div class="mb-3">
                <label for="comentarios" class="form-label">Comentarios:</label>
                <textarea class="form-control" id="comentarios" name="comentarios" rows="5" placeholder="Comentarios"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Enviar mensaje</button>
        </form>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 footer-section">
                    <h5>Contacto</h5>
                    <p>Teléfono: +123 456 789</p>
                    <p>Correo: contacto@tiendaonline.com</p>
                </div>
                <div class="col-md-4 footer-section">
                    <h5>Dirección</h5>
                    <p>Av. Principal 123, Ciudad, País</p>
                    <p>Sucursal 1: Av. Secundaria 456</p>
                    <p>Sucursal 2: Av. Terciaria 789</p>
                </div>
                <div class="col-md-4 footer-section">
                    <h5>Ubicación</h5>
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3153.9495588743705!2d-122.41941548468149!3d37.77492927975958!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8085808b6bfc87ff%3A0x6d29e2d51db8b4d3!2sSan%20Francisco%2C%20CA!5e0!3m2!1sen!2sus!4v1603679318810!5m2!1sen!2sus" width="100%" height="100%" frameborder="0" style="border:0;" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <p>&copy; 2024 Tienda Online - Todos los derechos reservados</p>
                <div class="footer-icons">
                    <a href="https://www.facebook.com/tiendaonline" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://twitter.com/tiendaonline" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.instagram.com/tiendaonline" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
