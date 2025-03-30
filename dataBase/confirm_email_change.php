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

// Verifica che il token sia presente nell'URL
if (isset($_GET['token'])) {
    $token = htmlspecialchars($_GET['token']);

    // Verifica il token nel database
    $stmt = $conn->prepare("SELECT id, temp_mail FROM users WHERE confirmation_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $temp_email);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        $stmt->close();

        // Aggiorna l'email definitiva con quella temporanea e rimuovi il token
        $update_stmt = $conn->prepare("UPDATE users SET email = ?, temp_mail = NULL, confirmation_token = NULL WHERE id = ?");
        $update_stmt->bind_param("si", $temp_email, $user_id);

        if ($update_stmt->execute()) {
            $_SESSION['email_change_message'] = "La tua email è stata aggiornata con successo.";
        } else {
            $_SESSION['email_change_message'] = "Errore nell'aggiornamento dell'email: " . $update_stmt->error;
        }

        $update_stmt->close();
    } else {
        $_SESSION['email_change_message'] = "Token non valido o già utilizzato.";
    }
} else {
    $_SESSION['email_change_message'] = "Token mancante. Verifica l'email e riprova.";
}

$conn->close();

// Reindirizza alla pagina account con il messaggio di successo o errore
if ($deviceType === "mobile") {
            header("Location: ../accountPage_mobile.php");
        } else {
            header("Location: ../accountPage.php");
        }
exit();
?>
