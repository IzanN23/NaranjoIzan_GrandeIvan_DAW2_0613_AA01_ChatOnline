<?php
// Carga config.php, que esta en la misma carpeta services.
require_once 'config.php';

try {
    // Se crea la conexion PDO con soporte para acentos y otros caracteres UTF-8.
    $conn = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPassword
    );

    // PDO lanzara una excepcion si alguna consulta falla.
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // No mostramos detalles internos de la conexion en la pagina.
    die('Error de conexión con la base de datos.');
}
