<?php
ini_set('display_errors', 0);
error_reporting(0);

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

include("../config.php");

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($csrfToken) || !isset($_SESSION['admin_csrf']) || !hash_equals($_SESSION['admin_csrf'], $csrfToken)) {
    $_SESSION['admin_error'] = "Richiesta non valida.";
    header("Location: dashboard.php");
    exit;
}

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($subject) || empty($message)) {
    $_SESSION['admin_error'] = "Oggetto e messaggio sono obbligatori.";
    header("Location: dashboard.php");
    exit;
}

if (mb_strlen($subject) > 200) {
    $_SESSION['admin_error'] = "Oggetto troppo lungo (max 200 caratteri).";
    header("Location: dashboard.php");
    exit;
}
if (mb_strlen($message) > 10000) {
    $_SESSION['admin_error'] = "Messaggio troppo lungo (max 10000 caratteri).";
    header("Location: dashboard.php");
    exit;
}

$safeSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
$safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

$stmt = $conn->prepare("SELECT email FROM users WHERE is_confirmed = TRUE");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $_SESSION['admin_error'] = "Nessun utente confermato trovato.";
    header("Location: dashboard.php");
    exit;
}

$totalEmails = $result->num_rows;
$sentEmails = 0;
$errors = 0;

set_time_limit(300);

while ($row = $result->fetch_assoc()) {
    $email = $row['email'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) continue;

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
        $mail->Subject = $safeSubject;
        $mail->Body    = $safeMessage;

        $mail->send();
        $sentEmails++;
    } catch (Exception $e) {
        error_log("Admin email error to {$email}: " . $mail->ErrorInfo);
        $errors++;
    }
}

$stmt->close();
$conn->close();

$_SESSION['admin_success'] = "Email inviate: {$sentEmails}/{$totalEmails}" . ($errors > 0 ? " ({$errors} errori)" : "");
header("Location: dashboard.php");
exit;
