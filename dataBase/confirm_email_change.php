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
header("Location: ../accountPage.php");
exit();
?>
