<?php
// Iniciar sesión para usar tokens CSRF
session_start();

// Configuración de la base de datos
$host = 'localhost';
$port = '5433';
$dbname = 'proyecto1';
$user = 'postgres';
$password = '123';

// Crear conexión
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

// Verificar la conexión
if (!$conn) {
    die('Error en la conexión a la base de datos.');
}

// Inicializar variables
$success_message = '';
$error_message = '';
$prod_cod = '';
$prod_descri = '';
$prod_venta = '';
$prod_compra = '';
$mar_cod = '';
$mod_cod = '';
$iva_cod = '';
$tp_cod = '';
$prod_img = '';
$prod_estado = '';

// Obtener el siguiente código de producto
$result = pg_query($conn, 'SELECT COALESCE(MAX(prod_cod), 0) + 1 AS next_id FROM productos');
if ($result) {
    $row = pg_fetch_assoc($result);
    $prod_cod = $row['next_id'];
} else {
    $error_message = 'Error al obtener el código de producto.';
}

// Obtener las marcas, modelos, IVA y tipos de productos para los desplegables
$marcas_result = pg_query($conn, 'SELECT mar_cod, mar_descri FROM marca WHERE mar_estado = \'ACTIVO\'');
$marcas = [];
$modulos_result = pg_query($conn, 'SELECT mod_cod, mod_descri FROM modelo WHERE mod_estado = \'ACTIVO\'');
$modulos = [];
$iva_result = pg_query($conn, 'SELECT iva_cod, iva_tipo FROM iva WHERE iva_estado = \'ACTIVO\'');
$ivas = [];
$tipos_result = pg_query($conn, 'SELECT tp_cod, tp_descri FROM tipo_producto WHERE tp_estado = \'ACTIVO\'');
$tipos = [];

if ($marcas_result && $modulos_result && $iva_result && $tipos_result) {
    while ($row = pg_fetch_assoc($marcas_result)) {
        $marcas[] = $row;
    }
    while ($row = pg_fetch_assoc($modulos_result)) {
        $modulos[] = $row;
    }
    while ($row = pg_fetch_assoc($iva_result)) {
        $ivas[] = $row;
    }
    while ($row = pg_fetch_assoc($tipos_result)) {
        $tipos[] = $row;
    }
} else {
    $error_message = 'Error al obtener datos de marcas, modelos, IVA o tipos.';
}

// Generar un token CSRF para el formulario
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Procesar el formulario si se ha enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar el token CSRF
    if (!hash_equals($_SESSION['token'], $_POST['token'])) {
        die('Error: Token de formulario no válido.');
    }

    // Capturar los valores del formulario
    $prod_descri = trim($_POST['prod_descri']);
    $prod_venta = trim($_POST['prod_venta']);
    $prod_compra = trim($_POST['prod_compra']);
    $mar_cod = trim($_POST['mar_cod']);
    $mod_cod = trim($_POST['mod_cod']);
    $iva_cod = trim($_POST['iva_cod']);
    $tp_cod = trim($_POST['tp_cod']);
    $prod_estado = 'ACTIVO'; // Estado por defecto

    // Manejar la subida de imagen
    if (isset($_FILES['prod_img']) && $_FILES['prod_img']['error'] === UPLOAD_ERR_OK) {
        $prod_img = basename($_FILES['prod_img']['name']);
        $target_dir = "uploads/"; // Directorio donde se guardará la imagen
        $target_file = $target_dir . $prod_img;

        // Comprobar si el directorio existe, si no, crearlo
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Mover el archivo a la carpeta de destino
        if (move_uploaded_file($_FILES['prod_img']['tmp_name'], $target_file)) {
            // La imagen se ha subido exitosamente
        } else {
            $error_message = 'Error al subir la imagen.';
        }
    } else {
        $error_message = 'No se ha seleccionado ninguna imagen.';
    }

    // Validar datos
    if (empty($prod_descri) || empty($prod_venta) || empty($prod_compra) || empty($mar_cod) || empty($mod_cod) || empty($iva_cod) || empty($tp_cod)) {
        $error_message = 'Todos los campos son obligatorios.';
    } else {
        // Preparar la consulta SQL para insertar datos
        $query = 'INSERT INTO productos (prod_cod, prod_descri, prod_venta, prod_compra, mar_cod, mod_cod, iva_cod, tp_cod, prod_estado, prod_img) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)';
        $result = pg_query_params($conn, $query, array($prod_cod, $prod_descri, $prod_venta, $prod_compra, $mar_cod, $mod_cod, $iva_cod, $tp_cod, $prod_estado, $prod_img));

        if ($result) {
            $success_message = 'Producto agregado exitosamente.';
            // Reiniciar el formulario después de agregar
            $prod_descri = '';
            $prod_venta = '';
            $prod_compra = '';
            $mar_cod = '';
            $mod_cod = '';
            $iva_cod = '';
            $tp_cod = '';
            $prod_img = '';

            // Obtener el siguiente código de producto
            $result = pg_query($conn, 'SELECT COALESCE(MAX(prod_cod), 0) + 1 AS next_id FROM productos');
            $row = pg_fetch_assoc($result);
            $prod_cod = $row['next_id'];

            // Redireccionar para evitar reenvíos accidentales al refrescar la página
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error_message = 'Error al agregar el producto.';
        }
    }
}

