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
    if (empty($csrfToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        throw new Exception('Token CSRF non valido', 403);
    }

    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    if (!is_array($input)) {
        throw new Exception('JSON non valido', 400);
    }

    $domain = strtolower(trim($input['domain'] ?? ''));
    if (empty($domain) || !preg_match('/^(?!-)[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,62}[a-zA-Z0-9])?(?:\.(?!-)[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,62}[a-zA-Z0-9])?)*\.[a-zA-Z]{2,}$/', $domain)) {
        throw new Exception('Dominio non valido', 400);
    }

    $db_host = getenv('DB_HOST');
    $db_name = getenv('DB_NAME');
    $db_user = getenv('DB_USER');
    $db_pass = getenv('DB_PASS');

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        throw new Exception('Errore interno', 500);
    }

    $userId = (int)$_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT id, verification_token, verified_at FROM verified_domains WHERE user_id = ? AND domain = ?");
    $stmt->bind_param("is", $userId, $domain);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        $conn->close();
        throw new Exception('Nessuna verifica in sospeso per questo dominio', 404);
    }

    $stmt->bind_result($domainId, $expectedToken, $verifiedAt);
    $stmt->fetch();
    $stmt->close();

    if ($verifiedAt) {
        echo json_encode([
            'status' => 'verified',
            'domain' => $domain,
        ]);
        $conn->close();
        exit();
    }

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
            'timeout' => 10,
        ],
    ]);

    $dohResponse = @file_get_contents($dohUrl, false, $context);
    if ($dohResponse === false) {
        $dohUrl = "https://cloudflare-dns.com/dns-query?" . http_build_query([
            'name' => $txtName,
            'type' => 'TXT',
        ]);
        $context = stream_context_create([
            'http' => [
                'header'  => "Accept: application/dns-json\r\n",
                'method'  => 'GET',
                'timeout' => 10,
            ],
        ]);
        $dohResponse = @file_get_contents($dohUrl, false, $context);
    }

    if ($dohResponse === false) {
        $conn->close();
        throw new Exception('Impossibile verificare il DNS. Riprova tra qualche minuto.', 503);
    }

    $dnsData = json_decode($dohResponse, true);
    $answers = $dnsData['Answer'] ?? [];

    $found = false;
    foreach ($answers as $answer) {
        if (($answer['type'] ?? 0) === 16) {
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
            'message' => 'Record TXT non trovato. Assicurati di averlo aggiunto e attendi qualche minuto per la propagazione DNS.',
        ]);
        $conn->close();
        exit();
    }

    $update = $conn->prepare("UPDATE verified_domains SET verified_at = NOW() WHERE id = ?");
    $update->bind_param("i", $domainId);
    $update->execute();
    $update->close();

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
