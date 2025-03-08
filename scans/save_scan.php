<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../config.php");


if (!isset($_SESSION['user_id'])) {
    echo "Errore: Utente non loggato!";
    exit;
}

$user_id = $_SESSION['user_id'];
$cookiesEnabled = $_POST['cookiesEnabled'] ?? '';
$doNotTrack = $_POST['doNotTrack'] ?? '';
$browserFingerprinting = $_POST['browserFingerprinting'] ?? '';
$webrtcSupport = $_POST['webrtcSupport'] ?? '';
$httpsOnly = $_POST['httpsOnly'] ?? '';
$blockedResources = $_POST['blockedResources'] ?? '';
$adBlockEnabled = $_POST['adBlockEnabled'] ?? '';

$javascriptStatus = $_POST['javascriptStatus'] ?? '';
$webglFingerprinting = $_POST['webglFingerprinting'] ?? '';
$developerMode = $_POST['developerMode'] ?? '';
$webAssemblySupport = $_POST['webAssemblySupport'] ?? '';
$webWorkersSupported = $_POST['webWorkersSupported'] ?? '';
$mediaQueriesSupported = $_POST['mediaQueriesSupported'] ?? '';
$webNotificationsSupported = $_POST['webNotificationsSupported'] ?? '';
$permissionsAPISupported = $_POST['permissionsAPISupported'] ?? '';
$paymentRequestAPISupported = $_POST['paymentRequestAPISupported'] ?? '';
$htmlCssSupport = $_POST['htmlCssSupport'] ?? '';
$geolocationInfo = $_POST['geolocationInfo'] ?? '';
$sensorsSupported = $_POST['sensorsSupported'] ?? '';
$popupsEnabled = $_POST['popupsEnabled'] ?? '';

$publicIpv4 = $_POST['publicIpv4'] ?? '';
$publicIpv6 = $_POST['publicIpv6'] ?? '';
$browserType = $_POST['browserType'] ?? '';
$browserVersion = $_POST['browserVersion'] ?? '';
$browserLanguage = $_POST['browserLanguage'] ?? '';
$osVersion = $_POST['osVersion'] ?? '';
$incognitoMode = $_POST['incognitoMode'] ?? '';
$deviceMemory = $_POST['deviceMemory'] ?? '';
$cpuThreads = $_POST['cpuThreads'] ?? '';
$cpuCores = $_POST['cpuCores'] ?? '';
$gpuName = $_POST['gpuName'] ?? '';
$colorDepth = $_POST['colorDepth'] ?? '';
$pixelDepth = $_POST['pixelDepth'] ?? '';
$touchSupport = $_POST['touchSupport'] ?? '';
$screenResolution = $_POST['screenResolution'] ?? '';
$mimeTypes = $_POST['mimeTypes'] ?? '';
$referrerPolicy = $_POST['referrerPolicy'] ?? '';
$batteryStatus = $_POST['batteryStatus'] ?? '';
$securityProtocols = $_POST['securityProtocols'] ?? '';

$stmt = $conn->prepare("INSERT INTO scans (
    user_id, cookiesEnabled, doNotTrack, browserFingerprinting, webrtcSupport, httpsOnly, blockedResources, adBlockEnabled, 
    javascriptStatus, webglFingerprinting, developerMode, webAssemblySupport, webWorkersSupported, mediaQueriesSupported, 
    webNotificationsSupported, permissionsAPISupported, paymentRequestAPISupported, htmlCssSupport, geolocationInfo, 
    sensorsSupported, popupsEnabled, publicIpv4, publicIpv6, browserType, browserVersion, browserLanguage, osVersion, 
    incognitoMode, deviceMemory, cpuThreads, cpuCores, gpuName, colorDepth, pixelDepth, touchSupport, screenResolution, 
    mimeTypes, referrerPolicy, batteryStatus, securityProtocols
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "isssssssssssssssssssssssssssssssssssssss",
    $user_id, $cookiesEnabled, $doNotTrack, $browserFingerprinting, $webrtcSupport, $httpsOnly, $blockedResources, $adBlockEnabled,
    $javascriptStatus, $webglFingerprinting, $developerMode, $webAssemblySupport, $webWorkersSupported, $mediaQueriesSupported, 
    $webNotificationsSupported, $permissionsAPISupported, $paymentRequestAPISupported, $htmlCssSupport, $geolocationInfo, 
    $sensorsSupported, $popupsEnabled, $publicIpv4, $publicIpv6, $browserType, $browserVersion, $browserLanguage, $osVersion, 
    $incognitoMode, $deviceMemory, $cpuThreads, $cpuCores, $gpuName, $colorDepth, $pixelDepth, $touchSupport, $screenResolution, 
    $mimeTypes, $referrerPolicy, $batteryStatus, $securityProtocols
);


if ($stmt->execute()) {
    echo "Scans salvati con successo!";
} else {
    echo "Errore nel salvataggio: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
