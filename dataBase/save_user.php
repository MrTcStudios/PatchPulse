<?php
session_start();

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../webhook/registerWebhook.php';

$servername = "";
$username = "";
$password = "";
$dbname = "PatchPulseBeta";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$secretKey = "";

// Verifica se il form e' stato inviato
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupera il token inviato dal form
    $captchaResponse = $_POST['cf-turnstile-response'] ?? '';
    $remoteIp = $_SERVER['REMOTE_ADDR'] ?? '';

    // Verifica che il token esista
    if (empty($captchaResponse)) {
        die("Errore: CAPTCHA non risolto.");
    }

    // Configura la chiamata API per verificare il token
    $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $data = [
        'secret' => $secretKey,
        'response' => $captchaResponse,
        'remoteip' => $remoteIp
    ];

    // Invia la richiesta a Cloudflare
    $options = [
        'http' => [
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];
    $context  = stream_context_create($options);
    $result   = file_get_contents($verifyUrl, false, $context);
    $responseData = json_decode($result);

    // Controlla la risposta del CAPTCHA
    if (!$responseData->success) {
        die("Errore nella verifica del CAPTCHA.");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = filter_var($_POST['NameOfUser'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['EmailOfUser'], FILTER_SANITIZE_EMAIL);
    $password = htmlspecialchars($_POST['PasswordOfUserUnCrypt']);
    $agree_terms = isset($_POST['AgreeTerms']) ? 1 : 0;

    // Verifica se l'email è valida
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	$_SESSION['registration_message'] = "Errore: L'email inserita non e' valida.";
        header("Location: ../registerPage.php");
        exit();
    }

    // Verifica se l'email esiste già
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
         $_SESSION['registration_message'] = "Errore: L'email gia' registrata. Usa un'altra email.";
         header("Location: ../registerPage.php");
         exit();
    } else {
        // Cripta la password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Genera un token di conferma
        $confirmation_token = bin2hex(random_bytes(32));

        // Inserisce l'utente nel database
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, agree_terms, is_confirmed, confirmation_token) VALUES (?, ?, ?, ?, FALSE, ?)");
        $stmt->bind_param("sssis", $name, $email, $hashed_password, $agree_terms, $confirmation_token);

        if ($stmt->execute()) {

            sendDiscordWebhook($name, $email);

            ob_start();
            include '../email/confirm_account.php';
            $message = ob_get_clean();

            $confirmation_link = "https://mrtc.cc/PatchPulse/dataBase/conferma-email.php?token=" . $confirmation_token;
            $message = str_replace('YOUR_TOKEN', $confirmation_token, $message);
            $message = str_replace('YOUR_LINK', $confirmation_link, $message);
            $message = str_replace('YOUR_EMAIL', $email, $message);

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
                $mail->Subject = 'Conferma la tua registrazione';
                $mail->Body    = $message;

                $mail->send();
		$_SESSION['registration_message'] = "Controlla la tua email per confermare la registrazione.";
                header("Location: ../registerPage.php");
                exit();
            } catch (Exception $e) {
		$_SESSION['registration_message'] = "Errore nell'invio della' email di conferma: " . $mail->ErrorInfo;
                header("Location: ../registerPage.php");
                exit();
            }
        } else {
		$_SESSION['registration_message'] = "Errore: " . $stmt->error;
            header("Location: ../registerPage.php");
            exit();
        }
    }

    $stmt->close();
    $conn->close();
}


