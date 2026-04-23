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
    $_SESSION['registration_message'] = "Errore interno. Riprova.";
    header("Location: ../log-reg.php");
    exit();
}

$token = $_GET['token'] ?? '';
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    $_SESSION['registration_message'] = "Link non valido.";
    $conn->close();
    header("Location: ../log-reg.php");
    exit();
}

$stmt = $conn->prepare("SELECT id FROM users WHERE deletion_token = ? AND deletion_token_expires > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $_SESSION['registration_message'] = "Link non valido o scaduto.";
    $stmt->close();
    $conn->close();
    header("Location: ../log-reg.php");
    exit();
}

$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

$conn->begin_transaction();
try {
    $del_logs = $conn->prepare("DELETE FROM activity_logs WHERE user_id = ?");
    $del_logs->bind_param("i", $user_id);
    $del_logs->execute();
    $del_logs->close();

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
    $_SESSION['registration_message'] = "Errore durante l'eliminazione. Riprova.";
    $conn->close();
    header("Location: ../log-reg.php");
    exit();
}

$conn->close();

session_unset();
session_destroy();

session_start();
$_SESSION['login_message'] = "Account eliminato con successo.";
header("Location: ../log-reg.php");
exit();
