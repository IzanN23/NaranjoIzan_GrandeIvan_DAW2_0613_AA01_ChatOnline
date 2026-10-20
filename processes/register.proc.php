<?php
// Inicia la sesion antes de procesar la peticion.
session_start();

// El procesador solo acepta datos enviados por un formulario POST.
// Si alguien abre esta URL directamente, vuelve al formulario de registro.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit;
}

// Cada clave entre corchetes coincide con el atributo name de register2.php.
// ?? devuelve una cadena vacia si ese campo no fue enviado.
$name = $_POST['nombre_real'] ?? '';
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$passwordConfirmation = $_POST['confirmar_password'] ?? '';

if (
    !is_string($name) ||
    !is_string($username) ||
    !is_string($email) ||
    !is_string($password) ||
    !is_string($passwordConfirmation)
) {
    // Los valores de $_POST normalmente son texto; se rechazan si alguien envia otro tipo.
    header('Location: ../register.php?error=invalid');
    exit;
}

// trim elimina espacios al principio y al final de los campos de texto.
$name = trim($name);
$username = trim($username);
$email = trim($email);

if (
    // Comprueba que el nombre no este vacio y que quepa en la columna VARCHAR(100).
    $name === '' ||
    strlen($name) > 100 ||
    // El username solo admite letras, numeros y guion bajo.
    !preg_match('/^[A-Za-z0-9_]{1,50}$/', $username) ||
    strlen($username) > 30 ||
    // Valida el formato y la longitud maxima del correo.
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 254 ||
    // PASSWORD_BCRYPT admite hasta 72 bytes; tambien se exige una longitud minima.
    strlen($password) < 8 ||
    strlen($password) > 72 ||
    // La confirmacion solo se compara; nunca se almacena en la base de datos.
    $password !== $passwordConfirmation
) {
    // Se envia un codigo simple por GET para que register2.php muestre el mensaje.
    header('Location: ../register.php?error=invalid');
    exit;
}

require_once '../services/connection.php';

// Crea un hash BCRYPT de la contrasena. El hash es lo unico que se guarda en la BBDD.
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

// Los parametros nombrados (:name, :username, etc.) separan los datos de la consulta SQL.
// prepare() y bindValue() protegen los valores contra inyeccion SQL.
$sql = 'INSERT INTO tbl_users (name_users, username_users, email_users, password_users)
        VALUES (:name, :username, :email, :password)';
$statement = $conn->prepare($sql);
$statement->bindValue(':name', $name);
$statement->bindValue(':username', $username);
$statement->bindValue(':email', $email);
$statement->bindValue(':password', $passwordHash);

try {
    // Ejecuta el INSERT con los valores asociados a los parametros.
    $statement->execute();
} catch (PDOException $e) {
    // SQLSTATE 23000 indica una restriccion incumplida, por ejemplo username/email duplicado.
    if ($e->getCode() === '23000') {
        header('Location: ../register.php?error=exists');
        exit;
    }

    // Los demas errores no se ocultan: se propagan para poder detectar el problema real.
    throw $e;
}

// Tras crear la cuenta, vuelve al login y le indica que el registro fue correcto.
header('Location: ../index.php?registered=1');
exit;
