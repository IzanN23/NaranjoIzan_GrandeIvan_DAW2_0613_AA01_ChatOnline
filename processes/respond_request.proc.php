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

$senderId = filter_input(INPUT_POST, 'sender_id', FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';

if (!$senderId || ($action !== 'accept' && $action !== 'reject')) {
    header('Location: ../buscar_amigos.php?msg=error');
    exit;
}

require_once __DIR__ . '/../services/connection.php';

try {
    if ($action === 'accept') {
        $statement = $conn->prepare(
            'UPDATE tbl_friend_request
             SET status = :new_status
             WHERE id_sender = :sender
               AND id_receiver = :me
               AND status = :old_status'
        );
        $statement->bindValue(':new_status', 'aceptada');
        $statement->bindValue(':old_status', 'pendiente');
        $statement->bindValue(':sender', $senderId, PDO::PARAM_INT);
        $statement->bindValue(':me', $userId, PDO::PARAM_INT);
        $statement->execute();

        $successMessage = 'accepted';
    } else {
        $statement = $conn->prepare(
            'DELETE FROM tbl_friend_request
             WHERE id_sender = :sender
               AND id_receiver = :me
               AND status = :status'
        );
        $statement->bindValue(':sender', $senderId, PDO::PARAM_INT);
        $statement->bindValue(':me', $userId, PDO::PARAM_INT);
        $statement->bindValue(':status', 'pendiente');
        $statement->execute();

        $successMessage = 'rejected';
    }

    if ($statement->rowCount() === 0) {
        header('Location: ../buscar_amigos.php?msg=error');
        exit;
    }

    header('Location: ../buscar_amigos.php?msg=' . $successMessage);
    exit;
} catch (PDOException $exception) {
    header('Location: ../buscar_amigos.php?msg=error');
    exit;
}