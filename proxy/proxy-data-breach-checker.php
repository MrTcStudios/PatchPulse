<?php
ini_set('display_errors', 0);
error_reporting(0);

$appDomain = getenv('APP_DOMAIN') ?: 'patchpulse.org';
header("Access-Control-Allow-Origin: https://{$appDomain}");
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

session_start();

$captchaResponse = $_GET['cf-turnstile-response'] ?? '';
if (empty($captchaResponse)) {
    echo json_encode(['success' => false, 'error' => 'Captcha richiesto']);
    exit();
}

$secretKey = getenv('TURNSTILE_SECRET_KEY');
$verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
$verifyData = [
    'secret'   => $secretKey,
    'response'  => $captchaResponse,
    'remoteip'  => $_SERVER['REMOTE_ADDR'] ?? ''
];
$verifyContext = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query($verifyData),
        'timeout' => 5
    ]
]);
$verifyResult = @file_get_contents($verifyUrl, false, $verifyContext);
$captchaData = json_decode($verifyResult, true);
if (empty($captchaData['success'])) {
    echo json_encode(['success' => false, 'error' => 'Verifica captcha fallita']);
    exit();
}

$rateKey = 'breach_requests';
$now = time();
$_SESSION[$rateKey] = array_filter($_SESSION[$rateKey] ?? [], fn($t) => ($now - $t) < 60);
if (count($_SESSION[$rateKey]) >= 5) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Troppe richieste. Riprova tra un minuto.']);
    exit();
}
$_SESSION[$rateKey][] = $now;
session_write_close();

$email = $_GET['email'] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Email non valida']);
    exit();
}

$safeEmail = urlencode($email);
$url = "https://leakcheck.net/api/public?check={$safeEmail}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || $response === false) {
    echo json_encode(['success' => false, 'error' => 'Errore nella richiesta al servizio']);
    exit();
}

echo $response;
