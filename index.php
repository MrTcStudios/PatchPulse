<?php
include("config.php");

// Controlla parametro GET "device" (fallback)
if (isset($_GET['device'])) {
    $device = $_GET['device'];
    if ($device === "mobile") {
        header("Location: PatchPulse/homePage_mobile.php");
        exit;
    } elseif ($device === "desktop") {
        header("Location: PatchPulse/homePage.php");
        exit;
    } else {
        die("Errore: parametro non valido.");
    }
}

// Aggiungi rilevamento del dispositivo lato server
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$isMobile = false;

// Controllo semplice per dispositivi mobili tramite user agent
if (preg_match'/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent)) {
    $isMobile = true;
}

// Reindirizzamento lato server quando possibile
if ($isMobile) {
    header("Location: PatchPulse/homePage_mobile.php");
    exit;
} else if (strpos($userAgent, 'Mozilla') !== false) { // Assicurati che sia un browser
    header("Location: PatchPulse/homePage.php");
    exit;
}
// Se non riusciamo a determinare con certezza, lasciamo che sia JavaScript a decidere
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rilevamento Schermo</title>
    <style>
        /* Nascondi completamente il contenuto della pagina */
        body {
            margin: 0;
            padding: 0;
            display: none;
        }
    </style>
    <script>
        (function () {
            // Esegui immediatamente
            let screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
            let deviceType = screenWidth <= 500 ? "mobile" : "desktop";
            
            // Salva il tipo di dispositivo nel Local Storage
            localStorage.setItem("deviceType", deviceType);
            
            // Reindirizzamento immediato
            if (deviceType === "mobile") {
                window.location.replace("PatchPulse/homePage_mobile.php");
            } else {
                window.location.replace("PatchPulse/homePage.php");
            }
        })();
    </script>
    <!-- Fallback con meta refresh più veloce (0 secondi) -->
    <meta http-equiv="refresh" content="0;url=PatchPulse/homePage.php">
</head>
<body>
</body>
</html>
