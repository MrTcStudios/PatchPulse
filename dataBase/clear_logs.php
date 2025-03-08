<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../loginPage.php");
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
header("Location: ../accountPage.php");
exit();
?>
