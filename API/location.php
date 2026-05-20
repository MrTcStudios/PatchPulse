<?php
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

session_start();
require_once __DIR__ . "/../lang/lang.php";

$rateKey = 'loc_requests';
$now = time();
$_SESSION[$rateKey] = array_filter($_SESSION[$rateKey] ?? [], fn($t) => ($now - $t) < 60);
if (count($_SESSION[$rateKey]) >= 10) {
    http_response_code(429);
    echo json_encode(['error' => t('api.rate_limit', false)]);
    exit();
}
$_SESSION[$rateKey][] = $now;
session_write_close();

$token = getenv('IPINFO_TOKEN');
if (empty($token)) {
    http_response_code(500);
    echo json_encode(['error' => t('flash.internal_error', false)]);
    exit();
}
$clientIP = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
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
