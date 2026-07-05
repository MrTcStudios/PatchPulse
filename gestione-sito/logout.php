<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || empty($_SESSION['admin_csrf'])
    || !hash_equals($_SESSION['admin_csrf'], $_POST['csrf_token'] ?? '')) {
    header("Location: index.php");
    exit;
}

// Pulisci sessione
$_SESSION = [];

// Elimina cookie di sessione
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

header("Location: index.php");
exit;
