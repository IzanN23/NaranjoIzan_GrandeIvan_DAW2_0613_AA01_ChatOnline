<?php
session_start();

// Solo los usuarios con sesion pueden solicitar una foto de perfil.
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

// La URL usa este parametro, por ejemplo: media.php?profile=4.
$profileUserId = filter_input(INPUT_GET, 'profile', FILTER_VALIDATE_INT);
if (!$profileUserId || $profileUserId < 1) {
    http_response_code(404);
    exit;
}

require_once 'services/connection.php';

// Busca la ruta guardada para ese usuario con una consulta PDO preparada.
$statement = $conn->prepare(
    'SELECT profile_image_path FROM tbl_users WHERE id_users = :user_id'
);
$statement->bindValue(':user_id', $profileUserId, PDO::PARAM_INT);
$statement->execute();
$imagePath = $statement->fetchColumn();

// Rechaza rutas inventadas para impedir que se lean otros archivos del servidor.
if (
    !is_string($imagePath) ||
    !preg_match('/^fotos_perfil\/[a-f0-9]{32}\.(jpg|png|gif|webp)$/', $imagePath)
) {
    http_response_code(404);
    exit;
}

$imageFile = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $imagePath);
if (!is_file($imageFile)) {
    http_response_code(404);
    exit;
}

// Detecta el formato real antes de enviar los bytes de imagen al navegador.
$fileInfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $fileInfo->file($imageFile);
$tiposPermitidos = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');

if (!in_array($mimeType, $tiposPermitidos, true)) {
    http_response_code(404);
    exit;
}

header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($imageFile));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
header('Content-Disposition: inline');
readfile($imageFile);
exit;
