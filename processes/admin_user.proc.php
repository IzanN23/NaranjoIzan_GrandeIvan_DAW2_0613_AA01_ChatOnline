<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Nueva_Area.php');
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (
    !is_string($csrfToken) ||
    !isset($_SESSION['admin_csrf_token']) ||
    !hash_equals($_SESSION['admin_csrf_token'], $csrfToken)
) {
    header('Location: ../Nueva_Area.php?msg=error');
    exit;
}

require_once __DIR__ . '/../services/connection.php';

// Revisa el permiso en cada operación para que una sesión vieja no conserve acceso.
$adminStatement = $conn->prepare(
    'SELECT is_admin FROM tbl_users WHERE id_users = :user_id'
);
$adminStatement->bindValue(':user_id', (int) $_SESSION['user_id'], PDO::PARAM_INT);
$adminStatement->execute();

if ((int) $adminStatement->fetchColumn() !== 1) {
    http_response_code(403);
    exit('No tienes permiso para realizar esta operacion.');
}

$action = $_POST['action'] ?? '';
$targetId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
if (!is_string($action) || !$targetId || $targetId < 1) {
    header('Location: ../Nueva_Area.php?msg=invalid');
    exit;
}

if ($action === 'update') {
    $name = $_POST['name'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (
        !is_string($name) ||
        !is_string($username) ||
        !is_string($email) ||
        !is_string($password)
    ) {
        header('Location: ../Nueva_Area.php?edit=' . $targetId . '&msg=invalid');
        exit;
    }

    $name = trim($name);
    $username = trim($username);
    $email = trim($email);

    if (
        $name === '' ||
        strlen($name) > 100 ||
        !preg_match('/^[A-Za-z0-9_]{1,30}$/', $username) ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 254 ||
        ($password !== '' && (strlen($password) < 8 || strlen($password) > 72))
    ) {
        header('Location: ../Nueva_Area.php?edit=' . $targetId . '&msg=invalid');
        exit;
    }

    if ($password === '') {
        $updateStatement = $conn->prepare(
            'UPDATE tbl_users
             SET name_users = :name, username_users = :username, email_users = :email
             WHERE id_users = :user_id'
        );
        $updateStatement->bindValue(':name', $name);
        $updateStatement->bindValue(':username', $username);
        $updateStatement->bindValue(':email', $email);
        $updateStatement->bindValue(':user_id', $targetId, PDO::PARAM_INT);
    } else {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $updateStatement = $conn->prepare(
            'UPDATE tbl_users
             SET name_users = :name, username_users = :username, email_users = :email,
                 password_users = :password
             WHERE id_users = :user_id'
        );
        $updateStatement->bindValue(':name', $name);
        $updateStatement->bindValue(':username', $username);
        $updateStatement->bindValue(':email', $email);
        $updateStatement->bindValue(':password', $passwordHash);
        $updateStatement->bindValue(':user_id', $targetId, PDO::PARAM_INT);
    }

    try {
        $updateStatement->execute();
    } catch (PDOException $exception) {
        if ($exception->getCode() === '23000') {
            header('Location: ../Nueva_Area.php?edit=' . $targetId . '&msg=exists');
            exit;
        }
        throw $exception;
    }

    if ($targetId === (int) $_SESSION['user_id']) {
        $_SESSION['username'] = $username;
    }

    header('Location: ../Nueva_Area.php?msg=updated');
    exit;
}

if ($action === 'delete') {
    $adminCountStatement = $conn->query(
        'SELECT COUNT(*) FROM tbl_users WHERE is_admin = 1'
    );
    $adminCount = (int) $adminCountStatement->fetchColumn();

    $targetStatement = $conn->prepare(
        'SELECT is_admin FROM tbl_users WHERE id_users = :user_id'
    );
    $targetStatement->bindValue(':user_id', $targetId, PDO::PARAM_INT);
    $targetStatement->execute();
    $targetIsAdmin = $targetStatement->fetchColumn();

    if (
        $targetId === (int) $_SESSION['user_id'] ||
        $targetIsAdmin === false ||
        ((int) $targetIsAdmin === 1 && $adminCount <= 1)
    ) {
        header('Location: ../Nueva_Area.php?msg=protected');
        exit;
    }

    // Se eliminan primero las filas relacionadas; las claves foraneas usan RESTRICT.
    $conn->beginTransaction();
    try {
        $deleteMessages = $conn->prepare(
            'DELETE FROM tbl_messages
             WHERE id_sender = :sender_id OR id_receiver = :receiver_id'
        );
        $deleteMessages->bindValue(':sender_id', $targetId, PDO::PARAM_INT);
        $deleteMessages->bindValue(':receiver_id', $targetId, PDO::PARAM_INT);
        $deleteMessages->execute();

        $deleteRequests = $conn->prepare(
            'DELETE FROM tbl_friend_request
             WHERE id_sender = :sender_id OR id_receiver = :receiver_id'
        );
        $deleteRequests->bindValue(':sender_id', $targetId, PDO::PARAM_INT);
        $deleteRequests->bindValue(':receiver_id', $targetId, PDO::PARAM_INT);
        $deleteRequests->execute();

        $deleteUser = $conn->prepare(
            'DELETE FROM tbl_users WHERE id_users = :user_id'
        );
        $deleteUser->bindValue(':user_id', $targetId, PDO::PARAM_INT);
        $deleteUser->execute();

        $conn->commit();
    } catch (PDOException $exception) {
        $conn->rollBack();
        throw $exception;
    }

    header('Location: ../Nueva_Area.php?msg=deleted');
    exit;
}

header('Location: ../Nueva_Area.php?msg=invalid');
exit;
