<?php
ini_set('display_errors', 0);
error_reporting(0);

$appDomain = getenv('APP_DOMAIN') ?: 'patchpulse.org';
header("Access-Control-Allow-Origin: https://{$appDomain}");
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . "/../lang/lang.php";
require_once __DIR__ . "/../security/rate_limiter.php";

// ── Turnstile verification ──
$captchaResponse = $_GET['cf-turnstile-response'] ?? '';
if (empty($captchaResponse)) {
    echo json_encode(['success' => false, 'error' => t('flash.captcha_required', false)]);
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

// ── Rate limiting persistente su IP (5/min) ──
$rlIp = rl_client_ip();
$rlId = rl_identifier($rlIp);
$rlDb = @new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
if (!$rlDb || $rlDb->connect_errno) {
    http_response_code(503);
    echo json_encode(['success' => false, 'error' => t('api.rate_limit', false)]);
    exit();
}
$retryAfter = 0;
if (!rl_consume($rlDb, 'proxy.breach_check', $rlId, 5, 60, $retryAfter)) {
    $rlDb->close();
    http_response_code(429);
    header('Retry-After: ' . max(1, $retryAfter));
    echo json_encode(['success' => false, 'error' => t('api.rate_limit', false)]);
    exit();
}
$rlDb->close();

// ── Email validation ──
$email = $_GET['email'] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => t('flash.invalid_email', false)]);
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
    echo json_encode(['success' => false, 'error' => t('proxy.service_error', false)]);
    exit();
}

echo $response;
