<?php
/**
 * Endpoint sicuro che ritorna SOLO l'IP del client (IPv4/IPv6).
 * Non espone alcuna informazione sul server.
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

// Rate limit (max 30 richieste/minuto per sessione)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$rateKey = 'ip_requests';
$now = time();
$_SESSION[$rateKey] = array_filter($_SESSION[$rateKey] ?? [], fn($t) => ($now - $t) < 60);
if (count($_SESSION[$rateKey]) >= 30) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests']);
    session_write_close();
    exit;
}
$_SESSION[$rateKey][] = $now;
session_write_close();

/**
 * Restituisce l'IP reale del client.
 * Gestisce Cloudflare/proxy. Scarta IP privati per non esporre IP interni.
 */
function get_real_ip(): string {
    $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP'];
    foreach ($headers as $h) {
        if (!empty($_SERVER[$h])) {
            $candidate = trim(explode(',', $_SERVER[$h])[0]);
            if (filter_var(
                $candidate,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            )) {
                return $candidate;
            }
        }
    }
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
