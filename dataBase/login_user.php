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

require_once __DIR__ . "/../security/rate_limiter.php";

$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    $_SESSION['login_message'] = "flash.internal_error";
    header("Location: ../log-reg.php");
    exit();
}

require '../webhook/loginWebhook.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../log-reg.php");
    exit();
}

// ── CSRF Token ──
$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($csrfToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    $_SESSION['login_message'] = "flash.invalid_request";
    header("Location: ../log-reg.php");
    exit();
}

// ── Turnstile CAPTCHA ──
$captchaResponse = $_POST['cf-turnstile-response'] ?? '';
if (empty($captchaResponse)) {
    $_SESSION['login_message'] = "flash.captcha_required";
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
    $_SESSION['login_message'] = "flash.captcha_failed";
    header("Location: ../log-reg.php");
    exit();
}


// ── Brute force protection (persistente per IP) ──
// 5 tentativi falliti → lockout 5 minuti. Sopravvive a session reset/incognito.
$loginAction = 'login.attempt';
$loginIpId   = rl_identifier(rl_client_ip());

if (rl_lockout_remaining($conn, $loginAction, $loginIpId) > 0) {
    $_SESSION['login_message'] = "flash.login.too_many";
    header("Location: ../log-reg.php");
    exit();
}

// ── Input ──
$email = filter_var(trim($_POST['EmailOfUser'] ?? ''), FILTER_SANITIZE_EMAIL);
// NON applicare htmlspecialchars alla password — corrompe i caratteri speciali
$password = $_POST['PasswordOfUserUnCrypt'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['login_message'] = "flash.fill_all_fields";
    header("Location: ../log-reg.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['login_message'] = "flash.invalid_email";
    header("Location: ../log-reg.php");
    exit();
}

// ── Query ──
$stmt = $conn->prepare("SELECT id, name, password, is_confirmed FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

// Messaggio generico per prevenire user enumeration
$genericError = "flash.login.invalid";

if ($stmt->num_rows === 0) {
    // Esegui un hash fittizio per evitare timing attack
    password_verify($password, '$2y$10$dummyhashtopreventtimingattackxxxxxxxxxxxxxxxxxxxxxx');

    rl_register_failure($conn, $loginAction, $loginIpId, 5, 300);

    $_SESSION['login_message'] = $genericError;
    $stmt->close();
    $conn->close();
    header("Location: ../log-reg.php");
    exit();
}

$stmt->bind_result($user_id, $name, $hashed_password, $is_confirmed);
$stmt->fetch();

if (!$is_confirmed) {
    $_SESSION['login_message'] = "flash.login.unverified";
    $stmt->close();
    $conn->close();
    header("Location: ../log-reg.php");
    exit();
}

if (!password_verify($password, $hashed_password)) {
    rl_register_failure($conn, $loginAction, $loginIpId, 5, 300);

    $_SESSION['login_message'] = $genericError;
    $stmt->close();
    $conn->close();
    header("Location: ../log-reg.php");
    exit();
}

// ── Login riuscito ──

// Rigenera session ID per prevenire session fixation
session_regenerate_id(true);

// Reset tentativi
rl_clear_failures($conn, $loginAction, $loginIpId);

$_SESSION['user_id'] = $user_id;
$_SESSION['email'] = $email;
$_SESSION['name'] = $name;

// Rigenera CSRF token per la nuova sessione
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

sendLoginWebhook($user_id, $name, $email);

$user_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? 'unknown';
if (!filter_var($user_ip, FILTER_VALIDATE_IP)) {
    $user_ip = 'unknown';
}

// Log attività
$log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, 'login', ?)");
$log_stmt->bind_param("is", $user_id, $user_ip);
$log_stmt->execute();
$log_stmt->close();

$_SESSION['login_message'] = "flash.login.welcome";

$stmt->close();
$conn->close();

header("Location: ../home.php");
exit();
