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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../log-reg.php");
    exit();
}

// CSRF
$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($csrfToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    $_SESSION['login_message'] = "flash.invalid_request";
    header("Location: ../log-reg.php");
    exit();
}

// Rate limiting: max 3 richieste reset ogni 15 minuti
$rateKey = 'forgot_attempts';
$rateLockKey = 'forgot_lockout';
if (isset($_SESSION[$rateLockKey]) && time() < $_SESSION[$rateLockKey]) {
    $_SESSION['login_message'] = "flash.too_many_requests";
    header("Location: ../log-reg.php");
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
    $_SESSION['login_message'] = "flash.internal_error";
    header("Location: ../log-reg.php");
    exit();
}

$email = filter_var(trim($_POST['EmailOfUser'] ?? ''), FILTER_SANITIZE_EMAIL);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['login_message'] = "flash.invalid_email";
    $conn->close();
    header("Location: ../log-reg.php");
    exit();
}

// Rate limit tracking
$_SESSION[$rateKey] = ($_SESSION[$rateKey] ?? 0) + 1;
if ($_SESSION[$rateKey] >= 3) {
    $_SESSION[$rateLockKey] = time() + 900; // 15 minuti
    $_SESSION[$rateKey] = 0;
}

// Messaggio generico per non rivelare se l'email esiste
$genericMessage = "Se l'email è registrata, riceverai un link per reimpostare la password.";

// Cerca utente
$stmt = $conn->prepare("SELECT id, name, is_confirmed FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    // Non rivelare che l'email non esiste — timing costante
    usleep(random_int(100000, 300000)); // 100-300ms delay
    $stmt->close();
    $conn->close();
    $_SESSION['login_message'] = $genericMessage;
    header("Location: ../log-reg.php");
    exit();
}

$stmt->bind_result($userId, $userName, $isConfirmed);
$stmt->fetch();
$stmt->close();

if (!$isConfirmed) {
    usleep(random_int(100000, 300000));
    $conn->close();
    $_SESSION['login_message'] = $genericMessage;
    header("Location: ../log-reg.php");
    exit();
}

// Genera token reset (64 hex chars = 32 bytes)
$resetToken = bin2hex(random_bytes(32));
$expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Salva token nel DB
$update = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
$update->bind_param("ssi", $resetToken, $expiresAt, $userId);

if (!$update->execute()) {
    $update->close();
    $conn->close();
    $_SESSION['login_message'] = "flash.internal_error_retry";
    header("Location: ../log-reg.php");
    exit();
}
$update->close();

// Log
$userIp = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? 'unknown';
if (!filter_var($userIp, FILTER_VALIDATE_IP)) $userIp = 'unknown';

$log = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, 'password_reset_requested', ?)");
$log->bind_param("is", $userId, $userIp);
$log->execute();
$log->close();

// Invia email
$appDomain = getenv('APP_DOMAIN') ?: 'patchpulse.org';
$resetLink = "https://{$appDomain}/dataBase/reset_password.php?token=" . urlencode($resetToken);
$safeName = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
$safeLink = htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8');

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

    $mail->setFrom(getenv('SMTP_FROM') ?: 'support@email.mrtc.cc', 'PatchPulse');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = t('mail.reset.subject', false);
    // Pre-resolve translated strings (escape=false because we control the surrounding HTML).
    $h1       = htmlspecialchars(t('mail.reset.h1', false), ENT_QUOTES, 'UTF-8');
    $greeting = htmlspecialchars(str_replace('{0}', $safeName, t('mail.greeting_name', false)), ENT_QUOTES, 'UTF-8');
    $intro    = htmlspecialchars(t('mail.reset.intro', false), ENT_QUOTES, 'UTF-8');
    $btn      = htmlspecialchars(t('mail.reset.button', false), ENT_QUOTES, 'UTF-8');
    $expiry   = htmlspecialchars(t('mail.reset.expiry', false), ENT_QUOTES, 'UTF-8');
    $fallback = htmlspecialchars(t('mail.reset.fallback_link', false), ENT_QUOTES, 'UTF-8');
    $mail->Body = "
        <div style='font-family:sans-serif;max-width:600px;margin:0 auto;padding:30px;background:#fff;border-radius:12px;border:1px solid #eee'>
            <h2 style='color:#1a1a1a;text-align:center'>{$h1}</h2>
            <p style='color:#555'>{$greeting}</p>
            <p style='color:#555'>{$intro}</p>
            <div style='text-align:center;margin:30px 0'>
                <a href='{$safeLink}' style='display:inline-block;background:#8b7cf8;color:#fff;padding:14px 28px;border-radius:50px;text-decoration:none;font-weight:600'>{$btn}</a>
            </div>
            <p style='color:#999;font-size:14px'>{$expiry}</p>
            <p style='color:#999;font-size:14px'>{$fallback} <a href='{$safeLink}' style='color:#8b7cf8'>{$safeLink}</a></p>
            <hr style='border:none;border-top:1px solid #eee;margin:20px 0'>
            <p style='color:#bbb;font-size:12px;text-align:center'>PatchPulse</p>
        </div>
    ";

    $mail->send();
} catch (Exception $e) {
    error_log("Forgot password email error: " . $mail->ErrorInfo);
}

$conn->close();
$_SESSION['login_message'] = $genericMessage;
header("Location: ../log-reg.php");
exit();
