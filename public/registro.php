<?php
// registro.php - conexión y lógica actualizada

// Configuración de la base de datos
$host = 'localhost';
$port = '5433';
$dbname = 'proyecto1';  // Asegúrate que el nombre coincida con tu BD
$user = 'postgres';
$password = '123';

// Conexión a la base de datos
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Error de conexión: " . pg_last_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = strtoupper(trim($_POST['nombre']));
    $apellido = strtoupper(trim($_POST['apellido']));
    $ci = trim($_POST['ci']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $dir_cod = intval($_POST['dir_cod']);

    $nick_pag = trim($_POST['nick_pag']);
    $contrasena = trim($_POST['contrasena']);
    $confirmar_contrasena = trim($_POST['confirmar_contrasena']);

    // Validar campos vacíos
    if (empty($nombre) || empty($apellido) || empty($ci) || empty($email) || empty($telefono) || $dir_cod <= 0 || empty($nick_pag) || empty($contrasena) || empty($confirmar_contrasena)) {
        echo "<script>alert('Por favor, complete todos los campos correctamente.'); window.history.back();</script>";
        exit();
    }

    if ($contrasena !== $confirmar_contrasena) {
        echo "<script>alert('Las contraseñas no coinciden.'); window.history.back();</script>";
        exit();
    }

    // Verificar nickname único
    $consulta_nick = "SELECT 1 FROM usuarios WHERE usu_nombre = $1";
    $resultado_nick = pg_query_params($conn, $consulta_nick, array($nick_pag));
    if (pg_num_rows($resultado_nick) > 0) {
        echo "<script>alert('El nickname ya está registrado.'); window.history.back();</script>";
        exit();
    }

    // Obtener nuevo ID usuario
    $consulta_id = "SELECT COALESCE(MAX(usu_id), 0) + 1 AS nuevo_id FROM usuarios";
    $resultado_id = pg_query($conn, $consulta_id);
    $fila_id = pg_fetch_assoc($resultado_id);
    $nuevo_id = $fila_id['nuevo_id'];

    $nombre_pag = $nombre . ' ' . $apellido;
    $contrasena_hash = md5($contrasena);  // <-- Contraseña en MD5
    $estado = 'ACTIVO';
    $imagen_ruta = 'login.png';  // Ruta por defecto
    $intentos = 0;
    $usu_grupo = 'CLIENTE';

    // Insertar usuario
    $query_usuario = "INSERT INTO usuarios (usu_id, usu_nombre, usu_contrasena, estado, intentos, imagen_ruta, nombre_pag, usu_grupo) 
                      VALUES ($1, $2, $3, $4, $5, $6, $7, $8)";
    $result_usuario = pg_query_params($conn, $query_usuario, array($nuevo_id, $nick_pag, $contrasena_hash, $estado, $intentos, $imagen_ruta, $nombre_pag, $usu_grupo));

    if (!$result_usuario) {
        echo "<script>alert('Error al registrar usuario.'); window.history.back();</script>";
        exit();
    }

    // Obtener nuevo ID cliente
    $consulta_id_cli = "SELECT COALESCE(MAX(cli_id), 0) + 1 AS nuevo_id_cli FROM cliente";
    $resultado_id_cli = pg_query($conn, $consulta_id_cli);
    $fila_id_cli = pg_fetch_assoc($resultado_id_cli);
    $nuevo_id_cli = $fila_id_cli['nuevo_id_cli'];

    // Insertar cliente
    $query_cliente = "INSERT INTO cliente (cli_id, cli_nom, cli_ape, cli_ci, cli_email, cli_tel, dir_cod, cli_estado) 
                      VALUES ($1, $2, $3, $4, $5, $6, $7, 'ACTIVO') RETURNING cli_id";
    $result_cliente = pg_query_params($conn, $query_cliente, array($nuevo_id_cli, $nombre, $apellido, $ci, $email, $telefono, $dir_cod));

    if (!$result_cliente) {
        $error = pg_last_error($conn);
        echo "<script>alert('Error al insertar datos del cliente: " . addslashes($error) . "'); window.history.back();</script>";
        exit();
    }

    $row_cliente = pg_fetch_assoc($result_cliente);
    $nuevo_cli_id = $row_cliente['cli_id'];

    // Insertar relación usuario_cliente
    $query_usuario_cliente = "INSERT INTO usuario_cliente (usu_id, cli_id, uc_estado) VALUES ($1, $2, 'ACTIVO')";
    $result_usuario_cliente = pg_query_params($conn, $query_usuario_cliente, array($nuevo_id, $nuevo_cli_id));

    if ($result_usuario_cliente) {
        echo "<script>alert('¡Registro exitoso!'); window.location.href = 'usuario.php';</script>";
    } else {
        echo "<script>alert('Error al registrar la relación usuario-cliente.'); window.history.back();</script>";
    }

    exit();
}

