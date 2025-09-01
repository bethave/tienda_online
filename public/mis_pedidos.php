<?php
session_start();
require_once('conexion.php');

if (!isset($_SESSION['usu_nombre'])) {
    header('Location: login.php');
    exit();
}

$usu_nombre = $_SESSION['usu_nombre'];

// Obtener cli_id
$stmt = $pdo->prepare("SELECT cli_id FROM v_usuario WHERE usu_nombre = :usu_nombre LIMIT 1");
$stmt->execute([':usu_nombre' => $usu_nombre]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die("Usuario no encontrado");
}

$cli_id = $usuario['cli_id'];

// Obtener pedidos del cliente
$stmt = $pdo->prepare("
    SELECT ped_id, ped_fecha, ped_estado 
    FROM pedido_cliente 
    WHERE cli_id = :cli_id 
    ORDER BY ped_fecha DESC
");
$stmt->execute([':cli_id' => $cli_id]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener detalles de pedidos junto con nombre producto
$detallesPedidos = [];
if ($pedidos) {
    $idsPedidos = array_column($pedidos, 'ped_id');
    $in  = str_repeat('?,', count($idsPedidos) - 1) . '?';
    $stmtDet = $pdo->prepare("
        SELECT d.ped_id, d.prod_cod, d.pc_cantidad, d.pc_montototal, p.prod_descri
        FROM pedido_cli_detalle d
        INNER JOIN productos p ON d.prod_cod = p.prod_cod
        WHERE d.ped_id IN ($in)
        ORDER BY d.ped_id, p.prod_descri
    ");
    $stmtDet->execute($idsPedidos);
    $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);
    foreach ($detalles as $d) {
        $detallesPedidos[$d['ped_id']][] = $d;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Pedidos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="logos/icono.webp">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f3f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        h3 {
            color: #0d6efd;
            font-weight: 700;
        }
        .accordion-button {
            font-weight: 600;
            font-size: 1.1rem;
        }
        .accordion-button:not(.collapsed) {
            color: #0d6efd;
            background-color: #e9f2ff;
            box-shadow: inset 0 -1px 0 rgba(0,0,0,.125);
        }
        .badge-status {
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.35em 0.75em;
            border-radius: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .badge-success {
            background-color: #198754;
            color: #fff;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-secondary {
            background-color: #6c757d;
            color: #fff;
        }
        .table thead {
            background-color: #0d6efd;
            color: #fff;
        }
        .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: rgba(13, 110, 253, 0.1);
        }
        .total-row th {
            background-color: #e9f2ff;
            font-weight: 700;
        }
        .btn-back {
            background: linear-gradient(90deg, #0d6efd 0%, #6610f2 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(13,110,253,.4);
            transition: background 0.3s ease;
            color: white;
        }
        .btn-back:hover {
            background: linear-gradient(90deg, #6610f2 0%, #0d6efd 100%);
            color: white;
        }
        footer {
            margin-top: 60px;
            padding: 15px 0;
            background-color: #0d6efd;
            color: white;
            text-align: center;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<div class="container my-5">
    <h3 class="text-center mb-4"><i class="bi bi-box-seam me-2"></i>Mis Pedidos</h3>

    <?php if (!$pedidos): ?>
        <div class="alert alert-info text-center shadow-sm">No tienes pedidos realizados.</div>
    <?php else: ?>
        <div class="accordion shadow-sm rounded" id="accordionPedidos">
            <?php foreach ($pedidos as $index => $pedido): ?>
                <?php
                    $estado = htmlspecialchars($pedido['ped_estado']);
                    $badgeClass = 'badge-secondary';
                    if (strtolower($estado) === 'PAGADO') {
                        $badgeClass = 'badge-success';
                    } elseif (strtolower($estado) === 'PENDIENTE') {
                        $badgeClass = 'badge-warning';
                    }
                ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?= $pedido['ped_id'] ?>">
                        <button class="accordion-button <?= $index !== 0 ? 'collapsed' : '' ?>" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse<?= $pedido['ped_id'] ?>" 
                                aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" 
                                aria-controls="collapse<?= $pedido['ped_id'] ?>">
                            Pedido #<?= htmlspecialchars($pedido['ped_id']) ?>
                            <small class="text-muted ms-3"><?= date('d/m/Y', strtotime($pedido['ped_fecha'])) ?></small>
                            <span class="badge badge-status <?= $badgeClass ?> ms-3"><?= $estado ?></span>
                            <i class="bi bi-chevron-down ms-auto"></i>
                        </button>
                    </h2>
                    <div id="collapse<?= $pedido['ped_id'] ?>" 
                         class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" 
                         aria-labelledby="heading<?= $pedido['ped_id'] ?>" 
                         data-bs-parent="#accordionPedidos">
                        <div class="accordion-body p-0">
                            <?php if (!empty($detallesPedidos[$pedido['ped_id']])): ?>
                                <table class="table table-striped m-0">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-end">Precio Unitario (Gs.)</th>
                                            <th class="text-end">Subtotal (Gs.)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $totalPedido = 0;
                                        foreach ($detallesPedidos[$pedido['ped_id']] as $detalle):
                                            $subtotal = $detalle['pc_montototal'];
                                            $totalPedido += $subtotal;
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($detalle['prod_descri']) ?></td>
                                            <td class="text-center"><?= intval($detalle['pc_cantidad']) ?></td>
                                            <td class="text-end"><?= number_format($detalle['pc_montototal'] / $detalle['pc_cantidad'], 0, ',', '.') ?></td>
                                            <td class="text-end"><?= number_format($subtotal, 0, ',', '.') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="total-row">
                                            <th colspan="3" class="text-end">Total Pedido:</th>
                                            <th class="text-end"><?= number_format($totalPedido, 0, ',', '.') ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            <?php else: ?>
                                <div class="p-4 text-center text-muted fst-italic">No hay detalles para este pedido.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-back btn-lg px-5">
            <i class="bi bi-arrow-left me-2"></i> Volver a la tienda
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
