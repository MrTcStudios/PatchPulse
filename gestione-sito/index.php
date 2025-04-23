<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$secretKey = "";

if (isset($_SESSION['admin'])) {
    header("Location: dashboard.php");
    exit;
}

// Credenziali di default
$default_password = '';

// Verifica la password personalizzata dal file di configurazione (se esiste)
if (file_exists('password.txt')) {
    $custom_password = trim(file_get_contents('password.txt'));
} else {
    $custom_password = '';
}

// Controllo credenziali
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verifica CAPTCHA
    $captchaResponse = $_POST['cf-turnstile-response'] ?? '';
    $remoteIp = $_SERVER['REMOTE_ADDR'] ?? '';

    if (empty($captchaResponse)) {
        $errore = "Per favore, completa la verifica di sicurezza.";
    } else {
        // Verifica il token CAPTCHA
        $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        $data = [
            'secret' => $secretKey,
            'response' => $captchaResponse,
            'remoteip' => $remoteIp
        ];

        $options = [
            'http' => [
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ],
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($verifyUrl, false, $context);
        $responseData = json_decode($result);

        if ($responseData->success) {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if ($username === '' && ($password === $default_password || $password === $custom_password)) {
                $_SESSION['admin'] = true;
                header("Location: dashboard.php");
                exit;
            } else {
                $errore = "Credenziali errate!";
            }
        } else {
            $errore = "Verifica di sicurezza fallita. Riprova.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PatchPulse - Admin Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <style>
        :root {
            --primary-color: #4a90e2;
            --error-color: #e74c3c;
            --background-color: #f5f7fa;
            --card-color: #ffffff;
            --text-color: #2c3e50;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: var(--card-color);
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: var(--primary-color);
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .login-header .logo {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .error-message {
            background-color: #fde8e8;
            color: var(--error-color);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .form-group i {
            position: absolute;
            left: 1rem;
            top: 2.3rem;
            color: #94a3b8;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: opacity 0.3s ease;
            margin-top: 1rem;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .captcha-container {
            margin: 1.5rem 0;
            display: flex;
            justify-content: center;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 1.5rem;
            }

            .login-header h1 {
                font-size: 1.8rem;
            }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        .shake {
            animation: shake 0.5s ease-in-out;
        }

    </style>
</head>
<body>
    <div class="login-container <?php if (isset($errore)) echo 'shake'; ?>">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1>PatchPulse</h1>
            <p>Accesso Amministratore</p>
        </div>

        <?php if (isset($errore)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $errore; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <i class="fas fa-user"></i>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-control" 
                    placeholder="Inserisci username"
                    required
                    autocomplete="username"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <i class="fas fa-lock"></i>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    placeholder="Inserisci password"
                    required
                    autocomplete="current-password"
                >
            </div>

            <div class="captcha-container">
                <div class="cf-turnstile" data-sitekey="0x4AAAAAAA45DIVnAjfWbKkG"></div>
            </div>

            <button type="submit" class="btn">
                <i class="fas fa-sign-in-alt"></i>
                Accedi
            </button>
        </form>
    </div>
</body>
</html>
