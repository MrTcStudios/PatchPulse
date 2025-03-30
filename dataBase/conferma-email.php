<?php
session_start();

function detectDevice() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent)) {
        return "mobile";
    }
    return "desktop";
}

// Usa questa funzione per il reindirizzamento
$deviceType = detectDevice();
$_SESSION['deviceType'] = $deviceType; // salva in sessione se necessario


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

if ($deviceType === "mobile") {
            header("Location: ../registerPage_mobile.php");
        } else {
            header("Location: ../registerPage.php");
        }
exit();
?>
