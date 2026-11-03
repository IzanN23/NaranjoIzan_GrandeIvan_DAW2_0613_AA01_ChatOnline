<?php
// Inicia la sesion para comprobar que el usuario ha iniciado sesion.
session_start();

// Si no hay usuario autenticado, solo puede volver a la pagina de login.
if (!isset($_SESSION['user_id'], $_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$username = $_SESSION['username'];

// Carga la conexion PDO para consultar amistades y mensajes.
require_once 'services/connection.php';

// Busca las amistades aceptadas. La consulta sirve tanto si el usuario envio
// la solicitud como si la recibio.
$sqlFriends = 'SELECT users.id_users, users.username_users, users.profile_image_path
               FROM tbl_friend_request AS requests
               INNER JOIN tbl_users AS users
                 ON users.id_users = CASE
                    WHEN requests.id_sender = :sender_case THEN requests.id_receiver
                    ELSE requests.id_sender
                 END
               WHERE (requests.id_sender = :sender_filter
                      OR requests.id_receiver = :receiver_filter)
                 AND requests.status = :status
               ORDER BY users.username_users';
$friendsStatement = $conn->prepare($sqlFriends);
$friendsStatement->bindValue(':sender_case', $userId, PDO::PARAM_INT);
$friendsStatement->bindValue(':sender_filter', $userId, PDO::PARAM_INT);
$friendsStatement->bindValue(':receiver_filter', $userId, PDO::PARAM_INT);
$friendsStatement->bindValue(':status', 'aceptada');
$friendsStatement->execute();
$friends = $friendsStatement->fetchAll();

// Si se ha elegido una amistad, mostramos su conversacion.
$selectedFriendId = filter_input(INPUT_GET, 'friend', FILTER_VALIDATE_INT);
if (!$selectedFriendId && count($friends) > 0) {
    $selectedFriendId = (int) $friends[0]['id_users'];
}

$selectedFriend = null;
$messages = array();

// Se comprueba que el id solicitado pertenece a la lista de amistades aceptadas.
foreach ($friends as $friend) {
    if ((int) $friend['id_users'] === $selectedFriendId) {
        $selectedFriend = $friend;
        break;
    }
}

if ($selectedFriend !== null) {
    // Trae mensajes enviados y recibidos entre los dos usuarios, del mas antiguo al mas nuevo.
    $sqlMessages = 'SELECT messages.id_message, messages.id_sender, messages.content,
                           messages.message_type, messages.original_file_name, messages.sent_at,
                           users.username_users AS sender_username
                    FROM tbl_messages AS messages
                    INNER JOIN tbl_users AS users
                      ON users.id_users = messages.id_sender
                    WHERE (messages.id_sender = :my_sender
                           AND messages.id_receiver = :friend_receiver)
                       OR (messages.id_sender = :friend_sender
                           AND messages.id_receiver = :my_receiver)
                    ORDER BY messages.sent_at ASC, messages.id_message ASC';
    $messagesStatement = $conn->prepare($sqlMessages);
    $messagesStatement->bindValue(':my_sender', $userId, PDO::PARAM_INT);
    $messagesStatement->bindValue(':friend_receiver', $selectedFriendId, PDO::PARAM_INT);
    $messagesStatement->bindValue(':friend_sender', $selectedFriendId, PDO::PARAM_INT);
    $messagesStatement->bindValue(':my_receiver', $userId, PDO::PARAM_INT);
    $messagesStatement->execute();
    $messages = $messagesStatement->fetchAll();
}

$messageError = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatOnline | Chat</title>
    <link rel="stylesheet" href="css/styles.css?v=4">
</head>
<body class="chat-page">
    <header class="site-header chat-header">
        <a class="brand" href="chat.php" aria-label="ChatOnline, inicio del chat">
            <span class="brand-icon" aria-hidden="true">C</span>
            <span>ChatOnline</span>
        </a>
        <div class="session-info">
            <span>Conectado como <strong><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></strong></span>
            <a href="profile.php">Mi perfil</a>
            <a href="buscar_amigos.php">Buscar gente</a>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </header>

    <main class="chat-layout">
        <aside class="friends-panel" aria-label="Lista de amistades">
            <div class="friends-heading">
                <h1>Mis chats</h1>
                <a class="search-link" href="buscar_amigos.php">Buscar gente</a>
            </div>

            <?php if (count($friends) === 0) { ?>
                <p class="empty-friends">Todavía no tienes amistades aceptadas.</p>
                <a href="buscar_amigos.php">Busca gente para empezar</a>
            <?php } else { ?>
                <ul class="friends-list">
                    <?php foreach ($friends as $friend) { ?>
                        <li>
                            <a
                                class="friend-link<?php echo $selectedFriendId === (int) $friend['id_users'] ? ' selected' : ''; ?>"
                                href="chat.php?friend=<?php echo (int) $friend['id_users']; ?>"
                            >
                                <!-- Si no hay foto, se muestra la inicial del username. -->
                                <?php if (!empty($friend['profile_image_path'])) { ?>
                                    <img class="friend-avatar" src="media.php?profile=<?php echo (int) $friend['id_users']; ?>" alt="">
                                <?php } else { ?>
                                    <span class="friend-avatar" aria-hidden="true"><?php echo htmlspecialchars(strtoupper(substr($friend['username_users'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php } ?>
                                <span><?php echo htmlspecialchars($friend['username_users'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            <?php } ?>
        </aside>

        <section class="conversation-panel" aria-label="Conversación">
            <?php if ($selectedFriend !== null) { ?>
                <div class="conversation-heading">
                    <!-- Muestra la foto del amigo o su inicial si todavía no tiene foto. -->
                    <?php if (!empty($selectedFriend['profile_image_path'])) { ?>
                        <img class="friend-avatar" src="media.php?profile=<?php echo (int) $selectedFriend['id_users']; ?>" alt="">
                    <?php } else { ?>
                        <span class="friend-avatar" aria-hidden="true"><?php echo htmlspecialchars(strtoupper(substr($selectedFriend['username_users'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php } ?>
                    <h2><?php echo htmlspecialchars($selectedFriend['username_users'], ENT_QUOTES, 'UTF-8'); ?></h2>
                </div>

                <div class="messages-list" aria-live="polite">
                    <?php if (count($messages) === 0) { ?>
                        <p class="empty-conversation">Todavía no hay mensajes. ¡Di hola!</p>
                    <?php } else { ?>
                        <?php foreach ($messages as $message) { ?>
                            <article class="message<?php echo (int) $message['id_sender'] === $userId ? ' own-message' : ''; ?>">
                                <div class="message-meta">
                                    <strong><?php echo htmlspecialchars($message['sender_username'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <time><?php echo htmlspecialchars($message['sent_at'], ENT_QUOTES, 'UTF-8'); ?></time>
                                </div>
                                <?php if ($message['message_type'] === 'image') { ?>
                                    <img class="chat-image" src="download.php?message=<?php echo (int) $message['id_message']; ?>" alt="Imagen enviada en el chat">
                                <?php } elseif ($message['message_type'] === 'file') { ?>
                                    <a class="chat-file" href="download.php?message=<?php echo (int) $message['id_message']; ?>">
                                        Descargar: <?php echo htmlspecialchars($message['original_file_name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                <?php } ?>
                                <?php if ($message['content'] !== '') { ?>
                                    <p><?php echo nl2br(htmlspecialchars($message['content'], ENT_QUOTES, 'UTF-8')); ?></p>
                                <?php } ?>
                            </article>
                        <?php } ?>
                    <?php } ?>
                </div>

                <?php if ($messageError === 'invalid') { ?>
                    <p class="form-message chat-error" role="alert">Escribe un mensaje de hasta 250 caracteres.</p>
                <?php } elseif ($messageError === 'attachment') { ?>
                    <p class="form-message chat-error" role="alert">Elige una imagen o un documento permitido de hasta 2 MB.</p>
                <?php } elseif ($messageError === 'upload') { ?>
                    <p class="form-message chat-error" role="alert">No se pudo guardar el archivo. Inténtalo de nuevo.</p>
                <?php } ?>

                <form id="form_message" class="message-form" action="processes/message.proc.php" method="post" novalidate>
                    <input type="hidden" name="receiver_id" value="<?php echo (int) $selectedFriend['id_users']; ?>">
                    <label class="visually-hidden" for="message-content">Escribe un mensaje</label>
                    <input
                        type="text"
                        id="message-content"
                        name="content"
                        maxlength="250"
                        placeholder="Escribe un mensaje..."
                        required
                    >
                    <button type="submit">Enviar</button>
                </form>

                <!-- Este formulario adjunta archivos; el de arriba solo envia texto. -->
                <form class="message-form attachment-form" action="processes/attachment.proc.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="receiver_id" value="<?php echo (int) $selectedFriend['id_users']; ?>">
                    <label class="attachment-label" for="chat-attachment">Imagen o archivo</label>
                    <input
                        type="file"
                        id="chat-attachment"
                        name="attachment"
                        accept="image/jpeg,image/png,image/gif,image/webp,.pdf,.txt,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                        required
                    >
                    <button type="submit">Adjuntar</button>
                </form>

                <p class="form-message chat-error" id="error-message" role="alert" hidden></p>
            <?php } else { ?>
                <div class="no-conversation">
                    <span class="brand-icon" aria-hidden="true">C</span>
                    <h2>Tu espacio para conversar</h2>
                    <p>Selecciona una amistad aceptada o busca gente para empezar.</p>
                    <a class="primary-link" href="buscar_amigos.php">Buscar gente</a>
                </div>
            <?php } ?>
        </section>
    </main>

    <script src="js/validaciones_chat.js"></script>
</body>
</html>