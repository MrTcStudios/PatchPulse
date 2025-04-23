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



$servername = "";
$username = "";
$password = "";
$dbname = "PatchPulseBeta";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


require '../webhook/loginWebhook.php';

$secretKey = "";

// Verifica se il form è stato inviato
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
    $email = filter_var(trim($_POST['EmailOfUser']), FILTER_SANITIZE_EMAIL);
    $password = htmlspecialchars(trim($_POST['PasswordOfUserUnCrypt']), ENT_QUOTES, 'UTF-8');

    $stmt = $conn->prepare("SELECT id, name, password, is_confirmed FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $name, $hashed_password, $is_confirmed);
        $stmt->fetch();

        if (!$is_confirmed) {
            $_SESSION['login_message'] = htmlspecialchars("Account non confermato. Controlla la tua email per completare la registrazione.", ENT_QUOTES, 'UTF-8');
            header("Location: " . ($deviceType === "mobile" ? "../loginPage_mobile.php" : "../loginPage.php"));
            exit();
        } elseif (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['email'] = $email;
            $_SESSION['name'] = $name;

            sendLoginWebhook($user_id, $name, $email);

            $user_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? 'unknown IP';
            if (!filter_var($user_ip, FILTER_VALIDATE_IP)) {
                $user_ip = 'invalid IP';
            }

            $user_country = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? 'unknown country';
            if (!preg_match('/^[A-Z]{2}$/', $user_country)) {
                $user_country = 'invalid country';
            }

            error_log("User IP: " . $user_ip);
            error_log("User Country: " . $user_country);

            
            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, 'Login successful', ?)");
            $log_stmt->bind_param("is", $_SESSION['user_id'], $user_ip);
            $log_stmt->execute();
            $log_stmt->close();

            
            $_SESSION['login_message'] = "Login riuscito. Benvenuto, " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "!";
            header("Location: https://mrtc.cc");
            exit();
        } else {
            $_SESSION['login_message'] = htmlspecialchars("Credenziali errate. Password non corretta.", ENT_QUOTES, 'UTF-8');
            header("Location: " . ($deviceType === "mobile" ? "../loginPage_mobile.php" : "../loginPage.php"));
            exit();
        }
    } else {
        $_SESSION['login_message'] = htmlspecialchars("Credenziali errate. Utente non trovato.", ENT_QUOTES, 'UTF-8');
        header("Location: " . ($deviceType === "mobile" ? "../loginPage_mobile.php" : "../loginPage.php"));
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
