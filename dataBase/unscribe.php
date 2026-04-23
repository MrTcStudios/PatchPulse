<?php
ini_set('display_errors', 0);
error_reporting(0);

$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');
$unsubscribeSecret = getenv('UNSUBSCRIBE_SECRET');

if (empty($unsubscribeSecret)) {
    http_response_code(500);
    echo "Servizio non disponibile.";
    exit();
}

$email = filter_var($_GET['email'] ?? '', FILTER_SANITIZE_EMAIL);
$token = $_GET['token'] ?? '';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($token)) {
    http_response_code(400);
    echo "Link non valido.";
    exit();
}

$expectedToken = hash_hmac('sha256', $email, $unsubscribeSecret);
if (!hash_equals($expectedToken, $token)) {
    http_response_code(403);
    echo "Link non valido o scaduto.";
    exit();
}

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    http_response_code(500);
    echo "Errore interno. Riprova più tardi.";
    exit();
}

$stmt = $conn->prepare("DELETE FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "<!DOCTYPE html><html><body style='font-family:sans-serif;text-align:center;padding:3rem'>";
    echo "<h2>Account eliminato</h2><p>Il tuo account è stato rimosso con successo.</p>";
    echo "</body></html>";
} else {
    echo "<!DOCTYPE html><html><body style='font-family:sans-serif;text-align:center;padding:3rem'>";
    echo "<h2>Nessun account trovato</h2><p>L'email non risulta registrata.</p>";
    echo "</body></html>";
}

$stmt->close();
$conn->close();
