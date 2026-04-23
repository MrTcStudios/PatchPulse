<?php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');
include("config.php");

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Non autorizzato', 401);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metodo non consentito', 405);
    }

    if (
        empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
    ) {
        throw new Exception('Richiesta non valida', 403);
    }

    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (
        empty($csrfToken) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $csrfToken)
    ) {
        throw new Exception('Token CSRF non valido', 403);
    }

    $userId = $_SESSION['user_id'];
    $rateKey = 'rate_' . $userId;
    $rateWindow = 600;
    $rateMax = 15;

    if (!isset($_SESSION[$rateKey])) {
        $_SESSION[$rateKey] = [];
    }

    $now = time();
    $_SESSION[$rateKey] = array_filter(
        $_SESSION[$rateKey],
        fn($t) => ($now - $t) < $rateWindow
    );

    if (count($_SESSION[$rateKey]) >= $rateMax) {
        throw new Exception('Troppe scansioni. Riprova tra qualche minuto.', 429);
    }

    $rawInput = file_get_contents('php://input');
    if (strlen($rawInput) > 2048) {
        throw new Exception('Payload troppo grande', 400);
    }

    $input = json_decode($rawInput, true);
    if (!is_array($input)) {
        throw new Exception('JSON non valido', 400);
    }

    $target = trim($input['target'] ?? '');

    if (!filter_var($target, FILTER_VALIDATE_URL)) {
        throw new Exception('URL non valido', 400);
    }
    if (!str_starts_with($target, 'https://')) {
        throw new Exception('Solo HTTPS permesso', 400);
    }
    if (strlen($target) > 2048) {
        throw new Exception('URL troppo lungo', 400);
    }

    $host = parse_url($target, PHP_URL_HOST);
    if (!$host) {
        throw new Exception('Dominio non valido', 400);
    }

    if (!preg_match('/^(?!-)[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,62}[a-zA-Z0-9])?(?:\.(?!-)[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,62}[a-zA-Z0-9])?)*\.[a-zA-Z]{2,}$/', $host)) {
        throw new Exception('Dominio non valido', 400);
    }

    $blockedPatterns = [
        'localhost', '127.', '192.168.', '10.', '172.16.', '172.17.',
        '172.18.', '172.19.', '172.20.', '172.21.', '172.22.', '172.23.',
        '172.24.', '172.25.', '172.26.', '172.27.', '172.28.', '172.29.',
        '172.30.', '172.31.', '169.254.', '0.', '100.64.',
        '::1', 'fc00:', 'fd00:', 'fe80:',
    ];
    foreach ($blockedPatterns as $blocked) {
        if (str_starts_with(strtolower($host), $blocked)) {
            throw new Exception('Indirizzi privati non permessi', 403);
        }
    }

    if (filter_var($host, FILTER_VALIDATE_IP)) {
        throw new Exception('Inserire un dominio, non un indirizzo IP', 400);
    }

    $port = parse_url($target, PHP_URL_PORT);
    if ($port !== null && $port !== 443) {
        throw new Exception('Solo porta 443 (HTTPS) è permessa', 400);
    }

    $verifyDomain = preg_replace('/^www\./', '', strtolower($host));

    $db_host_env = getenv('DB_HOST');
    $db_name_env = getenv('DB_NAME');
    $db_user_env = getenv('DB_USER');
    $db_pass_env = getenv('DB_PASS');

    $conn = new mysqli($db_host_env, $db_user_env, $db_pass_env, $db_name_env);
    if ($conn->connect_error) {
        throw new Exception('Errore interno', 500);
    }

    $domainCheck = $conn->prepare("SELECT verification_token FROM verified_domains WHERE user_id = ? AND domain = ? AND verified_at IS NOT NULL");
    $domainCheck->bind_param("is", $userId, $verifyDomain);
    $domainCheck->execute();
    $domainCheck->store_result();

    if ($domainCheck->num_rows === 0) {
        $domainCheck->close();
        $conn->close();
        throw new Exception('Dominio non verificato. Completa la verifica DNS prima di scansionare.', 403);
    }

    $domainCheck->bind_result($verificationToken);
    $domainCheck->fetch();
    $domainCheck->close();

    $txtName = "_patchpulse.{$verifyDomain}";
    $expectedValue = "patchpulse-verify={$verificationToken}";

    $dohUrl = "https://dns.google/resolve?" . http_build_query([
        'name' => $txtName,
        'type' => 'TXT',
    ]);
    $dohCtx = stream_context_create([
        'http' => [
            'header'  => "Accept: application/json\r\n",
            'method'  => 'GET',
            'timeout' => 8,
        ],
    ]);
    $dohResult = @file_get_contents($dohUrl, false, $dohCtx);

    if ($dohResult === false) {
        $dohUrl = "https://cloudflare-dns.com/dns-query?" . http_build_query([
            'name' => $txtName,
            'type' => 'TXT',
        ]);
        $dohCtx = stream_context_create([
            'http' => [
                'header'  => "Accept: application/dns-json\r\n",
                'method'  => 'GET',
                'timeout' => 8,
            ],
        ]);
        $dohResult = @file_get_contents($dohUrl, false, $dohCtx);
    }

    if ($dohResult === false) {
        $conn->close();
        throw new Exception('Impossibile verificare il DNS. Riprova tra qualche minuto.', 503);
    }

    $dnsData = json_decode($dohResult, true);
    $dnsAnswers = $dnsData['Answer'] ?? [];
    $txtFound = false;
    foreach ($dnsAnswers as $ans) {
        if (($ans['type'] ?? 0) === 16) {
            $val = trim($ans['data'] ?? '', '"\'');
            if ($val === $expectedValue) {
                $txtFound = true;
                break;
            }
        }
    }

    if (!$txtFound) {
        $revoke = $conn->prepare("UPDATE verified_domains SET verified_at = NULL WHERE user_id = ? AND domain = ?");
        $revoke->bind_param("is", $userId, $verifyDomain);
        $revoke->execute();
        $revoke->close();
        $conn->close();
        throw new Exception('Record DNS di verifica non trovato. Il dominio non è più verificato.', 403);
    }

    $conn->close();

    $ch = curl_init('http://scanner:5000/health');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception('Scanner non disponibile', 503);
    }

    $scanId = bin2hex(random_bytes(16));
    $_SESSION['scan_' . $scanId] = [
        'target'     => $target,
        'type'       => 'url',
        'scans' => ['nmap','testssl','headers','dnsrecon','extra','whois','emailsec','robots','redirects'],
        'start_time' => $now,
        'user_id'    => $userId,
    ];

    $_SESSION[$rateKey][] = $now;

    echo json_encode(['scanId' => $scanId, 'status' => 'started']);

} catch (Exception $e) {
    $code = $e->getCode();
    if ($code < 400 || $code > 599) {
        $code = 500;
    }
    http_response_code($code);
    echo json_encode(['error' => $e->getMessage()]);
}
