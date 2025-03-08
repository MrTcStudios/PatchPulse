<?php
ignore_user_abort(true);
set_time_limit(0);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include("../config.php");

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function makeClickableLinks($text) {
    return preg_replace(
        '~(https?://[^\s]+)~',
        '<a href="$1" target="_blank">$1</a>',
        $text
    );
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = trim($_POST["subject"]);
    $message = trim($_POST["message"]);
    
    if (empty($subject) || empty($message)) {
        $_SESSION['error'] = "L'oggetto e il corpo del messaggio sono obbligatori.";
        header("Location: dashboard.php");
        exit;
    }

    $_SESSION['sending_status'] = "Invio delle email in corso...";
    session_write_close();

    $query = "SELECT email FROM users";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $totalEmails = $result->num_rows;
        $sentEmails = 0;
        
        while ($row = $result->fetch_assoc()) {
            $email = $row["email"];
            $mail = new PHPMailer(true);
            
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp-relay.brevo.com';
                $mail->SMTPAuth = true;
                $mail->Username = '';
                $mail->Password = '';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
                $mail->setFrom('support@email.mrtc.cc', 'PatchPulseUser');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = nl2br(makeClickableLinks($message));
                $mail->send();
                $sentEmails++;
                
                session_start();
                $_SESSION['sending_status'] = "Invio in corso: $sentEmails su $totalEmails...";
                session_write_close();
            } catch (Exception $e) {
                error_log('Errore email: ' . $mail->ErrorInfo);
            }
        }
        
        session_start();
        $_SESSION['message_email'] = "Email inviate con successo: $sentEmails su $totalEmails";
        unset($_SESSION['sending_status']);
        session_write_close();
    } else {
        session_start();
        $_SESSION['error'] = "Nessun utente trovato.";
        session_write_close();
    }
    
    header("Location: dashboard.php");
    exit;
}
?>
