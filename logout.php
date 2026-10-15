<?php

session_start();
session_unset(); // Eliminar todas las variables de sesión
session_destroy(); // Destruir la sesión actual

// Redirigir al login.php
header("Location: view/login.php");
exit();

?>