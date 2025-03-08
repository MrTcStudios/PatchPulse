<?php
session_start();
$servername = "";
$username = "";
$password = "";
$dbname = "PatchPulseBeta";

$conn = new mysqli($servername, $username, $password, $dbname);

// Se il file maintenance.lock esiste e l'utente NON è admin, mostra la pagina di manutenzione
if (file_exists(__DIR__ . "/gestione-sito/maintenance.lock") && !isset($_SESSION['admin'])) {
    include("maintenance.php");
    exit;
}
?>
