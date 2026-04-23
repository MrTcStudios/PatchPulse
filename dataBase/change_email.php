<?php
ini_set('display_errors', 0);
error_reporting(0);

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../log-reg.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['change_email'])) {
    header("Location: ../account.php");
    exit();
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($csrfToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    $_SESSION['email_change_message'] = "Richiesta non valida. Riprova.";
    header("Location: ../account.php");
    exit();
}

$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    $_SESSION['email_change_message'] = "Errore interno. Riprova più tardi.";
    header("Location: ../account.php");
    exit();
}

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$user_id = (int)$_SESSION['user_id'];
$current_password = $_POST['current_password'] ?? '';
$new_email = filter_var(trim($_POST['new_email'] ?? ''), FILTER_SANITIZE_EMAIL);

if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['email_change_message'] = "La nuova email non è valida.";
    $conn->close();
    header("Location: ../account.php");
    exit();
}

if (empty($current_password)) {
    $_SESSION['email_change_message'] = "Inserisci la password attuale per confermare.";
    $conn->close();
    header("Location: ../account.php");
    exit();
}

$stmt = $conn->prepare("SELECT email, password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($db_email, $hashed_password);
$stmt->fetch();
$stmt->close();

if (!password_verify($current_password, $hashed_password)) {
    $_SESSION['email_change_message'] = "Password non corretta.";
    $conn->close();
    header("Location: ../account.php");
    exit();
}

if ($new_email === $db_email) {
    $_SESSION['email_change_message'] = "La nuova email è uguale a quella attuale.";
    $conn->close();
    header("Location: ../account.php");
    exit();
}

$check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check_stmt->bind_param("s", $new_email);
$check_stmt->execute();
$check_stmt->store_result();
if ($check_stmt->num_rows > 0) {
    $_SESSION['email_change_message'] = "L'email inserita è già in uso.";
    $check_stmt->close();
    $conn->close();
    header("Location: ../account.php");
    exit();
}
$check_stmt->close();

$confirmation_token = bin2hex(random_bytes(32));

$update_stmt = $conn->prepare("UPDATE users SET temp_email = ?, confirmation_token = ? WHERE id = ?");
$update_stmt->bind_param("ssi", $new_email, $confirmation_token, $user_id);

if (!$update_stmt->execute()) {
    $_SESSION['email_change_message'] = "Errore interno. Riprova.";
    $update_stmt->close();
    $conn->close();
    header("Location: ../account.php");
    exit();
}
$update_stmt->close();

$user_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? 'unknown';
if (!filter_var($user_ip, FILTER_VALIDATE_IP)) {
    $user_ip = 'unknown';
}

$log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, 'email_change_requested', ?)");
$log_stmt->bind_param("is", $user_id, $user_ip);
$log_stmt->execute();
$log_stmt->close();

$appDomain = getenv('APP_DOMAIN') ?: 'patchpulse.org';
$confirmation_link = "https://{$appDomain}/dataBase/confirm_email_change.php?token=" . urlencode($confirmation_token);

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
    $mail->addAddress($new_email);

    $mail->isHTML(true);
    $mail->Subject = 'Conferma cambio email';
    $mail->Body = "<p>Per confermare il cambio della tua email, clicca sul <a href='" . htmlspecialchars($confirmation_link, ENT_QUOTES, 'UTF-8') . "'>link di conferma</a>.</p><p>Se non hai richiesto questa operazione, ignora questa email.</p>";

    $mail->send();
    $_SESSION['email_change_message'] = "Email di conferma inviata alla nuova email.";
} catch (Exception $e) {
    error_log("PHPMailer error: " . $mail->ErrorInfo);
    $_SESSION['email_change_message'] = "Errore nell'invio dell'email. Riprova.";
}

$conn->close();
header("Location: ../account.php");
exit();
