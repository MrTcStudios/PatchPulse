<?php
if (
    isset($_SERVER['SCRIPT_FILENAME'])
    && realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)
) {
    http_response_code(403);
    exit('Forbidden');
}

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

ini_set('session.gc_maxlifetime', 3600); // 1 ora
ini_set('session.cookie_lifetime', 0);   // fino a chiusura browser

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

require_once __DIR__ . '/security/session_guard.php';
pp_session_is_valid($conn);

if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    $adminNow   = time();
    $ipMismatch = isset($_SESSION['admin_ip']) && $_SESSION['admin_ip'] !== ($_SERVER['REMOTE_ADDR'] ?? '');
    $idleOut    = isset($_SESSION['admin_last_activity']) && ($adminNow - (int)$_SESSION['admin_last_activity']) > 1800;
    $absOut     = isset($_SESSION['admin_created_at'])    && ($adminNow - (int)$_SESSION['admin_created_at'])    > 14400;

    if ($ipMismatch || $idleOut || $absOut) {
        unset($_SESSION['admin'], $_SESSION['admin_ip'], $_SESSION['admin_csrf'],
              $_SESSION['admin_last_activity'], $_SESSION['admin_created_at']);
    } else {
        $_SESSION['admin_last_activity'] = $adminNow;
        if (!isset($_SESSION['admin_created_at'])) {
            $_SESSION['admin_created_at'] = $adminNow;
        }
    }
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
