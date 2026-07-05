<?php
ini_set('display_errors', 0);
error_reporting(0);

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

include("../config.php");
require_once __DIR__ . "/../lang/lang.php";
require_once __DIR__ . "/../security/rate_limiter.php";

header('Content-Type: application/json');

// Helper: emit a JSON error and stop. Decouples frontend from response text.
function pp_save_error(int $code, string $key): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => t($key, false), 'code' => $key]);
    exit;
}

// Chiave di cifratura da variabile d'ambiente
$encKey = getenv('ENC_KEY');
if (empty($encKey)) {
    pp_save_error(500, 'flash.internal_error');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    pp_save_error(405, 'vd.method_not_allowed');
}

if (!isset($_SESSION['user_id'])) {
    pp_save_error(401, 'vd.unauthorized');
}

$user_id = (int)$_SESSION['user_id'];

// Cap dimensione body (anti-OOM): 65 KB. 39 campi × 1000 char in chiaro stanno
// largamente sotto.
$contentLen = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
if ($contentLen > 65536) {
    pp_save_error(413, 'ss.payload_too_big');
}

// Leggi JSON body (limita a 65537 byte per detectare overflow oltre il cap).
$rawInput = file_get_contents('php://input', false, null, 0, 65537);
if (empty($rawInput)) {
    pp_save_error(400, 'vd.invalid_request');
}
if (strlen($rawInput) > 65536) {
    pp_save_error(413, 'ss.payload_too_big');
}

$input = json_decode($rawInput, true);
if (!is_array($input)) {
    pp_save_error(400, 'vd.json_invalid');
}

if (!isset($input['csrf_token']) || !isset($input['data'])) {
    pp_save_error(400, 'vd.invalid_request');
}

// CSRF
$csrfToken = $input['csrf_token'];
if (empty($csrfToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    pp_save_error(403, 'vd.csrf_invalid');
}

// Rate limit persistente per user_id: max 10 salvataggi / 10 min. Applicato DOPO
// la verifica CSRF, così una richiesta senza token valido non consuma la quota
// dell'utente. Senza questo limite la tabella scans si riempirebbe in pochi minuti.
$saveAction = 'browser_scan.save';
$saveId     = rl_identifier((string)$user_id);
$retryAfter = 0;
if (!rl_consume($conn, $saveAction, $saveId, 10, 600, $retryAfter)) {
    http_response_code(429);
    header('Retry-After: ' . max(1, $retryAfter));
    echo json_encode([
        'success' => false,
        'error'   => t('ss.too_many_scans', false),
        'code'    => 'rate_limited',
    ]);
    exit;
}

// Decodifica: base64 → JSON → array
$decoded = base64_decode($input['data'], true);
if ($decoded === false) {
    pp_save_error(400, 'vd.invalid_request');
}

$scanData = json_decode($decoded, true);
if (!is_array($scanData)) {
    pp_save_error(400, 'vd.json_invalid');
}

function encryptData($data) {
    global $encKey;
    if ($data === '' || $data === null) return '';
    $iv = random_bytes(16);
    $cipher = openssl_encrypt($data, 'AES-256-CBC', $encKey, OPENSSL_RAW_DATA, $iv);
    $hmac = hash_hmac('sha256', $iv . $cipher, $encKey, true);
    return base64_encode($iv . $hmac . $cipher);
}

function getField($data, $key, $maxLength = 1000) {
    $val = $data[$key] ?? '';
    if (!is_string($val)) $val = '';
    return encryptData(substr($val, 0, $maxLength));
}

$fields = [
    'cookiesEnabled','doNotTrack','browserFingerprinting','webrtcSupport','httpsOnly',
    'blockedResources','adBlockEnabled',
    'javascriptStatus','webglFingerprinting','developerMode','webAssemblySupport',
    'webWorkersSupported','mediaQueriesSupported','webNotificationsSupported',
    'permissionsAPISupported','paymentRequestAPISupported','htmlCssSupport',
    'geolocationInfo','sensorsSupported','popupsEnabled',
    'publicIpv4','publicIpv6','browserType','browserVersion','browserLanguage',
    'osVersion','incognitoMode','deviceMemory','cpuThreads','cpuCores',
    'gpuName','colorDepth','pixelDepth','touchSupport','screenResolution',
    'mimeTypes','referrerPolicy','batteryStatus','securityProtocols'
];

$encryptedValues = [];
foreach ($fields as $field) {
    $encryptedValues[] = getField($scanData, $field);
}

$placeholders = implode(',', array_fill(0, count($fields) + 1, '?'));
$types = 'i' . str_repeat('s', count($fields));

$sql = "INSERT INTO scans (user_id," . implode(',', $fields) . ") VALUES ($placeholders)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    pp_save_error(500, 'flash.internal_error');
}

$stmt->bind_param($types, $user_id, ...$encryptedValues);

if ($stmt->execute()) {
    // Log attività
    // mod_remoteip mette l'IP reale del client (CF-Connecting-IP) in REMOTE_ADDR e rimuove l'header originale.
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!filter_var($user_ip, FILTER_VALIDATE_IP)) $user_ip = 'unknown';
    $log = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, 'browser_scan_saved', ?)");
    $log->bind_param("is", $user_id, $user_ip);
    $log->execute();
    $log->close();

    echo json_encode(['success' => true]);
} else {
    pp_save_error(500, 'flash.internal_error');
}

$stmt->close();
$conn->close();
