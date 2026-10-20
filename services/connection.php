<?php
// Importa host, nombre de base de datos y credenciales desde config.php.
require_once __DIR__ . '/config.php';

// DSN indica a PDO que use MySQL, con esta base de datos y codificacion UTF-8.
$dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";

try {
    // Crea el objeto PDO. Se reutiliza como $conn para consultas desde los procesadores.
    $conn = new PDO($dsn, $dbUser, $dbPassword);
    // Si una consulta falla, PDO lanza una excepcion que se puede manejar con try/catch.
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // fetch() devolvera columnas identificadas por su nombre, por ejemplo username_users.
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Evita mostrar credenciales o detalles internos de MySQL al usuario.
    die('No se pudo conectar con la base de datos. Comprueba la configuracion de MySQL.');
}