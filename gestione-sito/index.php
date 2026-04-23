<?php
ini_set('display_errors', 0);
error_reporting(0);

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

session_start();

if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    header("Location: dashboard.php");
    exit;
}

if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

$errore = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $csrf = $_POST['csrf_token'] ?? '';
    if (empty($csrf) || !isset($_SESSION['admin_csrf']) || !hash_equals($_SESSION['admin_csrf'], $csrf)) {
        $errore = "Richiesta non valida. Riprova.";
    } else {
        $attemptsKey = 'admin_login_attempts';
        $lockoutKey = 'admin_lockout_until';

        if (isset($_SESSION[$lockoutKey]) && time() < $_SESSION[$lockoutKey]) {
            $remaining = $_SESSION[$lockoutKey] - time();
            $errore = "Troppi tentativi. Riprova tra {$remaining} secondi.";
        } else {
            $captchaResponse = $_POST['cf-turnstile-response'] ?? '';
            if (empty($captchaResponse)) {
                $errore = "Completa la verifica di sicurezza.";
            } else {
                $secretKey = getenv('TURNSTILE_SECRET_KEY');
                $verifyData = [
                    'secret'   => $secretKey,
                    'response' => $captchaResponse,
                    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
                ];
                $ctx = stream_context_create([
                    'http' => [
                        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                        'method'  => 'POST',
                        'content' => http_build_query($verifyData),
                        'timeout' => 5,
                    ],
                ]);
                $result = @file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $ctx);
                $responseData = $result ? json_decode($result) : null;

                if (!$responseData || !$responseData->success) {
                    $errore = "Verifica di sicurezza fallita. Riprova.";
                } else {
                    $username = $_POST['username'] ?? '';
                    $password = $_POST['password'] ?? '';

                    $adminUser = getenv('ADMIN_USERNAME') ?: 'admin';
                    $adminHash = getenv('ADMIN_PASSWORD_HASH');

                    if (empty($adminHash)) {
                        $errore = "Configurazione admin non valida.";
                    } else {
                        $usernameMatch = hash_equals($adminUser, $username);
                        $passwordMatch = password_verify($password, $adminHash);

                        if ($usernameMatch && $passwordMatch) {
                            session_regenerate_id(true);
                            unset($_SESSION[$attemptsKey], $_SESSION[$lockoutKey]);
                            $_SESSION['admin'] = true;
                            $_SESSION['admin_ip'] = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'];
                            $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
                            header("Location: dashboard.php");
                            exit;
                        } else {
                            $_SESSION[$attemptsKey] = ($_SESSION[$attemptsKey] ?? 0) + 1;
                            if ($_SESSION[$attemptsKey] >= 5) {
                                $_SESSION[$lockoutKey] = time() + 600;
                                $_SESSION[$attemptsKey] = 0;
                            }
                            $errore = "Credenziali errate.";
                        }
                    }
                }
            }
        }
    }
}

$siteKey = getenv('TURNSTILE_SITE_KEY') ?: '0x4AAAAAACxRHR_H4N6K4-b5';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PatchPulse - Admin</title>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; background:linear-gradient(135deg,#f5f7fa,#c3cfe2); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
        .login-box { background:#fff; padding:2.5rem; border-radius:15px; box-shadow:0 10px 25px rgba(0,0,0,0.1); width:100%; max-width:400px; }
        .login-box h1 { text-align:center; color:#4a90e2; margin-bottom:1.5rem; }
        .err { background:#fde8e8; color:#e74c3c; padding:0.8rem; border-radius:8px; margin-bottom:1rem; text-align:center; font-size:0.9rem; }
        label { display:block; margin-bottom:0.3rem; font-weight:500; color:#333; font-size:0.9rem; }
        input[type=text], input[type=password] { width:100%; padding:0.75rem; border:2px solid #e2e8f0; border-radius:8px; font-size:1rem; margin-bottom:1rem; }
        input:focus { border-color:#4a90e2; outline:none; }
        .captcha { display:flex; justify-content:center; margin:1rem 0; }
        button { width:100%; padding:0.75rem; background:#4a90e2; color:#fff; border:none; border-radius:8px; font-size:1rem; cursor:pointer; }
        button:hover { opacity:0.9; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>PatchPulse Admin</h1>
        <?php if ($errore): ?><div class="err"><?= htmlspecialchars($errore, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf']) ?>">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autocomplete="username">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
            <div class="captcha"><div class="cf-turnstile" data-theme="light" data-sitekey="<?= htmlspecialchars($siteKey) ?>"></div></div>
            <button type="submit">Accedi</button>
        </form>
    </div>
</body>
</html>
