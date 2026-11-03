<?php
session_start();

// Solo un usuario conectado puede cambiar su foto.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../profile.php');
    exit;
}

// Comprueba que se haya elegido una imagen y que no supere 2 MB.
if (
    !isset($_FILES['profile_image']['error'], $_FILES['profile_image']['size'], $_FILES['profile_image']['tmp_name']) ||
    !is_int($_FILES['profile_image']['error']) ||
    !is_int($_FILES['profile_image']['size']) ||
    !is_string($_FILES['profile_image']['tmp_name']) ||
    $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK ||
    $_FILES['profile_image']['size'] > 2 * 1024 * 1024 ||
    !is_uploaded_file($_FILES['profile_image']['tmp_name'])
) {
    header('Location: ../profile.php?error=image');
    exit;
}

// Comprueba el formato real de la imagen.
$fileInfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $fileInfo->file($_FILES['profile_image']['tmp_name']);
$tiposPermitidos = array(
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp'
);
$imageInfo = getimagesize($_FILES['profile_image']['tmp_name']);

if (
    !is_string($mimeType) ||
    !isset($tiposPermitidos[$mimeType]) ||
    $imageInfo === false ||
    $imageInfo[0] > 6000 ||
    $imageInfo[1] > 6000 ||
    $imageInfo[0] * $imageInfo[1] > 20000000
) {
    header('Location: ../profile.php?error=image');
    exit;
}

// Guarda la imagen en fotos_perfil con un nombre aleatorio.
$folder = __DIR__ . '/../fotos_perfil';
if (!is_dir($folder) && !mkdir($folder, 0755)) {
    header('Location: ../profile.php?error=upload');
    exit;
}

$storedName = bin2hex(random_bytes(16)) . '.' . $tiposPermitidos[$mimeType];
$imagePath = 'fotos_perfil/' . $storedName;

if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $folder . '/' . $storedName)) {
    header('Location: ../profile.php?error=upload');
    exit;
}

require_once __DIR__ . '/../services/connection.php';
$userId = (int) $_SESSION['user_id'];

// Guarda en la base de datos la ruta de la foto.
$oldImageStatement = $conn->prepare(
    'SELECT profile_image_path FROM tbl_users WHERE id_users = :user_id'
);
$oldImageStatement->bindValue(':user_id', $userId, PDO::PARAM_INT);
$oldImageStatement->execute();
$oldImage = $oldImageStatement->fetchColumn();

$updateStatement = $conn->prepare(
    'UPDATE tbl_users SET profile_image_path = :image_path WHERE id_users = :user_id'
);
$updateStatement->bindValue(':image_path', $imagePath);
$updateStatement->bindValue(':user_id', $userId, PDO::PARAM_INT);

try {
    $updateStatement->execute();
} catch (PDOException $exception) {
    // Borra el archivo nuevo si no se ha podido guardar en la base de datos.
    unlink($folder . '/' . $storedName);
    throw $exception;
}

// Borra la foto anterior, si tambien estaba guardada por esta aplicacion.
if (
    is_string($oldImage) &&
    preg_match('/^fotos_perfil\/[a-f0-9]{32}\.(jpg|png|gif|webp)$/', $oldImage)
) {
    unlink(__DIR__ . '/../' . $oldImage);
}

header('Location: ../profile.php?updated=1');
exit;