// Cerrar la conexión
pg_close($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../../imagenes/login.png">
    <title>Administrar Productos</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 900px;
            margin-top: 30px;
        }
        .content-container {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 0.5rem;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .header {
            display: flex;
            align-items: center;
            background-color: #e0f7fa;
            padding: 20px;
            border-radius: 0.5rem;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .header img {
            max-height: 60px;
            margin-right: 15px;
        }
        .header h1 {
            font-size: 1.75rem;
            color: #00796b;
            margin: 0;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .btn-custom {
            margin: 5px;
        }
        .alert {
            margin-top: 20px;
        }
        .form-control[disabled] {
            background-color: #e9ecef;
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content-container">
            <!-- Encabezado con el nombre del sistema y el logo -->
            <div class="header">
                <img src="../../imagenes/logo.png" alt="Logo del Sistema">
                <h1>Administrar Productos</h1>
            </div>

            <!-- Botones de Navegación -->
            <div class="mb-3">
                <a href="../../welcome.php" class="btn btn-secondary btn-custom">
                    <i class="fas fa-arrow-left"></i> Volver al Inicio
                </a>
                <a href="producto_listar.php" class="btn btn-warning btn-custom">
                    <i class="fas fa-edit"></i> Editar Producto
                </a>
                <a href="producto_eliminar.php" class="btn btn-danger btn-custom">
                    <i class="fas fa-trash"></i> Eliminar o Reactivar Producto
                </a>
                <a href="producto_detalle.php" class="btn btn-success btn-custom">
                    <i class="fas fa-eye"></i> Ver Detalles
                </a>
            </div>

            <!-- Mensajes de éxito o error -->
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Formulario para agregar productos -->
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                <div class="form-group">
                    <label for="prod_cod">Código del Producto:</label>
                    <input type="text" class="form-control" id="prod_cod" name="prod_cod" value="<?php echo $prod_cod; ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="prod_descri">Descripción:</label>
                    <input type="text" class="form-control" id="prod_descri" name="prod_descri" value="<?php echo $prod_descri; ?>" required>
                </div>
                <div class="form-group">
                    <label for="prod_venta">Precio de Venta:</label>
                    <input type="number" class="form-control" id="prod_venta" name="prod_venta" value="<?php echo $prod_venta; ?>" required>
                </div>
                <div class="form-group">
                    <label for="prod_compra">Precio de Compra:</label>
                    <input type="number" class="form-control" id="prod_compra" name="prod_compra" value="<?php echo $prod_compra; ?>" required>
                </div>
                <div class="form-group">
                    <label for="mar_cod">Marca:</label>
                    <select class="form-control" id="mar_cod" name="mar_cod" required>
                        <option value="">Seleccione una Marca</option>
                        <?php foreach ($marcas as $marca): ?>
                            <option value="<?php echo $marca['mar_cod']; ?>"><?php echo $marca['mar_descri']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="mod_cod">Modelo:</label>
                    <select class="form-control" id="mod_cod" name="mod_cod" required>
                        <option value="">Seleccione un Modelo</option>
                        <?php foreach ($modulos as $modulo): ?>
                            <option value="<?php echo $modulo['mod_cod']; ?>"><?php echo $modulo['mod_descri']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="iva_cod">IVA:</label>
                    <select class="form-control" id="iva_cod" name="iva_cod" required>
                        <option value="">Seleccione un IVA</option>
                        <?php foreach ($ivas as $iva): ?>
                            <option value="<?php echo $iva['iva_cod']; ?>"><?php echo $iva['iva_tipo']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tp_cod">Tipo de Producto:</label>
                    <select class="form-control" id="tp_cod" name="tp_cod" required>
                        <option value="">Seleccione un Tipo de Producto</option>
                        <?php foreach ($tipos as $tipo): ?>
                            <option value="<?php echo $tipo['tp_cod']; ?>"><?php echo $tipo['tp_descri']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="prod_img">Imagen del Producto:</label>
                    <input type="file" class="form-control-file" id="prod_img" name="prod_img" accept="image/*" required>
                </div>
                <button type="submit" class="btn btn-primary">Agregar Producto</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
