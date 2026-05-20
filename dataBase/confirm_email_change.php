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
    $_SESSION['email_change_message'] = "flash.internal_error_retry";
    header("Location: ../account.php");
    exit();
}

// Validazione token (64 hex chars)
$token = $_GET['token'] ?? '';
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    $_SESSION['email_change_message'] = "flash.email.link_invalid";
    $conn->close();
    header("Location: ../account.php");
    exit();
}

// Cerca token e recupera la nuova email
$stmt = $conn->prepare("SELECT id, temp_email FROM users WHERE confirmation_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $_SESSION['email_change_message'] = "flash.email.link_expired";
    $stmt->close();
    $conn->close();
    header("Location: ../account.php");
    exit();
}

$stmt->bind_result($user_id, $temp_email);
$stmt->fetch();
$stmt->close();

if (empty($temp_email)) {
    $_SESSION['email_change_message'] = "flash.email.no_pending";
    $conn->close();
    header("Location: ../account.php");
    exit();
}

// Verifica che la nuova email non sia nel frattempo stata presa
$check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$check->bind_param("si", $temp_email, $user_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $_SESSION['email_change_message'] = "flash.email.taken_other";
    $check->close();
    $conn->close();
    header("Location: ../account.php");
    exit();
}
$check->close();

// Aggiorna email e pulisci token
$update_stmt = $conn->prepare("UPDATE users SET email = ?, temp_email = NULL, confirmation_token = NULL WHERE id = ?");
$update_stmt->bind_param("si", $temp_email, $user_id);

if ($update_stmt->execute()) {
    // Aggiorna anche la sessione se è l'utente corrente
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
        $_SESSION['email'] = $temp_email;
    }
    $_SESSION['email_change_message'] = "flash.email.changed";
} else {
    $_SESSION['email_change_message'] = "flash.email.update_failed";
}

$update_stmt->close();
$conn->close();

header("Location: ../account.php");
exit();
