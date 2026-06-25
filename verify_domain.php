<?php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/lang/lang.php";
require_once __DIR__ . "/security/rate_limiter.php";

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception(t('vd.unauthorized', false), 401);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception(t('vd.method_not_allowed', false), 405);
    }

    // CSRF
    if (
        empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
    ) {
        throw new Exception(t('vd.invalid_request', false), 403);
    }

    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($csrfToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        throw new Exception(t('vd.csrf_invalid', false), 403);
    }

    // Input
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    if (!is_array($input)) {
        throw new Exception(t('vd.json_invalid', false), 400);
    }

    $domain = strtolower(trim($input['domain'] ?? ''));
    if (empty($domain) || !preg_match('/^(?!-)[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,62}[a-zA-Z0-9])?(?:\.(?!-)[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,62}[a-zA-Z0-9])?)*\.[a-zA-Z]{2,}$/', $domain)) {
        throw new Exception(t('vd.domain_invalid', false), 400);
    }

    $db_host = getenv('DB_HOST');
    $db_name = getenv('DB_NAME');
    $db_user = getenv('DB_USER');
    $db_pass = getenv('DB_PASS');

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        throw new Exception(t('vd.internal_error', false), 500);
    }

    $userId = (int)$_SESSION['user_id'];

    // ── Rate limiting anti-abuso: conta OGNI tentativo PRIMA del lookup DNS ──
    // Questo endpoint fa una query DNS-over-HTTPS in uscita per ogni richiesta:
    // senza limite e' la leva ideale per saturare i worker PHP. Limitiamo per
    // utente (identita' forte, non falsificabile) e per IP (difesa in profondita').
    // Fail-open su errore DB: una verifica legittima non deve fallire per il limiter.
    $rlWindow = 600; // 10 minuti
    $rlIp     = rl_client_ip();
    $okUser = rl_consume($conn, 'domain.verify', rl_identifier('domain.verify.user', (string)$userId), 15, $rlWindow);
    $okIp   = rl_consume($conn, 'domain.verify', rl_identifier('domain.verify.ip', $rlIp), 40, $rlWindow);
    if (!$okUser || !$okIp) {
        $conn->close();
        throw new Exception(t('vd.too_many', false), 429);
    }

    // Recupera il token atteso dal DB
    $stmt = $conn->prepare("SELECT id, verification_token, verified_at FROM verified_domains WHERE user_id = ? AND domain = ?");
    $stmt->bind_param("is", $userId, $domain);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        $conn->close();
        throw new Exception(t('vd.no_pending', false), 404);
    }

    $stmt->bind_result($domainId, $expectedToken, $verifiedAt);
    $stmt->fetch();
    $stmt->close();

    // Se già verificato, conferma
    if ($verifiedAt) {
        echo json_encode([
            'status' => 'verified',
            'domain' => $domain,
        ]);
        $conn->close();
        exit();
    }

    // Cerca il record TXT tramite Google DoH
    $txtName = "_patchpulse.{$domain}";
    $expectedValue = "patchpulse-verify={$expectedToken}";

    $dohUrl = "https://dns.google/resolve?" . http_build_query([
        'name' => $txtName,
        'type' => 'TXT',
    ]);

    $context = stream_context_create([
        'http' => [
            'header'  => "Accept: application/json\r\n",
            'method'  => 'GET',
            'timeout' => 5,
        ],
    ]);

    $dohResponse = @file_get_contents($dohUrl, false, $context);
    if ($dohResponse === false) {
        // Fallback: prova Cloudflare DoH
        $dohUrl = "https://cloudflare-dns.com/dns-query?" . http_build_query([
            'name' => $txtName,
            'type' => 'TXT',
        ]);
        $context = stream_context_create([
            'http' => [
                'header'  => "Accept: application/dns-json\r\n",
                'method'  => 'GET',
                'timeout' => 5,
            ],
        ]);
        $dohResponse = @file_get_contents($dohUrl, false, $context);
    }

    if ($dohResponse === false) {
        $conn->close();
        throw new Exception(t('vd.dns_failed', false), 503);
    }

    $dnsData = json_decode($dohResponse, true);
    $answers = $dnsData['Answer'] ?? [];

    // Cerca il record TXT corrispondente
    $found = false;
    foreach ($answers as $answer) {
        if (($answer['type'] ?? 0) === 16) { // TXT record
            // I record TXT da DoH possono avere apici, rimuovili
            $value = trim($answer['data'] ?? '', '"\'');
            if ($value === $expectedValue) {
                $found = true;
                break;
            }
        }
    }

    if (!$found) {
        echo json_encode([
            'status' => 'not_found',
            'domain' => $domain,
            'expected_name' => $txtName,
            'expected_value' => $expectedValue,
            'message' => t('vd.txt_not_found', false),
        ]);
        $conn->close();
        exit();
    }

    // Verificato! Aggiorna il DB (nessuna scadenza — ri-verifica live prima di ogni scan)
    $update = $conn->prepare("UPDATE verified_domains SET verified_at = NOW() WHERE id = ?");
    $update->bind_param("i", $domainId);
    $update->execute();
    $update->close();

    // Log
    $userIp = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? 'unknown';
    if (!filter_var($userIp, FILTER_VALIDATE_IP)) $userIp = 'unknown';

    $log = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, ?, ?)");
    $action = "domain_verified:{$domain}";
    $log->bind_param("iss", $userId, $action, $userIp);
    $log->execute();
    $log->close();

    $conn->close();

    echo json_encode([
        'status' => 'verified',
        'domain' => $domain,
    ]);

} catch (Exception $e) {
    $code = $e->getCode();
    if ($code < 400 || $code > 599) $code = 500;
    http_response_code($code);
    echo json_encode(['error' => $e->getMessage()]);
}
