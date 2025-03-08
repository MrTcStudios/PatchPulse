<?php
session_start();

$servername = "";
$username = "";
$password = "";
$dbname = "PatchPulseBeta";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['token'])) {
    die("Token non valido.");
}

$token = $_GET['token'];

$stmt = $conn->prepare("SELECT id, deletion_token_expires FROM users WHERE deletion_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->bind_result($user_id, $expires_at);
$stmt->fetch();
$stmt->close();

if (!$user_id) {
    die("Token non valido o scaduto.");
}

// Verifica se il token è scaduto
if (strtotime($expires_at) < time()) {
    die("Token scaduto. Richiedi nuovamente l'eliminazione dell'account.");
}

// Elimina l'account
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    echo "Account eliminato con successo.";
} else {
    echo "Errore durante l'eliminazione dell'account.";
}

$stmt->close();
$conn->close();
?>
