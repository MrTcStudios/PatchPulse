<?php
header("Access-Control-Allow-Origin: *");
ini_set('display_errors', 0);
ini_set('html_errors', 0);
header('Content-Type: application/json');

// Verifica che la richiesta provenga da HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    http_response_code(403);
    echo json_encode(['error' => 'Accesso consentito solo via HTTPS']);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Verifica metodo GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Metodo non consentito', 405);
    }

    // Validazione scanId (solo esadecimale, 13 caratteri)
    if (!isset($_GET['scanId']) || !preg_match('/^[a-f0-9]{13}$/', $_GET['scanId'])) {
        throw new Exception('ID scansione non valido', 400);
    }
    $scanId = $_GET['scanId'];
    
    if (!isset($_SESSION['scan_' . $scanId])) {
        throw new Exception('Sessione di scansione non trovata', 404);
    }
    
    $scan = $_SESSION['scan_' . $scanId];
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
    
    echo json_encode([
        'output' => $output,
        'completed' => $scan['completed']
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>