<?php
session_start();
require_once('conexion.php');

if (!isset($_SESSION['usu_nombre'])) {
    header('Location: login.php');
    exit();
}

$usu_nombre = $_SESSION['usu_nombre'];
$carrito = $_SESSION['carrito'] ?? [];

// AGREGAR UNA UNIDAD
if (isset($_GET['mas'])) {
    $codigo = $_GET['mas'];
    if (isset($carrito[$codigo])) {
        $carrito[$codigo]++;
        $_SESSION['mensaje'] = "✅ Se agregó una unidad más al producto.";
    }
    $_SESSION['carrito'] = $carrito;
    header('Location: carrito.php');
    exit();
}

// QUITAR UNA UNIDAD
if (isset($_GET['menos'])) {
    $codigo = $_GET['menos'];
    if (isset($carrito[$codigo])) {
        $carrito[$codigo]--;
        if ($carrito[$codigo] <= 0) {
            unset($carrito[$codigo]);
            $_SESSION['mensaje'] = "🗑️ Producto eliminado completamente del carrito.";
        } else {
            $_SESSION['mensaje'] = "➖ Una unidad del producto fue eliminada.";
        }
    }
    $_SESSION['carrito'] = $carrito;
    header('Location: carrito.php');
    exit();
}

// ELIMINAR TODO EL PRODUCTO
if (isset($_GET['eliminar_todo'])) {
    $codigo = $_GET['eliminar_todo'];
    if (isset($carrito[$codigo])) {
        unset($carrito[$codigo]);
        $_SESSION['mensaje'] = "❌ Producto eliminado del carrito.";
    }
    $_SESSION['carrito'] = $carrito;
    header('Location: carrito.php?redir=index');
    exit();
}

