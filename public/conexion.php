<?php
// Configuración de la base de datos
$host = 'localhost';
$db = 'proyecto1';
$user = 'postgres';
$password = '123';
$port = '5433';

// Conectar a PostgreSQL
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo 'Error de conexión: ' . $e->getMessage();
    exit();
}
?>
