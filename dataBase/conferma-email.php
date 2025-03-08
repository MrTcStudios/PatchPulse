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

$token = $_GET['token'] ?? '';
echo "Token ricevuto: $token";

$stmt = $conn->prepare("UPDATE users SET is_confirmed = TRUE, confirmation_token = NULL WHERE confirmation_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $_SESSION['registration_message'] = "Account confermato con successo!";
} else {
    $_SESSION['registration_message'] = "Token non valido o già utilizzato.";
}

$stmt->close();
$conn->close();

header("Location: ../registerPage.php");
exit();
?>
