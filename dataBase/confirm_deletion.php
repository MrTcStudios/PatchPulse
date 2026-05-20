<?php
ini_set('display_errors', 0);
error_reporting(0);

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

session_start();

$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    $_SESSION['registration_message'] = "flash.internal_error_retry";
    header("Location: ../log-reg.php");
    exit();
}

// Validazione token (32 bytes = 64 hex chars)
$token = $_GET['token'] ?? '';
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    $_SESSION['registration_message'] = "flash.email.link_invalid";
    $conn->close();
    header("Location: ../log-reg.php");
    exit();
}

// Cerca token e verifica scadenza in un'unica query atomica
$stmt = $conn->prepare("SELECT id FROM users WHERE deletion_token = ? AND deletion_token_expires > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $_SESSION['registration_message'] = "flash.account.confirm_link_invalid";
    $stmt->close();
    $conn->close();
    header("Location: ../log-reg.php");
    exit();
}

$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Elimina l'account (cascade o manual cleanup)
$conn->begin_transaction();
try {
    // Pulisci log
    $del_logs = $conn->prepare("DELETE FROM activity_logs WHERE user_id = ?");
    $del_logs->bind_param("i", $user_id);
    $del_logs->execute();
    $del_logs->close();

    // Elimina utente
    $del_user = $conn->prepare("DELETE FROM users WHERE id = ? AND deletion_token = ?");
    $del_user->bind_param("is", $user_id, $token);
    $del_user->execute();

    if ($del_user->affected_rows === 0) {
        throw new \Exception("Delete failed");
    }
    $del_user->close();

    $conn->commit();
} catch (\Exception $e) {
    $conn->rollback();
    $_SESSION['registration_message'] = "flash.account.delete_failed";
    $conn->close();
    header("Location: ../log-reg.php");
    exit();
}

$conn->close();

// Distruggi sessione
session_unset();
session_destroy();

// Nuova sessione per il messaggio
session_start();
$_SESSION['login_message'] = "flash.account.deleted";
header("Location: ../log-reg.php");
exit();
