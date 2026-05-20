<?php
ini_set('display_errors', 0);
error_reporting(0);

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

session_start();

require_once __DIR__ . "/../lang/lang.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../log-reg.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['change_password'])) {
    header("Location: ../account.php");
    exit();
}

// CSRF
$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($csrfToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    $_SESSION['password_change_message'] = "flash.invalid_request";
    header("Location: ../account.php");
    exit();
}

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    $_SESSION['password_change_message'] = "flash.internal_error";
    header("Location: ../account.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
// NON applicare htmlspecialchars alle password
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_new_password = $_POST['confirm_new_password'] ?? '';

// Validazioni
if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
    $_SESSION['password_change_message'] = "flash.fill_all_fields";
    $conn->close();
    header("Location: ../account.php");
    exit();
}

if ($new_password !== $confirm_new_password) {
    $_SESSION['password_change_message'] = "flash.register.pwd_mismatch";
    $conn->close();
    header("Location: ../account.php");
    exit();
}

// Forza password
if (mb_strlen($new_password) < 8) {
    $_SESSION['password_change_message'] = "flash.register.weak_pwd";
    $conn->close();
    header("Location: ../account.php");
    exit();
}
if (mb_strlen($new_password) > 128) {
    $_SESSION['password_change_message'] = "flash.register.long_pwd";
    $conn->close();
    header("Location: ../account.php");
    exit();
}
if (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
    $_SESSION['password_change_message'] = "flash.register.pwd_complexity";
    $conn->close();
    header("Location: ../account.php");
    exit();
}

// Verifica password attuale
$stmt = $conn->prepare("SELECT password, email, name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($hashed_password, $user_email, $user_name);
$stmt->fetch();
$stmt->close();

if (!password_verify($current_password, $hashed_password)) {
    $_SESSION['password_change_message'] = "flash.password.wrong_current";
    $conn->close();
    header("Location: ../account.php");
    exit();
}

// Aggiorna password (bcrypt cost 12)
$new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);

$update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$update_stmt->bind_param("si", $new_hashed_password, $user_id);

if (!$update_stmt->execute()) {
    $_SESSION['password_change_message'] = "flash.password.change_failed";
    $update_stmt->close();
    $conn->close();
    header("Location: ../account.php");
    exit();
}
$update_stmt->close();

// Rigenera sessione dopo cambio password
session_regenerate_id(true);

// Log
$user_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? 'unknown';
if (!filter_var($user_ip, FILTER_VALIDATE_IP)) {
    $user_ip = 'unknown';
}

$log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, 'password_changed', ?)");
$log_stmt->bind_param("is", $user_id, $user_ip);
$log_stmt->execute();
$log_stmt->close();

$_SESSION['password_change_message'] = "flash.password.changed";

// Email notifica
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = getenv('SMTP_HOST') ?: 'smtp-relay.brevo.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = getenv('SMTP_USER');
    $mail->Password   = getenv('SMTP_PASS');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->Timeout    = 10;

    $mail->setFrom(getenv('SMTP_FROM') ?: 'support@patchpulse.org', 'PatchPulse');
    $mail->addAddress($user_email);

    $safeName = htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8');
    $safeIp = htmlspecialchars($user_ip, ENT_QUOTES, 'UTF-8');

    $mail->isHTML(true);
    $mail->Subject = t('mail.pwd_changed.subject', false);
    $mail->Body = str_replace(['{0}', '{1}'], [$safeName, $safeIp], t('mail.pwd_changed.body', false));

    $mail->send();
} catch (Exception $e) {
    error_log("PHPMailer error: " . $mail->ErrorInfo);
}

$conn->close();
header("Location: ../account.php");
exit();
