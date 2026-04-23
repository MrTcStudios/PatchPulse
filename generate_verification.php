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
        throw new Exception('Utente non loggato', 401);
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

    $host = parse_url($target, PHP_URL_HOST);
    if (!$host || !preg_match('/^(?!-)[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,62}[a-zA-Z0-9])?(?:\.(?!-)[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,62}[a-zA-Z0-9])?)*\.[a-zA-Z]{2,}$/', $host)) {
        throw new Exception('Dominio non valido', 400);
    }

    if (filter_var($host, FILTER_VALIDATE_IP)) {
        throw new Exception('Inserire un dominio, non un indirizzo IP', 400);
    }

    $verifyDomain = preg_replace('/^www\./', '', strtolower($host));

    $db_host = getenv('DB_HOST');
    $db_name = getenv('DB_NAME');
    $db_user = getenv('DB_USER');
    $db_pass = getenv('DB_PASS');

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        throw new Exception('Errore interno', 500);
    }

    $userId = (int)$_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT id, verified_at, verification_token FROM verified_domains WHERE user_id = ? AND domain = ?");
    $stmt->bind_param("is", $userId, $verifyDomain);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($domainId, $verifiedAt, $existingToken);
        $stmt->fetch();
        $stmt->close();

        if ($verifiedAt) {
            echo json_encode([
                'status' => 'verified',
                'domain' => $verifyDomain,
            ]);
            $conn->close();
            exit();
        }

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

    $token = bin2hex(random_bytes(16));
    $insert = $conn->prepare("INSERT INTO verified_domains (user_id, domain, verification_token) VALUES (?, ?, ?)");
    $insert->bind_param("iss", $userId, $verifyDomain, $token);

    if (!$insert->execute()) {
        throw new Exception('Errore interno', 500);
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
