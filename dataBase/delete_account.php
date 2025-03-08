<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../loginPage.php");
    exit();
}

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$servername = "";
$username = "";
$password = "";
$dbname = "PatchPulseBeta";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Ottiene i dettagli dell'utente
$stmt = $conn->prepare("SELECT email, name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_email, $user_name);
$stmt->fetch();
$stmt->close();

if (!$user_email) {
    $_SESSION['account_delete_message'] = "Errore: utente non trovato.";
    header("Location: ../accountPage.php");
    exit();
}

// Genera un token univoco e una scadenza
$token = bin2hex(random_bytes(16));
$expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

// Salva il token e la scadenza nel database
$stmt = $conn->prepare("UPDATE users SET deletion_token = ?, deletion_token_expires = ? WHERE id = ?");
$stmt->bind_param("ssi", $token, $expires_at, $user_id);

if ($stmt->execute()) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp-relay.brevo.com';
        $mail->SMTPAuth = true;
        $mail->Username = '';
        $mail->Password = '';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('support@email.mrtc.cc', 'PatchPulse Support');
        $mail->addAddress($user_email);

        $confirmation_link = "https://mrtc.cc/PatchPulse/dataBase/confirm_deletion.php?token=" . $token;

        $mail->isHTML(true);
        $mail->Subject = 'Conferma Eliminazione Account';
        $mail->Body = "
            <p>Ciao {$user_name},</p>
            <p>Hai richiesto di eliminare il tuo account. Per completare l'eliminazione, clicca sul link seguente:</p>
            <p><a href='{$confirmation_link}'>Conferma Eliminazione</a></p>
            <p>Se non hai richiesto questa operazione, ignora questa email e cambia immediatamente la tua password.</p>
            <p>Grazie,<br>Il Team di PatchPulse</p>
        ";

        $mail->send();
        $_SESSION['account_delete_message'] = "Email di conferma inviata. Controlla la tua casella di posta.";
    } catch (Exception $e) {
        $_SESSION['account_delete_message'] = "Errore nell'invio dell'email di conferma: " . $mail->ErrorInfo;
    }
} else {
    $_SESSION['account_delete_message'] = "Errore durante la richiesta di eliminazione: " . $stmt->error;
}

$stmt->close();
$conn->close();

header("Location: ../accountPage.php");
exit();
?>
