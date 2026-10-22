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
$sqlFriends = 'SELECT users.id_users, users.username_users
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
    $sqlMessages = 'SELECT messages.id_sender, messages.content, messages.sent_at,
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
    <link rel="stylesheet" href="css/styles.css?v=3">
</head>
<body class="chat-page">
    <header class="site-header chat-header">
        <a class="brand" href="chat.php" aria-label="ChatOnline, inicio del chat">
            <span class="brand-icon" aria-hidden="true">C</span>
            <span>ChatOnline</span>
        </a>
        <div class="session-info">
            <span>Conectado como <strong><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></strong></span>
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
                                <span class="friend-avatar" aria-hidden="true"><?php echo htmlspecialchars(strtoupper(substr($friend['username_users'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span>
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
                    <span class="friend-avatar" aria-hidden="true"><?php echo htmlspecialchars(strtoupper(substr($selectedFriend['username_users'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span>
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
                                <p><?php echo nl2br(htmlspecialchars($message['content'], ENT_QUOTES, 'UTF-8')); ?></p>
                            </article>
                        <?php } ?>
                    <?php } ?>
                </div>

                <?php if ($messageError === 'invalid') { ?>
                    <p class="form-message chat-error" role="alert">Escribe un mensaje de hasta 250 caracteres.</p>
                <?php } ?>

                <form class="message-form" action="processes/message.proc.php" method="post">
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

    
</body>
</html>
