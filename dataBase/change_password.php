<?php
session_start();


function detectDevice() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent)) {
        return "mobile";
    }
    return "desktop";
}

// Usa questa funzione per il reindirizzamento
$deviceType = detectDevice();
$_SESSION['deviceType'] = $deviceType; // salva in sessione se necessario


if (!isset($_SESSION['user_id'])) {
    if ($deviceType === "mobile") {
            header("Location: ../loginPage_mobile.php");
        } else {
            header("Location: ../loginPage.php");
        }
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

// Cambio password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = htmlspecialchars($_POST['current_password']);
    $new_password = htmlspecialchars($_POST['new_password']);
    $confirm_new_password = htmlspecialchars($_POST['confirm_new_password']);
    $user_id = $_SESSION['user_id'];

    if ($new_password !== $confirm_new_password) {
        $_SESSION['password_change_message'] = "Le nuove password non coincidono.";
        if ($deviceType === "mobile") {
            header("Location: ../accountPage_mobile.php");
        } else {
            header("Location: ../accountPage.php");
        }
        exit();
    }

    // Ottiene la password attuale dell'utente
    $stmt = $conn->prepare("SELECT password, email, name FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hashed_password, $user_email, $user_name);
    $stmt->fetch();

    // Verifica la password attuale
    if (password_verify($current_password, $hashed_password)) {
        // Ottiene l'IP dal campo Cloudflare
        $user_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? 'unknown IP';
        if (!filter_var($user_ip, FILTER_VALIDATE_IP)) {
            $user_ip = 'invalid IP';
        }

        // Ottiene il paese dal campo Cloudflare
        $user_country = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? 'unknown country';
        if (!preg_match('/^[A-Z]{2}$/', $user_country)) {
            $user_country = 'invalid country';
        }

        error_log("User IP: " . $user_ip);
        error_log("User Country: " . $user_country);

        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt->close();

        // Aggiorna la password nel database
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_hashed_password, $user_id);


	$stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, 'Password changed', ?)");
	$stmt->bind_param("is", $user_id, $user_ip);
	$stmt->execute();
	$stmt->close();
	

        if ($update_stmt->execute()) {
            $_SESSION['password_change_message'] = "Password cambiata con successo!";

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
                $mail->addAddress($user_email);

                // Configura il contenuto dell'email
                $mail->isHTML(true);
                $mail->Subject = 'Notifica di Cambio Password';
                $mail->Body = "
                <p>Ciao {$user_name},</p>
                <p>La tua password è stata cambiata correttamente. Se non hai effettuato tu questa operazione, contattaci immediatamente.</p>
                <p>Dettagli della modifica:</p>
                <ul>
                <li>IP del dispositivo: {$user_ip}</li>
                <li>Paese: {$user_country}</li>
                </ul>
                <p>Grazie,<br>Il Team di PatchPulse</p>
                ";

                // Invia l'email
                $mail->send();
            } catch (Exception $e) {
                $_SESSION['password_change_message'] .= " Errore nella notifica email: " . $mail->ErrorInfo;
            }
        } else {
            $_SESSION['password_change_message'] = "Errore nel cambio della password: " . $update_stmt->error;
        }

        $update_stmt->close();
    } else {
        $_SESSION['password_change_message'] = "La password attuale non è corretta.";
    }

    $conn->close();

    // Reindirizza all'account page
    if ($deviceType === "mobile") {
            header("Location: ../accountPage_mobile.php");
        } else {
            header("Location: ../accountPage.php");
        }
    exit();
}
?>
