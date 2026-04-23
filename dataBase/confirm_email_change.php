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
    $_SESSION['email_change_message'] = "Errore interno. Riprova.";
    header("Location: ../account.php");
    exit();
}

$token = $_GET['token'] ?? '';
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    $_SESSION['email_change_message'] = "Link non valido.";
    $conn->close();
    header("Location: ../account.php");
    exit();
}

$stmt = $conn->prepare("SELECT id, temp_email FROM users WHERE confirmation_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $_SESSION['email_change_message'] = "Link non valido o già utilizzato.";
    $stmt->close();
    $conn->close();
    header("Location: ../account.php");
    exit();
}

$stmt->bind_result($user_id, $temp_email);
$stmt->fetch();
$stmt->close();

if (empty($temp_email)) {
    $_SESSION['email_change_message'] = "Nessun cambio email in sospeso.";
    $conn->close();
    header("Location: ../account.php");
    exit();
}

$check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$check->bind_param("si", $temp_email, $user_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $_SESSION['email_change_message'] = "L'email è già in uso da un altro account.";
    $check->close();
    $conn->close();
    header("Location: ../account.php");
    exit();
}
$check->close();

$update_stmt = $conn->prepare("UPDATE users SET email = ?, temp_email = NULL, confirmation_token = NULL WHERE id = ?");
$update_stmt->bind_param("si", $temp_email, $user_id);

if ($update_stmt->execute()) {
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
        $_SESSION['email'] = $temp_email;
    }
    $_SESSION['email_change_message'] = "Email aggiornata con successo.";
} else {
    $_SESSION['email_change_message'] = "Errore nell'aggiornamento. Riprova.";
}

$update_stmt->close();
$conn->close();

header("Location: ../account.php");
exit();
