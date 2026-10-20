<?php
// Inicia la sesion antes de leer o guardar datos de autenticacion.
session_start();

// Solo se procesa el envio del formulario; abrir directamente este archivo vuelve al login.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

// Recoge los valores usando los mismos nombres que tienen los campos HTML.
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (
    !is_string($username) ||
    !is_string($password) ||
    $username === '' ||
    strlen($username) > 50 ||
    $password === ''
) {
    // Si falta algun campo o tiene un formato inesperado, el login no puede continuar.
    header('Location: ../index.php?error=login');
    exit;
}

$username = trim($username);

// Incluye el archivo que crea la conexion PDO llamada $conn.
require_once '../services/connection.php';

// Busca solo el registro que coincide con el username.
// La consulta preparada evita insertar el valor directamente dentro del SQL.
$sql = 'SELECT id_users, username_users, password_users
        FROM tbl_users
        WHERE username_users = :username';
$statement = $conn->prepare($sql);
$statement->bindValue(':username', $username);
// Ejecuta el SELECT y obtiene como maximo una fila asociativa.
$statement->execute();
$user = $statement->fetch();

// password_verify compara la contrasena recibida con el hash guardado al registrarse.
// Se muestra el mismo error si el usuario no existe o la contrasena no coincide.
if (!$user || !password_verify($password, $user['password_users'])) {
    header('Location: ../index.php?error=login');
    exit;
}

// Cambia el identificador de sesion al autenticar para evitar reutilizar el anterior.
session_regenerate_id(true);
// Estos datos quedan disponibles en otras paginas mientras dure la sesion.
$_SESSION['user_id'] = $user['id_users'];
$_SESSION['username'] = $user['username_users'];

// Al entrar correctamente, lleva al usuario a la pagina protegida del chat.
header('Location: ../chat.php');
exit;
