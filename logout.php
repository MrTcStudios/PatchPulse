<?php
session_start();
session_unset();
session_destroy(); 


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

if ($deviceType === "mobile") {
            header("Location: loginPage_mobile.php");
        } else {
            header("Location: loginPage.php");
        } 
exit();
?>
