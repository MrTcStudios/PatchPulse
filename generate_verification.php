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
require_once __DIR__ . "/security/session_guard.php";
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
    if (strlen($rawInput) > 2048) {
        throw new Exception(t('ss.payload_too_big', false), 400);
    }

    $input = json_decode($rawInput, true);
    if (!is_array($input)) {
        throw new Exception(t('vd.json_invalid', false), 400);
    }

    $target = trim($input['target'] ?? '');

    // Validazione URL
    if (!filter_var($target, FILTER_VALIDATE_URL)) {
        throw new Exception(t('ss.url_invalid', false), 400);
    }
    if (!str_starts_with($target, 'https://')) {
        throw new Exception(t('ss.https_only', false), 400);
    }

    $host = parse_url($target, PHP_URL_HOST);
    if (!$host || !preg_match('/^(?!-)[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,62}[a-zA-Z0-9])?(?:\.(?!-)[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,62}[a-zA-Z0-9])?)*\.[a-zA-Z]{2,}$/', $host)) {
        throw new Exception(t('ss.domain_invalid', false), 400);
    }

    // Blocca IP privati
    if (filter_var($host, FILTER_VALIDATE_IP)) {
        throw new Exception(t('ss.ip_not_domain', false), 400);
    }

    // Normalizza: rimuovi www. per la verifica
    $verifyDomain = preg_replace('/^www\./', '', strtolower($host));

    $db_host = getenv('DB_HOST');
    $db_name = getenv('DB_NAME');
    $db_user = getenv('DB_USER');
    $db_pass = getenv('DB_PASS');

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        throw new Exception(t('vd.internal_error', false), 500);
    }

    if (!pp_session_is_valid($conn)) {
        throw new Exception(t('vd.unauthorized', false), 401);
    }

    $userId = (int)$_SESSION['user_id'];

    // Rate limit persistente per utente: senza questo, ogni POST con un dominio
    $retryAfter = 0;
    if (!rl_consume($conn, 'domain.generate', rl_identifier((string)$userId), 10, 600, $retryAfter)) {
        throw new Exception(t('flash.too_many_requests', false), 429);
    }

    $cap = $conn->prepare("SELECT COUNT(*) FROM verified_domains WHERE user_id = ? AND verified_at IS NULL");
    if ($cap) {
        $cap->bind_param("i", $userId);
        $cap->execute();
        $cap->bind_result($pendingCount);
        $cap->fetch();
        $cap->close();
        if ((int)$pendingCount >= 20) {
            throw new Exception(t('flash.too_many_requests', false), 429);
        }
    }

    // Controlla se il dominio è già verificato e non scaduto
    $stmt = $conn->prepare("SELECT id, verified_at, verification_token FROM verified_domains WHERE user_id = ? AND domain = ?");
    $stmt->bind_param("is", $userId, $verifyDomain);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($domainId, $verifiedAt, $existingToken);
        $stmt->fetch();
        $stmt->close();

        // Se già verificato, permetti lo scan
        if ($verifiedAt) {
            echo json_encode([
                'status' => 'verified',
                'domain' => $verifyDomain,
            ]);
            $conn->close();
            exit();
        }

        // Se esiste ma non verificato, restituisci il token esistente
        echo json_encode([
            'status' => 'pending',
            'domain' => $verifyDomain,
            'txt_record' => "patchpulse-verify={$existingToken}",
            'txt_name' => "_patchpulse.{$verifyDomain}",
        ]);
        $conn->close();
        exit();
    }
    $stmt->close();

    // Nuovo dominio: genera token e salva
    $token = bin2hex(random_bytes(16));
    $insert = $conn->prepare("INSERT INTO verified_domains (user_id, domain, verification_token) VALUES (?, ?, ?)");
    $insert->bind_param("iss", $userId, $verifyDomain, $token);

    if (!$insert->execute()) {
        throw new Exception(t('vd.internal_error', false), 500);
    }
    $insert->close();
    $conn->close();

    echo json_encode([
        'status' => 'pending',
        'domain' => $verifyDomain,
        'txt_record' => "patchpulse-verify={$token}",
        'txt_name' => "_patchpulse.{$verifyDomain}",
    ]);

} catch (Exception $e) {
    $code = $e->getCode();
    if ($code < 400 || $code > 599) $code = 500;
    http_response_code($code);
    echo json_encode(['error' => $e->getMessage()]);
}
