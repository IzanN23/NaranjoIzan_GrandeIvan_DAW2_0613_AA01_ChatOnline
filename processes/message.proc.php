<?php
// Inicia la sesion para identificar al usuario que envia el mensaje.
session_start();

// Solo un usuario conectado puede enviar mensajes.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Este procesador solo acepta formularios enviados con POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../chat.php');
    exit;
}

$senderId = (int) $_SESSION['user_id'];
$receiverId = filter_input(INPUT_POST, 'receiver_id', FILTER_VALIDATE_INT);
$content = $_POST['content'] ?? '';

if (!is_string($content) || !$receiverId || $receiverId === $senderId) {
    header('Location: ../chat.php');
    exit;
}

$content = trim($content);

// El chat solo admite mensajes de texto de 1 a 250 caracteres.
if ($content === '' || mb_strlen($content, 'UTF-8') > 250) {
    header('Location: ../chat.php?friend=' . $receiverId . '&error=invalid');
    exit;
}

require_once '../services/connection.php';

// Comprueba en la base de datos que la persona destinataria sea una amistad aceptada.
$sqlFriend = 'SELECT id_request FROM tbl_friend_request WHERE status = :status AND ( (id_sender = :sender_one AND id_receiver = :receiver_one) OR (id_sender = :sender_two AND id_receiver = :receiver_two))LIMIT 1';


$friendStatement = $conn->prepare($sqlFriend);
$friendStatement->bindValue(':status', 'aceptada');
$friendStatement->bindValue(':sender_one', $senderId, PDO::PARAM_INT);
$friendStatement->bindValue(':receiver_one', $receiverId, PDO::PARAM_INT);
$friendStatement->bindValue(':sender_two', $receiverId, PDO::PARAM_INT);
$friendStatement->bindValue(':receiver_two', $senderId, PDO::PARAM_INT);
$friendStatement->execute();

if (!$friendStatement->fetch()) {
    header('Location: ../chat.php');
    exit;
}

// Guarda el texto usando parametros nombrados para evitar inyeccion SQL.
$sqlMessage = 'INSERT INTO tbl_messages (id_sender, id_receiver, content) VALUES (:sender, :receiver, :content)';
$messageStatement = $conn->prepare($sqlMessage);
$messageStatement->bindValue(':sender', $senderId, PDO::PARAM_INT);
$messageStatement->bindValue(':receiver', $receiverId, PDO::PARAM_INT);
$messageStatement->bindValue(':content', $content);
$messageStatement->execute();

// Vuelve a la conversacion que acaba de recibir el mensaje.
header('Location: ../chat.php?friend=' . $receiverId);
exit;
