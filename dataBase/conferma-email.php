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
    $_SESSION['registration_message'] = "flash.internal_error";
    header("Location: ../log-reg.php");
    exit();
}

// Validazione rigorosa del token (64 caratteri hex)
$token = $_GET['token'] ?? '';
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    $_SESSION['registration_message'] = "flash.register.link_invalid";
    $conn->close();
    header("Location: ../log-reg.php");
    exit();
}

// Il DB conserva l'hash del token: confronta l'hash dell'input.
$tokenHash = hash('sha256', $token);

$stmt = @$conn->prepare("UPDATE users SET is_confirmed = TRUE, confirmation_token = NULL, confirmation_expires = NULL
                         WHERE confirmation_token = ? AND is_confirmed = FALSE
                           AND (confirmation_expires IS NULL OR confirmation_expires > NOW())");
if (!$stmt) { // colonna non ancora presente (migration non applicata)
    $stmt = $conn->prepare("UPDATE users SET is_confirmed = TRUE, confirmation_token = NULL WHERE confirmation_token = ? AND is_confirmed = FALSE");
}
$stmt->bind_param("s", $tokenHash);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $_SESSION['registration_message'] = "flash.register.confirmed";
} else {
    $_SESSION['registration_message'] = "flash.register.link_used";
}

$stmt->close();
$conn->close();

header("Location: ../log-reg.php");
exit();
