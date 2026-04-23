<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$dbServer = getenv('DB_HOST');
$dbUser   = getenv('DB_USER');
$dbPass   = getenv('DB_PASS');
$dbName   = getenv('DB_NAME');

if (!$dbServer || !$dbUser || !$dbName) {
    http_response_code(500);
    exit('Database configuration error.');
}

$conn = new mysqli($dbServer, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    http_response_code(500);
    exit('Database connection failed.');
}

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    $mStmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'maintenance_mode'");
    if ($mStmt) {
        $mStmt->execute();
        $mStmt->bind_result($mVal);
        $mStmt->fetch();
        $mStmt->close();
        if ($mVal === 'on') {
            include(__DIR__ . "/maintenance.php");
            exit;
        }
    }
}
