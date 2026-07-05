<?php
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . "/../lang/lang.php";
require_once __DIR__ . "/../security/rate_limiter.php";

// Rate limit persistente su IP (10/min). Sopravvive a reset di sessione.
$rlIp = rl_client_ip();
$rlId = rl_identifier($rlIp);
$rlDb = @new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
if (!$rlDb || $rlDb->connect_errno) {
    // Fail-closed: senza DB non possiamo applicare il limite, rifiuta.
    http_response_code(503);
    echo json_encode(['error' => t('api.rate_limit', false)]);
    exit();
}
$retryAfter = 0;
if (!rl_consume($rlDb, 'api.location', $rlId, 10, 60, $retryAfter)) {
    $rlDb->close();
    http_response_code(429);
    header('Retry-After: ' . max(1, $retryAfter));
    echo json_encode(['error' => t('api.rate_limit', false)]);
    exit();
}
$rlDb->close();

$token = getenv('IPINFO_TOKEN');
if (empty($token)) {
    http_response_code(500);
    echo json_encode(['error' => t('flash.internal_error', false)]);
    exit();
}
$clientIP = $_SERVER['REMOTE_ADDR'] ?? '';
if (empty($clientIP) || !filter_var($clientIP, FILTER_VALIDATE_IP)) {
    http_response_code(400);
    echo json_encode(['error' => 'IP non valido']);
    exit();
}
$ch = curl_init('https://ipinfo.io/' . urlencode($clientIP) . '/json?token=' . urlencode($token));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($httpCode === 200 && $response) {
    echo $response;
} else {
    http_response_code(502);
    echo json_encode(['error' => t('api.location_error', false)]);
}
