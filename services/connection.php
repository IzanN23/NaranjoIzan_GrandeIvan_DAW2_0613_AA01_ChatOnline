<?php
// Importa host, nombre de base de datos y credenciales desde config.php.
require_once  'config.php';

 try { 
     // Se crea una instancia de la clase PDO para establecer la conexión 
     $conn = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword); 
  
     // Se establece el modo de errores de PDO para lanzar excepciones en lugar de advertencias 
     $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
      
 } catch (PDOException $e) { 

    // Se captura la excepción y se muestra el mensaje de error 
    echo "Error en la conexión a la base de datos: " . $e->getMessage(); 
    die(); 

 } 