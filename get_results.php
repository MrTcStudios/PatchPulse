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
    // Verifica se è una richiesta GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Metodo non consentito', 405);
    }

    if (!isset($_GET['scanId']) || !ctype_alnum($_GET['scanId'])) {
        throw new Exception('ScanId non valido', 400);
    }

    $scanId = $_GET['scanId'];
    $sessionKey = 'scan_' . $scanId;
    
    if (!isset($_SESSION[$sessionKey])) {
        throw new Exception('Sessione non trovata', 404);
    }
    
    $scan = $_SESSION[$sessionKey];
    $output = "";
    
    if (!$scan['completed']) {
        $isRunning = function_exists('posix_kill') 
            ? posix_kill($scan['pid'], 0) 
            : file_exists("/proc/{$scan['pid']}");
        
        if (file_exists($scan['output_file'])) {
            $output = file_get_contents($scan['output_file']);
        }
        
        if (file_exists($scan['error_file'])) {
            $errorOutput = file_get_contents($scan['error_file']);
            if (!empty($errorOutput)) {
                $output .= "\nERROR: " . $errorOutput;
            }
        }
        
        if (!$isRunning || (time() - $scan['start_time'] > 300)) {
            $scan['completed'] = true;
            @unlink($scan['output_file']);
            @unlink($scan['error_file']);
            @unlink($scan['pid_file']);
            
            if (time() - $scan['start_time'] > 300) {
                $output .= "\nERROR: Timeout";
            }
        }
    }
    
    http_response_code(200);
    echo json_encode([
        'output' => $output,
        'completed' => $scan['completed']
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