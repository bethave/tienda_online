
<?php
// error.php
session_start();

// Obtener el mensaje desde la sesión
$mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : 'Error desconocido';

// Limpiar el mensaje para evitar que se muestre al recargar
unset($_SESSION['mensaje']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Error</title>
    <style>
        .alerta {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #f44336; /* rojo */
            color: white;
            padding: 16px 32px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.2);
            font-family: Arial, sans-serif;
            font-size: 16px;
            z-index: 1000;
        }
    </style>
</head>
<body>

<div class="alerta" id="mensajeError">
    <?php echo htmlspecialchars($mensaje); ?>
</div>

<script>
    // Ocultar la alerta después de 3 segundos
    setTimeout(function() {
        var alerta = document.getElementById("mensajeError");
        if (alerta) {
            alerta.style.display = "none";
            // Opcional: redirigir después de mostrar el mensaje
            window.location.href = "usuario.php"; // o a donde desees volver
        }
    }, 3000);
</script>

</body>
</html>
