<?php
ob_start();

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

session_start();

ini_set('display_errors', 0);
error_reporting(0);

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../webhook/registerWebhook.php';

$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    $_SESSION['registration_message'] = "Errore interno. Riprova più tardi.";
    header("Location: ../log-reg.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../log-reg.php");
    exit();
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($csrfToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    $_SESSION['registration_message'] = "Richiesta non valida. Riprova.";
    header("Location: ../log-reg.php");
    exit();
}

$captchaResponse = $_POST['cf-turnstile-response'] ?? '';
if (empty($captchaResponse)) {
    $_SESSION['registration_message'] = "Completa la verifica CAPTCHA.";
    header("Location: ../log-reg.php");
    exit();
}

$secretKey = getenv('TURNSTILE_SECRET_KEY');
$verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
$verifyData = [
    'secret'   => $secretKey,
    'response'  => $captchaResponse,
    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
];
$context = stream_context_create([
    'http' => [
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($verifyData),
        'timeout' => 5,
    ],
]);
$result = @file_get_contents($verifyUrl, false, $context);
$responseData = $result ? json_decode($result) : null;

if (!$responseData || !$responseData->success) {
    $_SESSION['registration_message'] = "Verifica CAPTCHA fallita. Riprova.";
    header("Location: ../log-reg.php");
    exit();
}

$name = trim($_POST['NameOfUser'] ?? '');
$email = filter_var(trim($_POST['EmailOfUser'] ?? ''), FILTER_SANITIZE_EMAIL);
$password = $_POST['PasswordOfUserUnCrypt'] ?? '';
$agree_terms = isset($_POST['AgreeTerms']) ? 1 : 0;

if (empty($name) || mb_strlen($name) > 100) {
    $_SESSION['registration_message'] = "Nome utente non valido (max 100 caratteri).";
    header("Location: ../log-reg.php");
    exit();
}

if (!preg_match('/^[\p{L}\p{N}\s\-_]{1,100}$/u', $name)) {
    $_SESSION['registration_message'] = "Il nome utente contiene caratteri non validi.";
    header("Location: ../log-reg.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['registration_message'] = "L'email inserita non è valida.";
    header("Location: ../log-reg.php");
    exit();
}

if (mb_strlen($password) < 8) {
    $_SESSION['registration_message'] = "La password deve avere almeno 8 caratteri.";
    header("Location: ../log-reg.php");
    exit();
}
if (mb_strlen($password) > 128) {
    $_SESSION['registration_message'] = "La password è troppo lunga (max 128 caratteri).";
    header("Location: ../log-reg.php");
    exit();
}
if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $_SESSION['registration_message'] = "La password deve contenere almeno una maiuscola, una minuscola e un numero.";
    header("Location: ../log-reg.php");
    exit();
}

if (!$agree_terms) {
    $_SESSION['registration_message'] = "Devi accettare i Termini di Servizio.";
    header("Location: ../log-reg.php");
    exit();
}

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
if (!$stmt) {
    $_SESSION['registration_message'] = "Errore interno. Riprova più tardi.";
    header("Location: ../log-reg.php");
    exit();
}
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['registration_message'] = "Se l'email non è già registrata, riceverai un messaggio di conferma.";
    $stmt->close();
    $conn->close();
    header("Location: ../log-reg.php");
    exit();
}
$stmt->close();

$hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
$confirmation_token = bin2hex(random_bytes(32));

$stmt = $conn->prepare("INSERT INTO users (name, email, password, agree_terms, is_confirmed, confirmation_token) VALUES (?, ?, ?, ?, FALSE, ?)");
$stmt->bind_param("sssis", $name, $email, $hashed_password, $agree_terms, $confirmation_token);

if (!$stmt->execute()) {
    $_SESSION['registration_message'] = "Errore durante la registrazione. Riprova.";
    $stmt->close();
    $conn->close();
    header("Location: ../log-reg.php");
    exit();
}

$stmt->close();

sendDiscordWebhook($name, $email);

$appDomain = getenv('APP_DOMAIN') ?: 'patchpulse.org';
$confirmation_link = "https://{$appDomain}/dataBase/conferma-email.php?token=" . urlencode($confirmation_token);

ob_start();
include '../email/confirm_account.php';
$message = ob_get_clean();
$message = str_replace('YOUR_TOKEN', htmlspecialchars($confirmation_token, ENT_QUOTES, 'UTF-8'), $message);
$message = str_replace('YOUR_LINK', htmlspecialchars($confirmation_link, ENT_QUOTES, 'UTF-8'), $message);
$message = str_replace('YOUR_EMAIL', htmlspecialchars($email, ENT_QUOTES, 'UTF-8'), $message);

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
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Conferma la tua registrazione';
    $mail->Body    = $message;

    $mail->send();
} catch (Exception $e) {
    error_log("PHPMailer error: " . $mail->ErrorInfo);
}

$_SESSION['registration_message'] = "Se l'email non è già registrata, riceverai un messaggio di conferma.";
$conn->close();
header("Location: ../log-reg.php");
exit();
