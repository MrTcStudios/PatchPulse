<?php
session_start();

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../webhook/registerWebhook.php';




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
    $name = htmlspecialchars(trim($_POST['NameOfUser']), ENT_QUOTES, 'UTF-8'); 
    $email = filter_var(trim($_POST['EmailOfUser']), FILTER_SANITIZE_EMAIL);
    $password = htmlspecialchars(trim($_POST['PasswordOfUserUnCrypt']), ENT_QUOTES, 'UTF-8'); 
    $agree_terms = isset($_POST['AgreeTerms']) ? 1 : 0;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['registration_message'] = htmlspecialchars("Errore: L'email inserita non e' valida.", ENT_QUOTES, 'UTF-8'); 
        header("Location: " . ($deviceType === "mobile" ? "../registerPage_mobile.php" : "../registerPage.php"));
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if (!$stmt) {
        $_SESSION['registration_message'] = htmlspecialchars("Errore interno. Riprova piu' tardi.", ENT_QUOTES, 'UTF-8');
        header("Location: " . ($deviceType === "mobile" ? "../registerPage_mobile.php" : "../registerPage.php"));
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['registration_message'] = htmlspecialchars("Errore: L'email gia' registrata. Usa un'altra email.", ENT_QUOTES, 'UTF-8');
        header("Location: " . ($deviceType === "mobile" ? "../registerPage_mobile.php" : "../registerPage.php"));
        exit();
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $confirmation_token = bin2hex(random_bytes(32));

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

                $_SESSION['registration_message'] = htmlspecialchars("Controlla la tua email per confermare la registrazione.", ENT_QUOTES, 'UTF-8');
                header("Location: " . ($deviceType === "mobile" ? "../registerPage_mobile.php" : "../registerPage.php"));
                exit();
            } catch (Exception $e) {
                $_SESSION['registration_message'] = htmlspecialchars("Errore nell'invio della email di conferma: " . $mail->ErrorInfo, ENT_QUOTES, 'UTF-8');
                header("Location: " . ($deviceType === "mobile" ? "../registerPage_mobile.php" : "../registerPage.php"));
                exit();
            }
        } else {
            $_SESSION['registration_message'] = htmlspecialchars("Errore: " . $stmt->error, ENT_QUOTES, 'UTF-8');
            header("Location: " . ($deviceType === "mobile" ? "../registerPage_mobile.php" : "../registerPage.php"));
            exit();
        }
    }

    $stmt->close();
    $conn->close();
}


