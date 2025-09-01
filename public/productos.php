<?php
session_start();
include('conexion.php');

// Obtener productos de la vista v_productos
$sql = "SELECT * FROM v_productos WHERE prod_estado = 'ACTIVO' AND mar_estado = 'ACTIVO' AND mod_estado = 'ACTIVO' AND iva_estado = 'ACTIVO'";
$stmt = $pdo->query($sql);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agregar producto al carrito
if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id'])) {
    $id = $_GET['id'];
    if (!isset($_SESSION['carrito'][$id])) {
        $_SESSION['carrito'][$id] = 1;
    } else {
        $_SESSION['carrito'][$id]++;
    }
    header('Location: index.php');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1 class="my-4 text-center">Master Shop</h1>

        <div class="row">
            <!-- Mostrar los productos -->
            <?php foreach ($productos as $producto): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100">
                        <!-- Mostrar la imagen desde la base de datos -->
                        <img class="card-img-top" src="<?= $producto['prod_img'] ?>" alt="<?= $producto['prod_descri'] ?>">
                        <div class="card-body">
                            <h4 class="card-title"><?= $producto['prod_descri'] ?></h4>
                            <h5>Precio Venta: GS. <?= number_format($producto['prod_venta']) ?></h5>
                            <!--<p class="card-text">Código Producto: <?= $producto['prod_cod'] ?></p>-->
                            <p class="card-text">Marca: <?= $producto['mar_descri'] ?></p>
                            <p class="card-text">Modelo: <?= $producto['mod_descri'] ?></p>
                           <!-- <p class="card-text">Tipo IVA: <?= $producto['iva_tipo'] ?>%</p>-->
                        </div>
                        <div class="card-footer">
                            <a href="index.php?action=add&id=<?= $producto['prod_cod'] ?>" class="btn btn-primary">
                                <i class="fas fa-cart-plus"></i> Añadir al Carrito
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Botón para ver el carrito -->
        <a href="carrito.php" class="btn btn-success"><i class="fas fa-shopping-cart"></i> Ver Carrito</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
