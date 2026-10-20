<?php

session_start();
session_unset(); // Eliminar todas las variables de sesión
session_destroy(); // Destruir la sesión actual

// Volver a la pagina de inicio de sesion.
header("Location: index.php");
exit();

?>