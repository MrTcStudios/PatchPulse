<?php

$servername = "";
$username = "";
$password = "";
$dbname = "PatchPulseBeta";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $_GET['email'] ?? '';

if ($email) {
    $stmt = $conn->prepare("DELETE FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Il tuo account è stato eliminato con successo.";
    } else {
        echo "Errore: Non è stato possibile eliminare l'account.";
    }

    $stmt->close();
}

$conn->close();
?>
