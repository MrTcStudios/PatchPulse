<?php
ini_set('display_errors', 0);
error_reporting(0);

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

$appDomain = getenv('APP_DOMAIN') ?: 'patchpulse.org';
header("Access-Control-Allow-Origin: https://{$appDomain}");
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

include("config.php");

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
    if (strlen($rawInput) > 500000) {
        throw new Exception('Payload troppo grande', 400);
    }

    $input = json_decode($rawInput, true);
    if (!is_array($input)) {
        throw new Exception('JSON non valido', 400);
    }

    if (!isset($input['scanId']) || !isset($input['resultsContent']) || !isset($input['targetUrl'])) {
        throw new Exception('Dati mancanti', 400);
    }

    $user_id = (int)$_SESSION['user_id'];
    $scan_name = substr(trim($input['scanName'] ?? 'Vulnerability Scan'), 0, 255);
    $target_url = substr(trim($input['targetUrl']), 0, 2048);
    $results_content = $input['resultsContent'];
    $scan_id = substr(trim($input['scanId']), 0, 64);

    if (!filter_var($target_url, FILTER_VALIDATE_URL)) {
        throw new Exception('URL non valido', 400);
    }

    if (!preg_match('/^[a-f0-9]{32}$/', $scan_id)) {
        throw new Exception('Scan ID non valido', 400);
    }

    $stmt = $conn->prepare("INSERT INTO vulnerability_scans (user_id, scan_name, target_url, scan_results, scan_session_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issss", $user_id, $scan_name, $target_url, $results_content, $scan_id);

    if ($stmt->execute()) {
        $user_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? 'unknown';
        if (!filter_var($user_ip, FILTER_VALIDATE_IP)) $user_ip = 'unknown';

        $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address, created_at) VALUES (?, ?, ?, NOW())");
        $action = "vuln_scan_saved";
        $log_stmt->bind_param("iss", $user_id, $action, $user_ip);
        $log_stmt->execute();
        $log_stmt->close();

        echo json_encode(['success' => true]);
    } else {
        error_log("save_scan_results error: " . $stmt->error);
        throw new Exception('Errore nel salvataggio', 500);
    }

    $stmt->close();

} catch (Exception $e) {
    $code = $e->getCode();
    if ($code < 400 || $code > 599) $code = 500;
    http_response_code($code);
    echo json_encode(['error' => $e->getMessage()]);
}
