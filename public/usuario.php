<?php
// index.php
// Configuración de la base de datos
$host = 'localhost';
$port = '5433';
$dbname = 'proyecto1';
$user = 'postgres';
$password = '123';

// Crear conexión
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

// Verificar la conexión
$msg = $conn ? 'success' : 'error';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="logos/icono.webp">
    <title>Login</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            background-image: url('logos/fondo.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .login-container h2 {
            margin-bottom: 20px;
            color: #333;
            display: flex;
            align-items: center;
        }
        .btn-custom {
            background-color: #007bff;
            color: #fff;
            border: none;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
        .alert-custom {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
            opacity: 0.8;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h3 class="text-center d-flex align-items-center justify-content-center">
                <img src="logos/logo.png" alt="Logo" style="width: 40px; margin-right: 12px;">SOFTWARE - CLIENTES
            </h3>
            <form action="login.php" method="POST">
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                    </div>
                    <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Usuario" required>
                </div>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    </div>
                    <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="Contraseña" required>
                </div>
                <button type="submit" class="btn btn-custom btn-block">Iniciar sesión</button>
            </form>
            <div class="text-center mt-3">
                <a href="registro.php">¿No tienes una cuenta? Regístrate aquí</a>
            </div>
        </div>
    </div>
</body>
</html>