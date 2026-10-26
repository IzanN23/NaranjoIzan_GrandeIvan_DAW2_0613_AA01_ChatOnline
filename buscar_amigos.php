<?php
session_start();

if (!isset($_SESSION['user_id'], $_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$username = $_SESSION['username'];

require_once 'services/connection.php';


$sqlRequests = 'SELECT users.id_users, users.username_users
                FROM tbl_friend_request AS requests
                INNER JOIN tbl_users AS users
                  ON users.id_users = requests.id_sender
                WHERE requests.id_receiver = :me
                  AND requests.status = :status
                ORDER BY users.username_users';
$requestsStatement = $conn->prepare($sqlRequests);
$requestsStatement->bindValue(':me', $userId, PDO::PARAM_INT);
$requestsStatement->bindValue(':status', 'pendiente');
$requestsStatement->execute();
$pendingRequests = $requestsStatement->fetchAll();


$search = trim($_GET['q'] ?? '');
$results = array();

if ($search !== '') {
    $like = '%' . addcslashes($search, '%_\\') . '%';

    $sqlSearch = 'SELECT users.id_users, users.username_users, users.name_users,
                         requests.status AS request_status,
                         requests.id_sender AS request_sender
                  FROM tbl_users AS users
                  LEFT JOIN tbl_friend_request AS requests
                    ON (requests.id_sender = :me_sender
                        AND requests.id_receiver = users.id_users)
                    OR (requests.id_sender = users.id_users
                        AND requests.id_receiver = :me_receiver)
                  WHERE users.id_users <> :me_excluded
                    AND (users.username_users LIKE :like_username
                         OR users.name_users LIKE :like_name)
                  ORDER BY users.username_users
                  LIMIT 20';
    $searchStatement = $conn->prepare($sqlSearch);
    $searchStatement->bindValue(':me_sender', $userId, PDO::PARAM_INT);
    $searchStatement->bindValue(':me_receiver', $userId, PDO::PARAM_INT);
    $searchStatement->bindValue(':me_excluded', $userId, PDO::PARAM_INT);
    $searchStatement->bindValue(':like_username', $like);
    $searchStatement->bindValue(':like_name', $like);
    $searchStatement->execute();
    $results = $searchStatement->fetchAll();
}

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatOnline | Buscar gente</title>
    <link rel="stylesheet" href="css/styles.css?v=3">
</head>
<body class="chat-page">
    <!-- Cabecera comun de la aplicacion. -->
    <header class="site-header chat-header">
        <a class="brand" href="chat.php" aria-label="ChatOnline, inicio del chat">
            <span class="brand-icon" aria-hidden="true">C</span>
            <span>ChatOnline</span>
        </a>
        <div class="session-info">
            <span>Conectado como <strong><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></strong></span>
            <a href="chat.php">Mis chats</a>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </header>

    <main class="chat-layout">
        <!-- Sidebar: solicitudes de amistad recibidas. -->
        <aside class="friends-panel" aria-label="Solicitudes de amistad">
            <div class="friends-heading">
                <h1>Solicitudes</h1>
            </div>

            <?php if (count($pendingRequests) === 0) { ?>
                <p class="empty-friends">No tienes solicitudes pendientes.</p>
            <?php } else { ?>
                <ul class="friends-list">
                    <?php foreach ($pendingRequests as $request) { ?>
                        <li>
                            <span class="friend-avatar" aria-hidden="true"><?php echo htmlspecialchars(strtoupper(substr($request['username_users'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span>
                            <span><?php echo htmlspecialchars($request['username_users'], ENT_QUOTES, 'UTF-8'); ?></span>

                            <form action="processes/respond_request.proc.php" method="post">
                                <input type="hidden" name="sender_id" value="<?php echo (int) $request['id_users']; ?>">
                                <button type="submit" name="action" value="accept">Aceptar</button>
                                <button type="submit" name="action" value="reject">Rechazar</button>
                            </form>
                        </li>
                    <?php } ?>
                </ul>
            <?php } ?>
        </aside>

        <!-- Zona principal: buscador y resultados. -->
        <section class="conversation-panel" aria-label="Buscar gente">
            <div class="conversation-heading">
                <h2>Buscar gente</h2>
            </div>

            <?php if ($msg === 'sent') { ?>
                <p class="form-message" role="alert">Solicitud enviada.</p>
            <?php } elseif ($msg === 'exists') { ?>
                <p class="form-message chat-error" role="alert">Ya existe una solicitud con esa persona.</p>
            <?php } elseif ($msg === 'accepted') { ?>
                <p class="form-message" role="alert">Solicitud aceptada. Ya podéis chatear.</p>
            <?php } elseif ($msg === 'rejected') { ?>
                <p class="form-message" role="alert">Solicitud rechazada.</p>
            <?php } elseif ($msg === 'error') { ?>
                <p class="form-message chat-error" role="alert">No se ha podido completar la acción.</p>
            <?php } ?>

            <!-- Buscador: envia el texto por GET para poder recargar o compartir la busqueda. -->
            <form class="message-form" action="buscar_amigos.php" method="get">
                <label class="visually-hidden" for="search-query">Nombre o username</label>
                <input
                    type="text"
                    id="search-query"
                    name="q"
                    maxlength="100"
                    placeholder="Busca por nombre o username..."
                    value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
                >
                <button type="submit">Buscar</button>
            </form>

            <!-- Resultados de la busqueda. -->
            <div class="messages-list" aria-live="polite">
                <?php if ($search === '') { ?>
                    <p class="empty-conversation">Escribe un nombre o username para empezar.</p>
                <?php } elseif (count($results) === 0) { ?>
                    <p class="empty-conversation">No hay nadie que coincida con «<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>».</p>
                <?php } else { ?>
                    <ul class="friends-list">
                        <?php foreach ($results as $person) { ?>
                            <li>
                                <span class="friend-avatar" aria-hidden="true"><?php echo htmlspecialchars(strtoupper(substr($person['username_users'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span>
                                <span>
                                    <strong><?php echo htmlspecialchars($person['username_users'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <?php echo htmlspecialchars($person['name_users'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>

                                <?php if ($person['request_status'] === 'aceptada') { ?>
                                    <a href="chat.php?friend=<?php echo (int) $person['id_users']; ?>">Ya sois amigos · Abrir chat</a>
                                <?php } elseif ($person['request_status'] === 'pendiente' && (int) $person['request_sender'] === $userId) { ?>
                                    <span>Solicitud enviada</span>
                                <?php } elseif ($person['request_status'] === 'pendiente') { ?>
                                    <span>Te ha enviado una solicitud: respóndela en el panel de la izquierda</span>
                                <?php } else { ?>
                                    <form action="processes/send_request.proc.php" method="post">
                                        <input type="hidden" name="receiver_id" value="<?php echo (int) $person['id_users']; ?>">
                                        <button type="submit">Enviar solicitud</button>
                                    </form>
                                <?php } ?>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </div>
        </section>
    </main>
</body>
</html>