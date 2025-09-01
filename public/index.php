<?php
session_start();
include('conexion.php');

// Redirigir si no hay sesión activa
if (!isset($_SESSION['usu_nombre'])) {
    header('Location: login.php');
    exit();
}

// Obtener término de búsqueda (si existe)
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

// Agregar producto al carrito
if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id'])) {
    $id = $_GET['id'];
    if (!isset($_SESSION['carrito'][$id])) {
        $_SESSION['carrito'][$id] = 1;
    } else {
        $_SESSION['carrito'][$id]++;
    }
    header('Location: index.php');
    exit();
}

// Consulta base
$sql = "SELECT * FROM v_productos 
        WHERE prod_estado = 'ACTIVO' 
        AND mar_estado = 'ACTIVO' 
        AND mod_estado = 'ACTIVO' 
        AND iva_estado = 'ACTIVO'";

if (!empty($busqueda)) {
    $sql .= " AND (
        LOWER(prod_descri) LIKE LOWER(:busqueda) OR
        LOWER(mar_descri) LIKE LOWER(:busqueda) OR
        LOWER(mod_descri) LIKE LOWER(:busqueda)
    )";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':busqueda' => "%$busqueda%"]);
} else {
    $stmt = $pdo->query($sql);
}

$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular cantidad total de productos en el carrito
$cantidad_total = 0;
if (isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $cantidad) {
        $cantidad_total += $cantidad;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tienda Online - Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        .navbar-custom .navbar-brand:hover {
            color: #ffff00;
        }
        .navbar-custom .navbar-nav .nav-item {
            margin-right: 15px;
        }
        .navbar-custom .search-bar {
            margin-left: auto;
            margin-right: 15px;
        }
        .navbar-custom .social-icons {
            display: flex;
            align-items: center;
        }
        .navbar-toggler {
            border-color: white;
        }
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='white' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }
        .carousel-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .carousel-item img {
            object-fit: contain;
            width: 100%;
            height: 150px;
        }
        .square-container {
            max-width: 1300px;
            padding: 20px;
            background-color: #f9f9f9;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .productos-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        .producto-card {
            width: 300px;
            padding: 15px;
        }
        .producto-img {
            height: 150px;
            object-fit: contain;
        }
        .producto-titulo {
            text-align: center;
            color: #001f3f;
            margin-bottom: 10px;
        }
        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }
        footer {
            background-color: #001f3f;
            color: #ffffff;
            padding: 20px 0;
            margin-top: 40px;
        }
        .footer-section h5 {
            margin-bottom: 10px;
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

                <form class="search-bar d-flex" method="get" action="index.php">
                    <input class="form-control me-2" type="search" name="q" placeholder="Buscar..." aria-label="Buscar">
                    <button class="btn btn-outline-light" type="submit"><i class="fas fa-search"></i></button>
                </form>

                <!-- Usuario y carrito -->
                <div class="social-icons">
                    <a href="https://wa.me/595983490370?text=Hola%2C+quiero+hacer+una+consulta" class="nav-link"><i class="fab fa-whatsapp"></i> Chat Online</a>

                    <a href="carrito.php" class="nav-link position-relative">
                        <i class="fas fa-shopping-cart icono"></i> Mi Carrito
                        <?php if ($cantidad_total > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $cantidad_total ?>
                                <span class="visually-hidden">productos en el carrito</span>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- Nuevo ícono y enlace Mis Pedidos -->
                    <a href="mis_pedidos.php" class="nav-link position-relative" style="margin-left: 15px;">
                        <i class="fas fa-clipboard-list icono"></i> Mis Pedidos
                        <!-- Si quieres poner algún badge con número de pedidos pendientes, aquí lo puedes agregar -->
                    </a>

                    <?php if (isset($_SESSION['usu_nombre'])): ?>
                        <a href="usuario.php" class="nav-link d-flex align-items-center">
                            <img src="../public/logos/<?= htmlspecialchars($_SESSION['usu_imagen']) ?>" alt="Perfil" class="rounded-circle" style="width: 30px; height: 30px; object-fit: cover; margin-right: 5px;">
                            <span><?= htmlspecialchars($_SESSION['usu_apellido']) ?><br><small>Cerrar sesión</small></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Carrusel -->
    <div class="container mt-4 carousel-container">
        <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active"><img src="logos/bosh.webp" class="d-block w-100" alt="Marca 1"></div>
                <div class="carousel-item"><img src="logos/dewalt.jfif" class="d-block w-100" alt="Marca 2"></div>
                <div class="carousel-item"><img src="logos/fascy.png" class="d-block w-100" alt="Marca 3"></div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
                <span class="visually-hidden">Siguiente</span>
            </button>
        </div>
    </div>

    <!-- Productos -->
    <div class="container mt-5">
        <div class="square-container">
            <h1 class="text-center mb-4">Nuestros Productos</h1>
            <div class="productos-container">
                <?php foreach ($productos as $producto): ?>
                    <div class="card h-100 producto-card">
                        <img class="card-img-top producto-img" src="<?= htmlspecialchars($producto['prod_img']) ?>" alt="<?= htmlspecialchars($producto['prod_descri']) ?>">
                        <div class="card-body">
                            <h5 class="card-title producto-titulo"><b><?= htmlspecialchars($producto['prod_descri']) ?> <?= htmlspecialchars($producto['mar_descri']) ?> <?= htmlspecialchars($producto['mod_descri']) ?></b></h5>
                            <h5 class="producto-precio"><b>Gs. <?= number_format($producto['prod_venta']) ?></b></h5>
                        </div>
                        <div class="card-footer">
                            <a href="?action=add&id=<?= $producto['prod_cod'] ?>" class="btn btn-primary">Añadir al carrito</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
