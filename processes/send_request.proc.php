<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../buscar_amigos.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];

$receiverId = filter_input(INPUT_POST, 'receiver_id', FILTER_VALIDATE_INT);

if (!$receiverId || $receiverId === $userId) {
    header('Location: ../buscar_amigos.php?msg=error');
    exit;
}

require_once __DIR__ . '/../services/connection.php';

try {
    $userStatement = $conn->prepare('SELECT id_users FROM tbl_users WHERE id_users = :receiver');
    $userStatement->bindValue(':receiver', $receiverId, PDO::PARAM_INT);
    $userStatement->execute();

    if (!$userStatement->fetch()) {
        header('Location: ../buscar_amigos.php?msg=error');
        exit;
    }

    $sqlExists = 'SELECT 1
                  FROM tbl_friend_request
                  WHERE (id_sender = :me_sender AND id_receiver = :other_receiver)
                     OR (id_sender = :other_sender AND id_receiver = :me_receiver)';
    $existsStatement = $conn->prepare($sqlExists);
    $existsStatement->bindValue(':me_sender', $userId, PDO::PARAM_INT);
    $existsStatement->bindValue(':other_receiver', $receiverId, PDO::PARAM_INT);
    $existsStatement->bindValue(':other_sender', $receiverId, PDO::PARAM_INT);
    $existsStatement->bindValue(':me_receiver', $userId, PDO::PARAM_INT);
    $existsStatement->execute();

    if ($existsStatement->fetch()) {
        header('Location: ../buscar_amigos.php?msg=exists');
        exit;
    }

    $insertStatement = $conn->prepare(
        'INSERT INTO tbl_friend_request (id_sender, id_receiver, status)
         VALUES (:sender, :receiver, :status)'
    );
    $insertStatement->bindValue(':sender', $userId, PDO::PARAM_INT);
    $insertStatement->bindValue(':receiver', $receiverId, PDO::PARAM_INT);
    $insertStatement->bindValue(':status', 'pendiente');
    $insertStatement->execute();

    header('Location: ../buscar_amigos.php?msg=sent');
    exit;
} catch (PDOException $exception) {
    header('Location: ../buscar_amigos.php?msg=exists');
    exit;
}