// Cargar direcciones activas para el select
$query_dir = "SELECT dir_cod, dir_descri FROM direccion WHERE dir_estado = 'ACTIVO' ORDER BY dir_descri";
$result_dir = pg_query($conn, $query_dir);
if (!$result_dir) {
    die("Error en la consulta de direcciones: " . pg_last_error());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Registro de Cliente y Usuario</title>
    <!-- Bootstrap CSS CDN -->
    <link rel="icon" type="image/webp" href="logos/icono.webp">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .registro-container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            padding: 30px 40px;
            width: 100%;
            max-width: 420px;
        }
        .registro-container h3 {
            font-weight: 700;
            margin-bottom: 25px;
            color: #333;
            text-align: center;
            letter-spacing: 1px;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(37, 117, 252, 0.4);
            border-color: #2575fc;
        }
        .btn-success {
            background-color: #2575fc;
            border-color: #2575fc;
            font-weight: 600;
            transition: background-color 0.3s ease;
            width: 100%;
        }
        .btn-success:hover {
            background-color: #1a52d1;
            border-color: #1a52d1;
        }
        .btn-link {
            color: #2575fc;
            font-weight: 600;
            text-decoration: none;
        }
        .btn-link:hover {
            text-decoration: underline;
        }
        label {
            font-weight: 600;
            color: #555;
        }
        .required:after {
            content:" *";
            color: red;
        }
        hr {
            margin: 25px 0;
        }
    </style>
</head>
<body>

<div class="registro-container">
    <h3>Registro de Cliente y Usuario</h3>
    <form action="registro.php" method="POST" novalidate>

        <h5 class="mb-3">Datos del Cliente</h5>

        <div class="mb-3">
            <label for="nombre" class="required">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="50" />
        </div>

        <div class="mb-3">
            <label for="apellido" class="required">Apellido</label>
            <input type="text" class="form-control" id="apellido" name="apellido" required maxlength="50" />
        </div>

        <div class="mb-3">
            <label for="ci" class="required">C.I.</label>
            <input type="text" class="form-control" id="ci" name="ci" required maxlength="20" />
        </div>

        <div class="mb-3">
            <label for="email" class="required">Email</label>
            <input type="email" class="form-control" id="email" name="email" required maxlength="100" />
        </div>

        <div class="mb-3">
            <label for="telefono" class="required">Teléfono</label>
            <input type="tel" class="form-control" id="telefono" name="telefono" required maxlength="20" />
        </div>

        <div class="mb-4">
            <label for="dir_cod" class="required">Dirección</label>
            <select class="form-select" id="dir_cod" name="dir_cod" required>
                <option value="" selected>Seleccione una dirección</option>
                <?php
                while ($row = pg_fetch_assoc($result_dir)) {
                    echo "<option value='" . $row['dir_cod'] . "'>" . htmlspecialchars($row['dir_descri']) . "</option>";
                }
                ?>
            </select>
        </div>

        <hr />

        <h5 class="mb-3">Datos del Usuario</h5>

        <div class="mb-3">
            <label for="nick_pag" class="required">Nickname</label>
            <input type="text" class="form-control" id="nick_pag" name="nick_pag" required maxlength="30" />
        </div>

        <div class="mb-3">
            <label for="contrasena" class="required">Contraseña</label>
            <input type="password" class="form-control" id="contrasena" name="contrasena" required minlength="6" />
            <div class="form-text">Mínimo 6 caracteres</div>
        </div>

        <div class="mb-4">
            <label for="confirmar_contrasena" class="required">Confirmar Contraseña</label>
            <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required minlength="6" />
        </div>

        <button type="submit" class="btn btn-success">Registrar</button>
    </form>
</div>

<!-- Bootstrap JS y dependencias Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
