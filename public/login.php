<?php
session_start();

// Configuración de la base de datos
$host = 'localhost';
$port = '5433';
$dbname = 'proyecto1';
$user = 'postgres';
$password = '123';

// Crear conexión
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Error al conectar con la base de datos.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tomar y limpiar datos del formulario
    $usuario = trim(strtolower($_POST['usuario']));
    $contrasena = trim($_POST['contrasena']);
    $contrasena_md5 = md5($contrasena);

    // Buscar usuario ignorando mayúsculas/minúsculas
    $query = "SELECT * FROM usuarios WHERE LOWER(usu_nombre) = $1";
    $result = pg_query_params($conn, $query, array($usuario));

    if (pg_num_rows($result) === 0) {
        $_SESSION['mensaje'] = 'USUARIO NO ENCONTRADO';
        header('Location: error.php');
        exit();
    }

    $datos = pg_fetch_assoc($result);

    // Verificar si está bloqueado
    if ($datos['estado'] === 'BLOQUEADO') {
        $_SESSION['mensaje'] = 'ESTE USUARIO ESTÁ BLOQUEADO';
        header('Location: error.php');
        exit();
    }

    // Verificar contraseña
    if ($datos['usu_contrasena'] !== $contrasena_md5) {
        $intentos = $datos['intentos'] + 1;
        pg_query_params($conn, "UPDATE usuarios SET intentos = $1 WHERE usu_nombre = $2", array($intentos, $datos['usu_nombre']));

        if ($intentos >= 3) {
            pg_query_params($conn, "UPDATE usuarios SET estado = 'BLOQUEADO' WHERE usu_nombre = $1", array($datos['usu_nombre']));
            $_SESSION['mensaje'] = 'USUARIO BLOQUEADO POR INTENTOS FALLIDOS';
        } else {
            $_SESSION['mensaje'] = 'CONTRASEÑA INCORRECTA. Intentos: ' . $intentos;
        }
        header('Location: error.php');
        exit();
    }

    // Verificar grupo CLIENTE
    if ($datos['usu_grupo'] !== 'CLIENTE') {
        $_SESSION['mensaje'] = 'Debe registrarse como CLIENTE para acceder a la tienda.';
        header('Location: error.php');
        exit();
    }

    // Reiniciar intentos tras inicio exitoso
    pg_query_params($conn, "UPDATE usuarios SET intentos = 0 WHERE usu_nombre = $1", array($datos['usu_nombre']));

    // Guardar datos en sesión
    $_SESSION['usuario'] = $datos['usu_nombre'];
    $_SESSION['usu_nombre'] = $datos['usu_nombre'];
    $_SESSION['usu_apellido'] = $datos['nombre_pag'];  // Cambia si tienes otro campo para apellido
    $_SESSION['usu_imagen'] = $datos['imagen_ruta'];

    // Redirigir a index.php
    header('Location: index.php');
    exit();

} else {
    header('Location: usuario.php');
    exit();
}
?>
