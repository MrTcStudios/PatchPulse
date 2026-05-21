<?php
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . "/../lang/lang.php";
require_once __DIR__ . "/../security/rate_limiter.php";

// Rate limit persistente su IP (5/min). Sopravvive a reset di sessione.
$rlIp = rl_client_ip();
$rlId = rl_identifier($rlIp);
$rlDb = @new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
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
    $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = trim(explode(',', $_SERVER[$header])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'];
}

$client_ip = getRealIP();

// Validazione aggiuntiva dell'IP
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
