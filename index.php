<?php
include("config.php");

// Controlla se il parametro GET "device" è presente (fallback per vecchie visite)
if (isset($_GET['device'])) {
    $device = $_GET['device'];

    // Protezione contro attacchi (evita iniezioni di URL)
    if ($device === "mobile") {
        header("Location: homePage_mobile.php");
        exit;
    } elseif ($device === "desktop") {
        header("Location: homePage.php");
        exit;
    } else {
        die("Errore: parametro non valido.");
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rilevamento Schermo</title>
    <script>
        (function () {
            let screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
            let deviceType = screenWidth <= 500 ? "mobile" : "desktop";

            // Salva il tipo di dispositivo nel Local Storage
            localStorage.setItem("deviceType", deviceType);

            // Reindirizza direttamente senza parametri nell'URL
            if (deviceType === "mobile") {
                window.location.replace("homePage_mobile.php");
            } else {
                window.location.replace("homePage.php");
            }
        })();
    </script>
</head>
<body>
    <h1>Rilevamento del dispositivo...</h1>
</body>
</html>
