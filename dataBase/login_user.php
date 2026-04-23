<?php
ob_start();

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

session_start();

ini_set('display_errors', 0);
error_reporting(0);

$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    $_SESSION['login_message'] = "Errore interno. Riprova più tardi.";
    header("Location: ../log-reg.php");
    exit();
}

require '../webhook/loginWebhook.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../log-reg.php");
    exit();
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($csrfToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    $_SESSION['login_message'] = "Richiesta non valida. Riprova.";
    header("Location: ../log-reg.php");
    exit();
}

$captchaResponse = $_POST['cf-turnstile-response'] ?? '';
if (empty($captchaResponse)) {
    $_SESSION['login_message'] = "Completa la verifica CAPTCHA.";
    header("Location: ../log-reg.php");
    exit();
}

$secretKey = getenv('TURNSTILE_SECRET_KEY');
$verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
$verifyData = [
    'secret'   => $secretKey,
    'response'  => $captchaResponse,
    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
];
$context = stream_context_create([
    'http' => [
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($verifyData),
        'timeout' => 5,
    ],
]);
$result = @file_get_contents($verifyUrl, false, $context);
$responseData = $result ? json_decode($result) : null;

if (!$responseData || !$responseData->success) {
    $_SESSION['login_message'] = "Verifica CAPTCHA fallita. Riprova.";
    header("Location: ../log-reg.php");
    exit();
}


$loginAttemptsKey = 'login_attempts';
$loginLockoutKey = 'login_lockout_until';

if (isset($_SESSION[$loginLockoutKey]) && time() < $_SESSION[$loginLockoutKey]) {
    $remaining = $_SESSION[$loginLockoutKey] - time();
    $_SESSION['login_message'] = "Troppi tentativi. Riprova tra {$remaining} secondi.";
    header("Location: ../log-reg.php");
    exit();
}

$email = filter_var(trim($_POST['EmailOfUser'] ?? ''), FILTER_SANITIZE_EMAIL);
$password = $_POST['PasswordOfUserUnCrypt'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['login_message'] = "Compila tutti i campi.";
    header("Location: ../log-reg.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['login_message'] = "Email non valida.";
    header("Location: ../log-reg.php");
    exit();
}

$stmt = $conn->prepare("SELECT id, name, password, is_confirmed FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

$genericError = "Credenziali errate. Verifica email e password.";

if ($stmt->num_rows === 0) {
    password_verify($password, '$2y$10$dummyhashtopreventtimingattackxxxxxxxxxxxxxxxxxxxxxx');

    $_SESSION[$loginAttemptsKey] = ($_SESSION[$loginAttemptsKey] ?? 0) + 1;
    if ($_SESSION[$loginAttemptsKey] >= 5) {
        $_SESSION[$loginLockoutKey] = time() + 300;
        $_SESSION[$loginAttemptsKey] = 0;
    }

    $_SESSION['login_message'] = $genericError;
    $stmt->close();
    $conn->close();
    header("Location: ../log-reg.php");
    exit();
}

$stmt->bind_result($user_id, $name, $hashed_password, $is_confirmed);
$stmt->fetch();

if (!$is_confirmed) {
    $_SESSION['login_message'] = "Account non confermato. Controlla la tua email.";
    $stmt->close();
    $conn->close();
    header("Location: ../log-reg.php");
    exit();
}

if (!password_verify($password, $hashed_password)) {
    $_SESSION[$loginAttemptsKey] = ($_SESSION[$loginAttemptsKey] ?? 0) + 1;
    if ($_SESSION[$loginAttemptsKey] >= 5) {
        $_SESSION[$loginLockoutKey] = time() + 300;
        $_SESSION[$loginAttemptsKey] = 0;
    }

    $_SESSION['login_message'] = $genericError;
    $stmt->close();
    $conn->close();
    header("Location: ../log-reg.php");
    exit();
}

session_regenerate_id(true);

unset($_SESSION[$loginAttemptsKey], $_SESSION[$loginLockoutKey]);

$_SESSION['user_id'] = $user_id;
$_SESSION['email'] = $email;
$_SESSION['name'] = $name;

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

sendLoginWebhook($user_id, $name, $email);

$user_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? 'unknown';
if (!filter_var($user_ip, FILTER_VALIDATE_IP)) {
    $user_ip = 'unknown';
}

$log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, 'login', ?)");
$log_stmt->bind_param("is", $user_id, $user_ip);
$log_stmt->execute();
$log_stmt->close();

$_SESSION['login_message'] = "Login riuscito. Benvenuto, " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "!";

$stmt->close();
$conn->close();

header("Location: ../home.php");
exit();