// Mostrar mensaje y redireccionar si viene de una eliminación
if (isset($_GET['redir']) && $_GET['redir'] === 'index' && isset($_SESSION['mensaje'])) {
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Carrito</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <meta http-equiv="refresh" content="2;url=index.php">
    </head>
    <body>
        <div class="container mt-5">
            <div class="alert alert-info text-center">' . htmlspecialchars($_SESSION['mensaje']) . '<br><small>Redirigiendo a la tienda...</small></div>
        </div>
    </body>
    </html>';
    unset($_SESSION['mensaje']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmar Pedido</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="logos/icono.webp">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f3f5;
        }
        .card-custom {
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        .btn-icon {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-icon i {
            font-size: 1rem;
        }
    </style>
</head>
<body>
<div class="container my-5">
    <div class="card card-custom p-4">
        <h3 class="mb-4 text-center text-primary"><i class="bi bi-cart-check-fill"></i> Confirmar Pedido</h3>

        <?php
        if (isset($_SESSION['mensaje'])) {
            echo '<div class="alert alert-info alert-dismissible fade show text-center" role="alert">'
                . htmlspecialchars($_SESSION['mensaje']) .
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>';
            unset($_SESSION['mensaje']);
        }

        // 🔔 BLOQUE AGREGADO: mostrar mensaje si carrito está vacío
        if (empty($carrito)) {
            echo '<div class="alert alert-warning text-center">
                    🛒 El carrito está vacío.<br>Por favor, añade productos antes de confirmar tu pedido.
                  </div>
                  <div class="text-center mt-3">
                    <a href="index.php" class="btn btn-primary btn-icon">
                        <i class="bi bi-shop"></i> Ir a la tienda
                    </a>
                  </div>
                </div>
            </div>
        </body>
        </html>';
            exit();
        }
        ?>

        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $totalGeneral = 0;
            foreach ($carrito as $prod_cod => $cantidad) {
                $stmt = $pdo->prepare("SELECT prod_descri, prod_venta FROM productos WHERE prod_cod = ?");
                $stmt->execute([$prod_cod]);
                $producto = $stmt->fetch(PDO::FETCH_ASSOC);

                $precio = $producto['prod_venta'];
                $subtotal = $precio * $cantidad;
                $totalGeneral += $subtotal;

                echo "<tr>
                    <td>$prod_cod</td>
                    <td>" . htmlspecialchars($producto['prod_descri']) . "</td>
                    <td>Gs. " . number_format($precio, 0, ',', '.') . "</td>
                    <td><span class='badge bg-secondary fs-6'>$cantidad</span></td>
                    <td>Gs. " . number_format($subtotal, 0, ',', '.') . "</td>
                    <td>
                        <a href='?mas=$prod_cod' class='btn btn-outline-success btn-sm me-1'>
                            <i class='bi bi-plus-circle'></i> Agregar 1
                        </a>
                        <a href='?menos=$prod_cod' class='btn btn-outline-warning btn-sm me-1'>
                            <i class='bi bi-dash-circle'></i> Quitar 1
                        </a>
                        <a href='?eliminar_todo=$prod_cod' class='btn btn-outline-danger btn-sm' onclick=\"return confirm('¿Eliminar este producto completamente del carrito?');\">
                            <i class='bi bi-trash'></i> Eliminar todo
                        </a>
                    </td>
                </tr>";
            }
            ?>
            </tbody>
            <tfoot>
                <tr class="table-light fw-bold">
                    <td colspan="4" class="text-end">Total General:</td>
                    <td colspan="2">Gs. <?= number_format($totalGeneral, 0, ',', '.') ?></td>
                </tr>
            </tfoot>
        </table>

        <?php
        if (!isset($_POST['suc_cod'])) {
            $stmt = $pdo->query("SELECT suc_cod, suc_descri FROM sucursal WHERE suc_estado = 'ACTIVO'");
            $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <div class="card mt-4 p-4 border border-success">
            <h5 class="mb-3 text-success"><i class="bi bi-building"></i> Selecciona una sucursal para continuar</h5>
            <form action="carrito.php" method="post">
                <div class="form-floating mb-3">
                    <select class="form-select" name="suc_cod" id="suc_cod" required>
                        <option value="">-- Elige una sucursal --</option>
                        <?php foreach ($sucursales as $sucursal): ?>
                            <option value="<?= $sucursal['suc_cod'] ?>"><?= htmlspecialchars($sucursal['suc_descri']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="suc_cod">Sucursal</label>
                </div>
                <div class="d-flex justify-content-center">
                    <button type="submit" class="btn btn-success btn-icon d-flex justify-content-center align-items-center" style="width: 300px;">
                        <i class="bi bi-check-circle-fill me-2"></i> Confirmar sucursal y pedido
                    </button>
                </div>
            </form>
        </div>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-outline-primary btn-sm btn-icon">
                <i class="bi bi-house-door-fill"></i> Volver a tienda
            </a>
        </div>
    </div>
</div>
</body>
</html>
<?php
    exit();
}

// PROCESO FINAL DE CONFIRMACION DE PEDIDO
$suc_cod = $_POST['suc_cod'];

$stmt = $pdo->prepare("SELECT cli_id FROM v_usuario WHERE usu_nombre = ?");
$stmt->execute([$usu_nombre]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "<div class='alert alert-danger text-center'>No se pudo obtener el usuario '$usu_nombre'.</div>";
    exit();
}

$cli_id = $usuario['cli_id'];

try {
    $pdo->beginTransaction();

    foreach ($carrito as $prod_cod => $cantidad) {
        $stmt = $pdo->prepare("SELECT st_cantidad_total, st_cantidad_minima FROM stock WHERE prod_cod = ? AND suc_cod = ? AND LOWER(st_estado) = 'activo'");
        $stmt->execute([$prod_cod, $suc_cod]);
        $stock = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$stock || $stock['st_cantidad_total'] < $cantidad || ($stock['st_cantidad_total'] - $cantidad) < $stock['st_cantidad_minima']) {
            throw new Exception("❌ Stock insuficiente o por debajo del mínimo para el producto");
        }
    }

    $stmt = $pdo->query("SELECT COALESCE(MAX(ped_id), 0) + 1 AS nuevo_id FROM pedido_cliente");
    $ped_id = $stmt->fetch(PDO::FETCH_ASSOC)['nuevo_id'];

    $auditoria = "Pedido confirmado por $usu_nombre el " . date('Y-m-d H:i:s') . " en sucursal $suc_cod";
    $stmt = $pdo->prepare("INSERT INTO pedido_cliente (ped_id, ped_fecha, ped_estado, ped_auditoria, cli_id) VALUES (?, CURRENT_DATE, 'PENDIENTE', ?, ?)");
    $stmt->execute([$ped_id, $auditoria, $cli_id]);

    foreach ($carrito as $prod_cod => $cantidad) {
        $stmt = $pdo->prepare("SELECT prod_venta FROM productos WHERE prod_cod = ?");
        $stmt->execute([$prod_cod]);
        $precio = $stmt->fetch(PDO::FETCH_ASSOC)['prod_venta'];
        $total = $precio * $cantidad;

        $stmt = $pdo->prepare("INSERT INTO pedido_cli_detalle (ped_id, prod_cod, pc_cantidad, pc_montototal) VALUES (?, ?, ?, ?)");
        $stmt->execute([$ped_id, $prod_cod, $cantidad, $total]);

        $stmt = $pdo->prepare("UPDATE stock SET st_cantidad_total = st_cantidad_total - ? WHERE prod_cod = ? AND suc_cod = ?");
        $stmt->execute([$cantidad, $prod_cod, $suc_cod]);
    }

    $pdo->commit();
    unset($_SESSION['carrito']);

    echo '<div class="container mt-5 text-center">';
    echo '<div class="alert alert-success fs-4">✅ Pedido confirmado correctamente. <br>Un vendedor se comunicará con Usted.</br></div>';
    echo '<a href="index.php" class="btn btn-primary"><i class="bi bi-shop"></i> Volver a la tienda</a>';
    echo '</div>';

} catch (Exception $e) {
    $pdo->rollBack();
    echo '<div class="container mt-5 text-center">';
    echo '<div class="alert alert-danger fs-5">⚠️ Error al confirmar pedido: ' . $e->getMessage() . '</div>';
    echo '<a href="carrito.php" class="btn btn-warning mt-3"><i class="bi bi-arrow-left-circle"></i> Volver al carrito</a>';
    echo '</div>';
}
?>
