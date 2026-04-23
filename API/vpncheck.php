<?php
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

session_start();
$rateKey = 'vpn_requests';
$now = time();
$_SESSION[$rateKey] = array_filter($_SESSION[$rateKey] ?? [], fn($t) => ($now - $t) < 60);
if (count($_SESSION[$rateKey]) >= 5) {
    http_response_code(429);
    echo json_encode(['error' => 'Troppe richieste. Riprova tra un minuto.']);
    exit();
}
$_SESSION[$rateKey][] = $now;
session_write_close();

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
    echo json_encode(['error' => 'Errore nella chiamata VPN API']);
}
