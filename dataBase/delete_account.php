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

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../account.php");
    exit();
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($csrfToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    $_SESSION['account_delete_message'] = "Richiesta non valida. Riprova.";
    header("Location: ../account.php");
    exit();
}

$password = $_POST['current_password'] ?? '';
if (empty($password)) {
    $_SESSION['account_delete_message'] = "Inserisci la password per confermare l'eliminazione.";
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
    $_SESSION['account_delete_message'] = "Errore interno. Riprova.";
    header("Location: ../account.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("SELECT email, name, password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_email, $user_name, $hashed_password);
$stmt->fetch();
$stmt->close();

if (!$user_email || !password_verify($password, $hashed_password)) {
    $_SESSION['account_delete_message'] = "Password non corretta.";
    $conn->close();
    header("Location: ../account.php");
    exit();
}

$token = bin2hex(random_bytes(32));
$expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

$stmt = $conn->prepare("UPDATE users SET deletion_token = ?, deletion_token_expires = ? WHERE id = ?");
$stmt->bind_param("ssi", $token, $expires_at, $user_id);

if (!$stmt->execute()) {
    $_SESSION['account_delete_message'] = "Errore interno. Riprova.";
    $stmt->close();
    $conn->close();
    header("Location: ../account.php");
    exit();
}
$stmt->close();

$appDomain = getenv('APP_DOMAIN') ?: 'patchpulse.org';
$confirmation_link = "https://{$appDomain}/dataBase/confirm_deletion.php?token=" . urlencode($token);

$safeName = htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8');
$safeLink = htmlspecialchars($confirmation_link, ENT_QUOTES, 'UTF-8');

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
    $mail->addAddress($user_email);

    $mail->isHTML(true);
    $mail->Subject = 'Conferma Eliminazione Account';
    $mail->Body = "<p>Ciao {$safeName},</p><p>Hai richiesto di eliminare il tuo account. Clicca per confermare:</p><p><a href='{$safeLink}'>Conferma Eliminazione</a></p><p>Il link scade tra 1 ora. Se non hai richiesto questa operazione, ignora questa email e cambia la tua password.</p><p>Il Team di PatchPulse</p>";

    $mail->send();
    $_SESSION['account_delete_message'] = "Email di conferma inviata. Controlla la tua casella.";
} catch (Exception $e) {
    error_log("PHPMailer error: " . $mail->ErrorInfo);
    $_SESSION['account_delete_message'] = "Errore nell'invio dell'email. Riprova.";
}

$conn->close();
header("Location: ../account.php");
exit();
