<?php
set_time_limit(600);
ini_set('display_errors', 0);
error_reporting(0);
include("config.php");

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$scanId = $_GET['scanId'] ?? '';
if (!preg_match('/^[a-f0-9]{32}$/', $scanId)) {
    http_response_code(400);
    exit;
}

if (!isset($_SESSION['scan_' . $scanId])) {
    http_response_code(404);
    exit;
}

$scan = $_SESSION['scan_' . $scanId];

if ($scan['user_id'] !== $_SESSION['user_id']) {
    http_response_code(403);
    exit;
}

if (time() - $scan['start_time'] > 600) {
    unset($_SESSION['scan_' . $scanId]);
    http_response_code(408);
    exit;
}

$allowedScans = ['nmap','testssl','headers','dnsrecon','extra','whois','emailsec','robots','redirects'];
$scans = array_filter($scan['scans'], fn($s) => in_array($s, $allowedScans, true));
if (empty($scans) || !filter_var($scan['target'], FILTER_VALIDATE_URL)) {
    unset($_SESSION['scan_' . $scanId]);
    http_response_code(400);
    exit;
}

unset($_SESSION['scan_' . $scanId]);
session_write_close();

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('X-Content-Type-Options: nosniff');
header('X-Accel-Buffering: no');

$scannerToken = getenv('SCANNER_AUTH_TOKEN') ?: '';

$headers = ['Content-Type: application/json'];
if ($scannerToken !== '') {
    $headers[] = 'X-Scanner-Token: ' . $scannerToken;
}

$isFirstChunk = true;

$ch = curl_init('http://scanner:5000/scan');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode([
        'target' => $scan['target'],
        'type'   => $scan['type'],
        'scans'  => array_values($scans),
    ]),
    CURLOPT_HTTPHEADER     => $headers,
    CURLOPT_TIMEOUT        => 600,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_WRITEFUNCTION  => function ($ch, $data) use (&$isFirstChunk) {
        if ($isFirstChunk) {
            $isFirstChunk = false;
            $decoded = json_decode(trim($data), true);
            if (is_array($decoded) && isset($decoded['error'])) {
                $msg = ['error' => $decoded['error']];
                if (!empty($decoded['busy'])) {
                    $msg['busy'] = true;
                }
                echo "data: " . json_encode($msg) . "\n\n";
                if (ob_get_level()) { ob_flush(); }
                flush();
                return strlen($data);
            }
        }

        $lines = explode("\n", $data);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                echo "data: " . json_encode(['line' => $line]) . "\n\n";
                if (ob_get_level()) { ob_flush(); }
                flush();
            }
        }
        return strlen($data);
    }
]);

curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlError) {
    echo "data: " . json_encode(['line' => 'Errore connessione al servizio di scansione.']) . "\n\n";
}

echo "data: " . json_encode(['completed' => true]) . "\n\n";
if (ob_get_level()) {
    ob_flush();
}
flush();
