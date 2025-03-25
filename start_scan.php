<?php
header("Access-Control-Allow-Origin: *");
ini_set('display_errors', 0);
ini_set('html_errors', 0);
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Verifica metodo POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metodo non consentito', 405);
    }

    // Leggi l'input JSON
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['target'])) {
        throw new Exception('Target URL non fornito', 400);
    }

    $target = $input['target'];

    // Validazione HTTPS
    if (!filter_var($target, FILTER_VALIDATE_URL)) {
        throw new Exception('Formato URL non valido', 400);
    }

    $parsed = parse_url($target);
    if (strtolower($parsed['scheme'] ?? '') !== 'https') {
        throw new Exception('Solo URL HTTPS sono permessi', 400);
    }

    // Validazione dominio
    $host = strtolower($parsed['host'] ?? '');
    if (!preg_match('/^([a-z0-9-]+\.)+[a-z]{2,}$/', $host)) {
        throw new Exception('Dominio non valido', 400);
    }

    // Blocca indirizzi locali/privati
    $forbidden = ['localhost', '127.0.0.1', '::1', '192.168.', '10.', '172.16.'];
    foreach ($forbidden as $item) {
        if (str_starts_with($host, $item)) {
            throw new Exception('Scansione di indirizzi locali vietata', 403);
        }
    }

    // Resto del codice invariato...
    $target = escapeshellarg($target);
    $scanId = uniqid();
    
    $jarPath = __DIR__ . "/securitychecker-V1.1.jar";
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
    
    echo json_encode([
        'scanId' => $scanId,
        'status' => 'started'
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>