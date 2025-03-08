<?php
header("Access-Control-Allow-Origin: *");
ini_set('display_errors', 0);
ini_set('html_errors', 0);

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    if (!isset($_GET['scanId'])) {
        throw new Exception('ScanId non fornito');
    }
    
    $scanId = $_GET['scanId'];
    $sessionKey = 'scan_' . $scanId;
    
    if (!isset($_SESSION[$sessionKey])) {
        throw new Exception('Sessione di scansione non trovata');
    }
    
    $scan = &$_SESSION[$sessionKey];
    
    if (!$scan['completed']) {
        // Controlla se il processo è ancora in esecuzione
        $isRunning = false;
        if (file_exists("/proc/{$scan['pid']}")) {
            $isRunning = true;
        }
        
        // Leggi l'output dai file
        $output = "";
        if (file_exists($scan['output_file'])) {
            $output = file_get_contents($scan['output_file']);
        }
        
        if (file_exists($scan['error_file'])) {
            $errorOutput = file_get_contents($scan['error_file']);
            if (!empty($errorOutput)) {
                $output .= "\nERROR: " . $errorOutput;
            }
        }
        
        // Controlla timeout (5 minuti)
        $timeout = (time() - $scan['start_time']) > 300;
        
        // Se il processo è terminato o c'è un timeout
        if (!$isRunning || $timeout) {
            $scan['completed'] = true;
            
            // Pulisci i file temporanei
            @unlink($scan['output_file']);
            @unlink($scan['error_file']);
            @unlink($scan['pid_file']);
            
            if ($timeout) {
                $output .= "\nERROR: Scan timeout after 5 minutes";
            }
        }
    }
    
    echo json_encode([
        'output' => $output ?? '',
        'completed' => $scan['completed'],
        'debug' => [
            'pid' => $scan['pid'],
            'running' => $isRunning ?? false
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
