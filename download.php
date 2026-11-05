<?php
session_start();

// Solo los usuarios conectados pueden abrir archivos.
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

// La URL recibe el id del mensaje que contiene el archivo.
$messageId = filter_input(INPUT_GET, 'message', FILTER_VALIDATE_INT);
if (!$messageId || $messageId < 1) {
    http_response_code(404);
    exit;
}

require_once 'services/connection.php';

// Busca la ruta y los datos del archivo; el usuario debe ser emisor o receptor.
$statement = $conn->prepare(
    'SELECT file_path, original_file_name, message_type
     FROM tbl_messages
     WHERE id_message = :message_id
       AND (id_sender = :sender_id OR id_receiver = :receiver_id)
     LIMIT 1'
);
$statement->bindValue(':message_id', $messageId, PDO::PARAM_INT);
$statement->bindValue(':sender_id', (int) $_SESSION['user_id'], PDO::PARAM_INT);
$statement->bindValue(':receiver_id', (int) $_SESSION['user_id'], PDO::PARAM_INT);
$statement->execute();
$message = $statement->fetch();

if (
    !$message ||
    !in_array($message['message_type'], array('image', 'file'), true) ||
    !is_string($message['file_path']) ||
    !is_string($message['original_file_name'])
) {
    http_response_code(404);
    exit;
}

// Comprueba que la ruta tenga el formato generado por la aplicacion.
if (!preg_match('/^archivos_chat\/[a-f0-9]{32}\.(jpg|jpeg|png|gif|webp|pdf|txt|doc|docx|xls|xlsx|ppt|pptx)$/', $message['file_path'])) {
    http_response_code(404);
    exit;
}

$filePath = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $message['file_path']);
if (!is_file($filePath)) {
    http_response_code(404);
    exit;
}

// Comprueba de nuevo el contenido real antes de enviarlo al navegador.
$fileInfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $fileInfo->file($filePath);
$tiposPermitidos = array(
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'application/pdf',
    'text/plain',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'application/zip'
);
$esImagen = in_array($mimeType, array('image/jpeg', 'image/png', 'image/gif', 'image/webp'), true);
$tipoCorrecto = $message['message_type'] === 'image'
    ? $esImagen
    : is_string($mimeType) && in_array($mimeType, $tiposPermitidos, true) && !$esImagen;

if (!$tipoCorrecto) {
    http_response_code(404);
    exit;
}

// El navegador muestra las imagenes en el chat y descarga los documentos.
if ($esImagen) {
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: inline');
} else {
    header('Content-Type: application/octet-stream');
    header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($message['original_file_name']));
}

header('Content-Length: ' . filesize($filePath));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
readfile($filePath);
exit;
