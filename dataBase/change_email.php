<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: loginPage.php");
    exit();
}

$servername = "";
$username = "";
$password = "";
$dbname = "PatchPulseBeta";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_email'])) {
    $current_email = htmlspecialchars($_POST['current_email']);
    $new_email = htmlspecialchars($_POST['new_email']);
    $user_id = $_SESSION['user_id'];

    //Controlla se l'email attuale è corretta
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($db_email);
    $stmt->fetch();
    
    if ($current_email === $db_email) {
        $stmt->close();

        //Verifica che la nuova email non sia già in uso
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $new_email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $_SESSION['email_change_message'] = "L'email inserita è già in uso.";
            $check_stmt->close();
            $conn->close();
            header("Location: ../accountPage.php");
            exit();
        }
        $check_stmt->close();

        // Genera un token di conferma
        $confirmation_token = bin2hex(random_bytes(32));

        // Salva temporaneamente la nuova email e il token nel database
        $update_stmt = $conn->prepare("UPDATE users SET temp_mail = ?, confirmation_token = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $new_email, $confirmation_token, $user_id);





	    // Recupera l'indirizzo IP da Cloudflare
        $user_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? 'unknown IP';
        if (!filter_var($user_ip, FILTER_VALIDATE_IP)) {
            $user_ip = 'invalid IP';
        }

        // Recupera il paese da Cloudflare
        $user_country = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? 'unknown country';
        if (!preg_match('/^[A-Z]{2}$/', $user_country)) {
            $user_country = 'invalid country';
        }

        // Logga l'IP e il paese
        error_log("User IP: " . $user_ip);
        error_log("User Country: " . $user_country);




        
	$stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, 'Email changed', ?)");
	$stmt->bind_param("is", $user_id, $user_ip);
	$stmt->execute();
	$stmt->close();



        if ($update_stmt->execute()) {
            $confirmation_link = "https://mrtc.cc/PatchPulse/dataBase/confirm_email_change.php?token=" . $confirmation_token;

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
                $mail->addAddress($new_email);

                $mail->isHTML(true);
                $mail->Subject = 'Conferma cambio email';
                $mail->Body = "<p>Per confermare il cambio della tua email, clicca sul <a href='$confirmation_link'>link di conferma</a>.</p>";

                $mail->send();
                $_SESSION['email_change_message'] = "Email di conferma inviata alla nuova email.";
            } catch (Exception $e) {
                $_SESSION['email_change_message'] = "Errore nell'invio dell'email di conferma: " . $mail->ErrorInfo;
            }
        } else {
            $_SESSION['email_change_message'] = "Errore nel salvataggio della nuova email: " . $update_stmt->error;
        }

        $update_stmt->close();
    } else {
        $_SESSION['email_change_message'] = "L'email attuale non è corretta.";
    }

    $conn->close();
    header("Location: ../accountPage.php");
    exit();
}
?>
