<?php
session_start();

// Protege la pagina: solo pueden entrar usuarios conectados.
if (!isset($_SESSION['user_id'], $_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$username = $_SESSION['username'];
$error = $_GET['error'] ?? '';
$updated = isset($_GET['updated']) && $_GET['updated'] === '1';

require_once 'services/connection.php';

// Busca la foto actual para mostrarla en la pagina.
$profileStatement = $conn->prepare(
    'SELECT profile_image_path FROM tbl_users WHERE id_users = :user_id'
);
$profileStatement->bindValue(':user_id', $userId, PDO::PARAM_INT);
$profileStatement->execute();
$profileImage = $profileStatement->fetchColumn();

// fetchColumn devuelve false si no existe el usuario; null significa que no tiene foto.
if ($profileImage === false) {
    header('Location: logout.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatOnline | Mi perfil</title>
    <link rel="stylesheet" href="css/styles.css?v=4">
</head>
<body>
    <header class="site-header chat-header">
        <a class="brand" href="chat.php" aria-label="ChatOnline, volver al chat">
            <span class="brand-icon" aria-hidden="true">C</span>
            <span>ChatOnline</span>
        </a>
        <div class="session-info">
            <span>Conectado como <strong><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></strong></span>
            <a href="chat.php">Mis chats</a>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </header>

    <main class="page-content">
        <section class="form-card" aria-labelledby="profile-title">
            <div class="card-heading">
                <h1 id="profile-title">Foto de perfil</h1>
            </div>
            <p class="card-description">Elige una imagen JPG, PNG, GIF o WEBP de hasta 2 MB.</p>

            <?php if ($updated) { ?>
                <p class="form-message success-message" role="status">Foto de perfil actualizada.</p>
            <?php } elseif ($error === 'image') { ?>
                <p class="form-message" role="alert">La imagen no es válida o supera los 2 MB.</p>
            <?php } elseif ($error === 'upload') { ?>
                <p class="form-message" role="alert">No se pudo guardar la imagen. Inténtalo de nuevo.</p>
            <?php } ?>

            <?php if (is_string($profileImage) && $profileImage !== '') { ?>
                <!-- media.php entrega la imagen al navegador usando el id del usuario conectado. -->
                <img class="profile-preview" src="media.php?profile=<?php echo $userId; ?>" alt="Foto de perfil actual">
            <?php } else { ?>
                <p class="empty-conversation">Todavía no tienes foto de perfil.</p>
            <?php } ?>

            <!-- Envia el archivo a profile_image.proc.php; enctype permite enviar archivos. -->
            <form action="processes/profile_image.proc.php" method="post" enctype="multipart/form-data">
                <div class="form-field">
                    <label for="profile-image">Seleccionar imagen</label>
                    <input type="file" id="profile-image" name="profile_image" accept="image/jpeg,image/png,image/gif,image/webp" required>
                </div>
                <button type="submit">Guardar foto</button>
            </form>
        </section>
    </main>
</body>
</html>
