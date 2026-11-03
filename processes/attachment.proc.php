<?php
session_start();

// Solo un usuario conectado puede enviar archivos.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../chat.php');
    exit;
}

$senderId = (int) $_SESSION['user_id'];
$receiverId = filter_input(INPUT_POST, 'receiver_id', FILTER_VALIDATE_INT);

if (!$receiverId || $receiverId === $senderId) {
    header('Location: ../chat.php');
    exit;
}

// Comprueba que se haya elegido un archivo y que no supere 2 MB.
if (
    !isset($_FILES['attachment']['error'], $_FILES['attachment']['size'], $_FILES['attachment']['tmp_name'], $_FILES['attachment']['name']) ||
    !is_int($_FILES['attachment']['error']) ||
    !is_int($_FILES['attachment']['size']) ||
    !is_string($_FILES['attachment']['tmp_name']) ||
    !is_string($_FILES['attachment']['name']) ||
    $_FILES['attachment']['error'] !== UPLOAD_ERR_OK ||
    $_FILES['attachment']['size'] > 2 * 1024 * 1024 ||
    !is_uploaded_file($_FILES['attachment']['tmp_name'])
) {
    header('Location: ../chat.php?friend=' . $receiverId . '&error=attachment');
    exit;
}

// Lista de extensiones y tipos de archivo permitidos.
$extension = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
$tiposPermitidos = array(
    'jpg' => array('image/jpeg'),
    'jpeg' => array('image/jpeg'),
    'png' => array('image/png'),
    'gif' => array('image/gif'),
    'webp' => array('image/webp'),
    'pdf' => array('application/pdf'),
    'txt' => array('text/plain'),
    'doc' => array('application/msword'),
    'docx' => array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'),
    'xls' => array('application/vnd.ms-excel'),
    'xlsx' => array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'),
    'ppt' => array('application/vnd.ms-powerpoint'),
    'pptx' => array('application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip')
);

// Comprueba el tipo real del archivo; no se fia solamente de su extension.
$fileInfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $fileInfo->file($_FILES['attachment']['tmp_name']);

if (
    !isset($tiposPermitidos[$extension]) ||
    !is_string($mimeType) ||
    !in_array($mimeType, $tiposPermitidos[$extension], true)
) {
    header('Location: ../chat.php?friend=' . $receiverId . '&error=attachment');
    exit;
}

$esImagen = in_array($extension, array('jpg', 'jpeg', 'png', 'gif', 'webp'), true);
$tipoMensaje = $esImagen ? 'image' : 'file';
$originalName = basename($_FILES['attachment']['name']);
$originalName = preg_replace('/[\x00-\x1F\x7F]/', '', $originalName);
$originalName = substr($originalName, 0, 200);

if ($originalName === '') {
    header('Location: ../chat.php?friend=' . $receiverId . '&error=attachment');
    exit;
}

// Evita imagenes con dimensiones demasiado grandes.
if ($esImagen) {
    $imageInfo = getimagesize($_FILES['attachment']['tmp_name']);
    if (
        $imageInfo === false ||
        $imageInfo[0] > 6000 ||
        $imageInfo[1] > 6000 ||
        $imageInfo[0] * $imageInfo[1] > 20000000
    ) {
        header('Location: ../chat.php?friend=' . $receiverId . '&error=attachment');
        exit;
    }
}

// El envio de archivos tambien requiere que sean amigos aceptados.
require_once __DIR__ . '/../services/connection.php';
$friendStatement = $conn->prepare(
    'SELECT id_request
     FROM tbl_friend_request
     WHERE status = :status
       AND ((id_sender = :sender_one AND id_receiver = :receiver_one)
         OR (id_sender = :sender_two AND id_receiver = :receiver_two))
     LIMIT 1'
);
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

// Guarda el archivo con un nombre aleatorio para evitar nombres peligrosos.
$folder = __DIR__ . '/../archivos_chat';
if (!is_dir($folder) && !mkdir($folder, 0755)) {
    header('Location: ../chat.php?friend=' . $receiverId . '&error=upload');
    exit;
}

$storedName = bin2hex(random_bytes(16)) . '.' . $extension;
$filePath = 'archivos_chat/' . $storedName;

if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $folder . '/' . $storedName)) {
    header('Location: ../chat.php?friend=' . $receiverId . '&error=upload');
    exit;
}

// Guarda el mensaje y la ruta con una consulta preparada de PDO.
try {
    $messageStatement = $conn->prepare(
        'INSERT INTO tbl_messages
            (id_sender, id_receiver, content, message_type, file_path, original_file_name)
         VALUES
            (:sender, :receiver, :content, :message_type, :file_path, :original_file_name)'
    );
    $messageStatement->bindValue(':sender', $senderId, PDO::PARAM_INT);
    $messageStatement->bindValue(':receiver', $receiverId, PDO::PARAM_INT);
    $messageStatement->bindValue(':content', '');
    $messageStatement->bindValue(':message_type', $tipoMensaje);
    $messageStatement->bindValue(':file_path', $filePath);
    $messageStatement->bindValue(':original_file_name', $originalName);
    $messageStatement->execute();
} catch (PDOException $exception) {
    // Si falla la base de datos, elimina el archivo que acabamos de subir.
    unlink($folder . '/' . $storedName);
    throw $exception;
}

header('Location: ../chat.php?friend=' . $receiverId);
exit;
