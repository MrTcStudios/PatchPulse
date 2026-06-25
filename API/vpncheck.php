<?php
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../lang/lang.php";
require_once __DIR__ . "/../security/rate_limiter.php";

$rlIp = rl_client_ip();
$rlId = rl_identifier($rlIp);
$rlDb = @new mysqli(
    (string) getenv('DB_HOST'),
    (string) getenv('DB_USER'),
    (string) getenv('DB_PASS'),
    (string) getenv('DB_NAME')
);
if (!$rlDb || $rlDb->connect_errno) {
    http_response_code(503);
    echo json_encode(['error' => t('api.rate_limit', false)]);
    exit();
}
$retryAfter = 0;
if (!rl_consume($rlDb, 'api.vpncheck', $rlId, 5, 60, $retryAfter)) {
    $rlDb->close();
    http_response_code(429);
    header('Retry-After: ' . max(1, $retryAfter));
    echo json_encode(['error' => t('api.rate_limit', false)]);
    exit();
}
$rlDb->close();

$key = getenv('VPNAPI_KEY');
if (empty($key)) {
    http_response_code(500);
    echo json_encode(['error' => 'Servizio non configurato']);
    exit();
}

function getRealIP() {
    $cf = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '';
    if (!empty($cf) && filter_var($cf, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return $cf;
    }
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

$client_ip = getRealIP();

if (!filter_var($client_ip, FILTER_VALIDATE_IP)) {
    http_response_code(400);
    echo json_encode(['error' => 'IP non valido']);
    exit();
}

$ch = curl_init('https://vpnapi.io/api/' . urlencode($client_ip) . '?key=' . urlencode($key));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 8);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && $response) {
    echo $response;
} else {
    http_response_code(502);
    echo json_encode(['error' => t('api.vpn_error', false)]);
}
