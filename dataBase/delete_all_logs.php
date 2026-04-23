<?php
ini_set('display_errors', 0);
error_reporting(0);

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../log-reg.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../account.php");
    exit();
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($csrfToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    header("Location: ../account.php?error=invalid_request");
    exit();
}

$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    header("Location: ../account.php?error=internal");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
if ($user_id <= 0) {
    $conn->close();
    header("Location: ../account.php");
    exit();
}

$conn->begin_transaction();

try {
    $stmt1 = $conn->prepare("DELETE FROM activity_logs WHERE user_id = ?");
    $stmt1->bind_param("i", $user_id);
    $stmt1->execute();
    $stmt1->close();

    $stmt2 = $conn->prepare("DELETE FROM scans WHERE user_id = ?");
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    $stmt2->close();

    $conn->commit();
    $conn->close();

    header("Location: ../account.php?message=logs_deleted");
    exit();
} catch (\Exception $e) {
    $conn->rollback();
    $conn->close();
    error_log("Delete logs failed for user {$user_id}: " . $e->getMessage());
    header("Location: ../account.php?error=delete_failed");
    exit();
}
