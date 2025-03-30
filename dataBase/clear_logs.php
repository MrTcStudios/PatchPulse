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


if (!isset($_SESSION['user_id'])) {
    if ($deviceType === "mobile") {
            header("Location: ../loginPage_mobile.php");
        } else {
            header("Location: ../loginPage.php");
        }
    exit();
}

$servername = "";
$username = "";
$password = "";
$dbname = "PatchPulseBeta";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("DELETE FROM activity_logs WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->close();

$conn->close();
if ($deviceType === "mobile") {
            header("Location: ../accountPage_mobile.php");
        } else {
            header("Location: ../accountPage.php");
        }
exit();
?>
