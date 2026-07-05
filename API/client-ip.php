<?php
/**
 * Endpoint sicuro che ritorna SOLO l'IP del client (IPv4/IPv6).
 */

ini_set('display_errors', 0);
error_reporting(0);

// Header di sicurezza
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

// Solo GET
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../security/rate_limiter.php';

// Rate limit persistente su IP (sopravvive a cancellazione cookie/incognito).
$rlIp = rl_client_ip();
$rlId = rl_identifier($rlIp);
$rlDb = @new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
if ($rlDb && !$rlDb->connect_errno) {
    $retryAfter = 0;
    if (!rl_consume($rlDb, 'api.client_ip', $rlId, 30, 60, $retryAfter)) {
        $rlDb->close();
        http_response_code(429);
        header('Retry-After: ' . max(1, $retryAfter));
        echo json_encode(['error' => 'Too many requests']);
        exit;
    }
    $rlDb->close();
}

function get_real_ip(): string {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    return filter_var($remote, FILTER_VALIDATE_IP) ? $remote : '';
}

$ip = get_real_ip();

if ($ip === '') {
    echo json_encode(['ipv4' => null, 'ipv6' => null]);
    exit;
}

$isV6 = (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false);
$isV4 = (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false);

echo json_encode([
    'ipv4' => $isV4 ? $ip : null,
    'ipv6' => $isV6 ? $ip : null,
]);
