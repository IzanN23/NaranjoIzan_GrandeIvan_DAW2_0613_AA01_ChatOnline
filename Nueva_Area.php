<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'services/connection.php';

// Se comprueba el rol en la base de datos, no solo en el navegador.
$adminStatement = $conn->prepare(
    'SELECT is_admin FROM tbl_users WHERE id_users = :user_id'
);
$adminStatement->bindValue(':user_id', (int) $_SESSION['user_id'], PDO::PARAM_INT);
$adminStatement->execute();

if ((int) $adminStatement->fetchColumn() !== 1) {
    http_response_code(403);
    exit('No tienes permiso para acceder a esta pagina.');
}

$_SESSION['is_admin'] = 1;

if (!isset($_SESSION['admin_csrf_token'])) {
    $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
}

$usersStatement = $conn->query(
    'SELECT id_users, name_users, username_users, email_users, is_admin
     FROM tbl_users
     ORDER BY username_users'
);
$users = $usersStatement->fetchAll();

$editUser = null;
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);

if ($editId) {
    $editStatement = $conn->prepare(
        'SELECT id_users, name_users, username_users, email_users
         FROM tbl_users
         WHERE id_users = :user_id'
    );
    $editStatement->bindValue(':user_id', $editId, PDO::PARAM_INT);
    $editStatement->execute();
    $editUser = $editStatement->fetch();
}

$messages = array(
    'updated' => 'Los datos del usuario se han actualizado.',
    'deleted' => 'El usuario y sus mensajes se han eliminado.',
    'invalid' => 'Revisa los datos: hay campos vacios o con un formato incorrecto.',
    'exists' => 'Ese nombre de usuario o correo ya esta registrado.',
    'protected' => 'No puedes eliminar tu propia cuenta ni la ultima cuenta administradora.',
    'error' => 'No se pudo completar la operacion.'
);
$notice = '';
$messageCode = $_GET['msg'] ?? '';
if (is_string($messageCode) && isset($messages[$messageCode])) {
    $notice = $messages[$messageCode];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatOnline | Administrar usuarios</title>
    <link rel="stylesheet" href="css/styles.css?v=5">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="chat.php">
            <span class="brand-icon" aria-hidden="true">C</span>
            <span>ChatOnline</span>
        </a>
        <div class="session-info">
            <a href="chat.php">Volver al chat</a>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </header>

    <main class="admin-content">
        <section class="intro">
            <p class="eyebrow">Area privada</p>
            <h1>Administrar usuarios</h1>
            <p>Modifica los datos de una cuenta o elimínala junto con sus mensajes y solicitudes.</p>
        </section>

        <?php if ($notice !== '') { ?>
            <p class="form-message admin-notice" role="status"><?php echo htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php } ?>

        <?php if ($editUser) { ?>
            <section class="form-card admin-form">
                <h2>Modificar usuario</h2>
                <p class="card-description">Deja la contraseña vacía si no quieres cambiarla.</p>
                <form action="processes/admin_user.proc.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['admin_csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_id" value="<?php echo (int) $editUser['id_users']; ?>">

                    <div class="form-field">
                        <label for="user-name">Nombre</label>
                        <input id="user-name" name="name" type="text" maxlength="100" required value="<?php echo htmlspecialchars($editUser['name_users'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="form-field">
                        <label for="user-username">Nombre de usuario</label>
                        <input id="user-username" name="username" type="text" maxlength="30" pattern="[A-Za-z0-9_]+" required value="<?php echo htmlspecialchars($editUser['username_users'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="form-field">
                        <label for="user-email">Correo electrónico</label>
                        <input id="user-email" name="email" type="email" maxlength="254" required value="<?php echo htmlspecialchars($editUser['email_users'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="form-field">
                        <label for="user-password">Nueva contraseña (opcional)</label>
                        <input id="user-password" name="password" type="password" minlength="8" maxlength="72" autocomplete="new-password">
                    </div>
                    <button type="submit">Guardar cambios</button>
                </form>
            </section>
        <?php } ?>

        <section class="admin-users">
            <h2>Cuentas registradas</h2>
            <?php if (count($users) === 0) { ?>
                <p>No hay usuarios registrados.</p>
            <?php } else { ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['name_users'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($user['username_users'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($user['email_users'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo (int) $user['is_admin'] === 1 ? 'Administrador' : 'Usuario'; ?></td>
                                    <td class="admin-actions">
                                        <a href="Nueva_Area.php?edit=<?php echo (int) $user['id_users']; ?>">Modificar</a>
                                        <?php if ((int) $user['id_users'] !== (int) $_SESSION['user_id']) { ?>
                                            <form action="processes/admin_user.proc.php" method="post">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['admin_csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?php echo (int) $user['id_users']; ?>">
                                                <button type="submit">Eliminar</button>
                                            </form>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </section>
    </main>
</body>
</html>
