<?php
// start_scan.php
ini_set('display_errors', 0);
ini_set('html_errors', 0);

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    if (!isset($_POST['target'])) {
        throw new Exception('Target URL non fornito');
    }

    $target = escapeshellarg($_POST['target']);
    $scanId = uniqid();
    
    $jarPath = __DIR__ . "/securitychecker-1.0-SNAPSHOT.jar";
    if (!file_exists($jarPath)) {
        throw new Exception("File JAR non trovato in: " . $jarPath);
    }
    
    // Crea file temporanei per l'output con nomi basati sul scanId
    $outputFile = sys_get_temp_dir() . "/scan_output_" . $scanId . ".txt";
    $errorFile = sys_get_temp_dir() . "/scan_error_" . $scanId . ".txt";
    $pidFile = sys_get_temp_dir() . "/scan_pid_" . $scanId . ".txt";
    
    $command = sprintf('(echo %s | java -jar "%s" > %s 2> %s & echo $! > %s)&',
        $target,
        $jarPath,
        escapeshellarg($outputFile),
        escapeshellarg($errorFile),
        escapeshellarg($pidFile)
    );
    
    exec($command);
    
    // Aspetta un momento che il PID venga scritto
    usleep(100000); // 0.1 secondi
    
    // Leggi il PID
    $pid = @file_get_contents($pidFile);
    if (empty($pid)) {
        throw new Exception('Impossibile avviare il processo Java');
    }
    
    // Salva le informazioni nella sessione
    $_SESSION['scan_' . $scanId] = array(
        'pid' => trim($pid),
        'output_file' => $outputFile,
        'error_file' => $errorFile,
        'pid_file' => $pidFile,
        'completed' => false,
        'start_time' => time()
    );
    
    echo json_encode([
        'scanId' => $scanId,
        'status' => 'started',
        'debug' => [
            'command' => $command,
            'jarPath' => $jarPath,
            'pid' => trim($pid)
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
