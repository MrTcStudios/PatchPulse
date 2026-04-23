<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

include("../config.php");

$encKey = getenv('ENC_KEY');
if (empty($encKey)) {
    http_response_code(500);
    exit('Chiave di cifratura non configurata');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metodo non consentito');
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Non autorizzato');
}

$rawInput = file_get_contents('php://input');
if (empty($rawInput)) {
    http_response_code(400);
    exit('Nessun dato ricevuto');
}

$input = json_decode($rawInput, true);
if (!is_array($input)) {
    http_response_code(400);
    exit('JSON non valido');
}

if (!isset($input['csrf_token']) || !isset($input['data'])) {
    http_response_code(400);
    exit('Campi mancanti');
}

$csrfToken = $input['csrf_token'];
if (empty($csrfToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    http_response_code(403);
    exit('Token CSRF non valido');
}

$decoded = base64_decode($input['data'], true);
if ($decoded === false) {
    http_response_code(400);
    exit('Base64 non valido');
}

$scanData = json_decode($decoded, true);
if (!is_array($scanData)) {
    http_response_code(400);
    exit('Dati scan non validi');
}

$user_id = (int)$_SESSION['user_id'];

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
    http_response_code(500);
    exit('Errore prepare: ' . $conn->error);
}

$stmt->bind_param($types, $user_id, ...$encryptedValues);

if ($stmt->execute()) {
    $user_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? 'unknown';
    if (!filter_var($user_ip, FILTER_VALIDATE_IP)) $user_ip = 'unknown';
    $log = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, 'browser_scan_saved', ?)");
    $log->bind_param("is", $user_id, $user_ip);
    $log->execute();
    $log->close();

    echo "Scans salvati con successo";
} else {
    http_response_code(500);
    exit('Errore execute: ' . $stmt->error);
}

$stmt->close();
$conn->close();
