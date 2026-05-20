<?php
/**
 * Unsubscribe — richiede un token firmato, NON un'email in chiaro.
 * Il link di unsubscribe nelle email deve essere generato così:
 *   $token = hash_hmac('sha256', $user_email, getenv('UNSUBSCRIBE_SECRET'));
 *   $link = "https://{domain}/dataBase/unscribe.php?email=" . urlencode($email) . "&token=" . $token;
 */
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . "/../lang/lang.php";

$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');
$unsubscribeSecret = getenv('UNSUBSCRIBE_SECRET');

if (empty($unsubscribeSecret)) {
    http_response_code(500);
    echo t('unsub.service_unavailable');
    exit();
}

$email = filter_var($_GET['email'] ?? '', FILTER_SANITIZE_EMAIL);
$token = $_GET['token'] ?? '';

// Validazione
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($token)) {
    http_response_code(400);
    echo t('unsub.link_invalid');
    exit();
}

// Verifica firma HMAC — senza questo, chiunque potrebbe cancellare qualsiasi account
$expectedToken = hash_hmac('sha256', $email, $unsubscribeSecret);
if (!hash_equals($expectedToken, $token)) {
    http_response_code(403);
    echo t('unsub.link_expired');
    exit();
}

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    http_response_code(500);
    echo t('flash.internal_error');
    exit();
}

// Elimina solo l'utente con quell'email
$stmt = $conn->prepare("DELETE FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "<!DOCTYPE html><html><body style='font-family:sans-serif;text-align:center;padding:3rem'>";
    echo "<h2>" . t('unsub.account_deleted') . "</h2><p>" . t('unsub.account_deleted_body') . "</p>";
    echo "</body></html>";
} else {
    echo "<!DOCTYPE html><html><body style='font-family:sans-serif;text-align:center;padding:3rem'>";
    echo "<h2>" . t('unsub.no_account') . "</h2><p>" . t('unsub.no_account_body') . "</p>";
    echo "</body></html>";
}

$stmt->close();
$conn->close();
