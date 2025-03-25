<?php
header("Access-Control-Allow-Origin: *");
ini_set('display_errors', 0);
ini_set('html_errors', 0);

// Aggiungi questa linea per gestire CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

header('Content-Type: application/json');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Verifica se è una richiesta POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metodo non consentito', 405);
    }

    // Verifica se è presente il parametro target
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['target'])) {
        throw new Exception('Target URL non fornito', 400);
    }

    $target = $data['target'];
    if (!filter_var($target, FILTER_VALIDATE_URL)) {
        throw new Exception('Target URL non valido', 400);
    }

    $target = escapeshellarg($target);
    $scanId = uniqid();
    
    $jarPath = __DIR__ . "/securitychecker-1.0-SNAPSHOT.jar";
    if (!file_exists($jarPath)) {
        throw new Exception("File JAR non trovato", 500);
    }
    
    $outputFile = tempnam(sys_get_temp_dir(), 'scan_out_');
    $errorFile = tempnam(sys_get_temp_dir(), 'scan_err_');
    $pidFile = tempnam(sys_get_temp_dir(), 'scan_pid_');
    
    chmod($outputFile, 0600);
    chmod($errorFile, 0600);
    chmod($pidFile, 0600);
    
    $command = sprintf(
        '(echo %s | java -jar "%s" > %s 2> %s & echo $! > %s)&',
        $target,
        $jarPath,
        escapeshellarg($outputFile),
        escapeshellarg($errorFile),
        escapeshellarg($pidFile)
    );
    
    exec($command);
    usleep(100000);
    
    $pid = @file_get_contents($pidFile);
    if (empty($pid)) {
        throw new Exception('Avvio processo fallito', 500);
    }
    
    $_SESSION['scan_' . $scanId] = [
        'pid' => trim($pid),
        'output_file' => $outputFile,
        'error_file' => $errorFile,
        'pid_file' => $pidFile,
        'completed' => false,
        'start_time' => time()
    ];
    
    http_response_code(200);
    echo json_encode([
        'scanId' => $scanId,
        'status' => 'started'
    ]);
    exit;
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'error' => $e->getMessage(),
        'code' => $e->getCode() ?: 500
    ]);
    exit;
}
?>