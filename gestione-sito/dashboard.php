<?php
include("../config.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}
// Cambia la password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $new_password = trim($_POST['new_password']);
    // Verifica che la nuova password non sia vuota
    if (!empty($new_password)) {
        file_put_contents('password.txt', $new_password);
        $_SESSION['messagepass1'] = "Password cambiata con successo!";
	header('Location: dashboard.php');
	exit;
    } else {
        $_SESSION['messagepass2'] = "La password non puo'�essere vuota!";
	header('Location: dashboard.php');
	exit;
    }
}
// Attivare/Disattivare la manutenzione
if (isset($_GET['maintenance_on'])) {
    file_put_contents("maintenance.lock", "on");
    $_SESSION['message1'] = "Modalita' manutenzione attivata con successo!";
    header('Location: dashboard.php');
    exit;
}
if (isset($_GET['maintenance_off'])) {
    unlink("maintenance.lock");
    $_SESSION['message2'] = "Modalita' manutenzione disattivata con successo!";
    header('Location: dashboard.php');
    exit;
}
// Eliminare un utente
if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    $conn->query("DELETE FROM users WHERE id = $id");
    $_SESSION['messagedelete1'] = "Utente eliminato con successo!";
    header('Location: dashboard.php');
    exit;
}
// Ultimi 5 utenti registrati
$result = $conn->query("SELECT id, name, email FROM users ORDER BY id DESC LIMIT 5")
    or die("Errore SQL: " . $conn->error);

$cloudflare_zone_id = "";
$cloudflare_api_token = "";

// Livello di sicurezza di Cloudflare
function setCloudflareSecurityLevel($level) {
    global $cloudflare_zone_id, $cloudflare_api_token;

    $ch = curl_init("https://api.cloudflare.com/client/v4/zones/$cloudflare_zone_id/settings/security_level");

    $data = json_encode(["value" => $level]);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $cloudflare_api_token",
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Attivare/Disattivare la modalità Under Attack
if (isset($_GET['under_attack_on'])) {
    $result = setCloudflareSecurityLevel("under_attack");
    $_SESSION['messagesecurity1'] =   "Modalita' Under Attack ATTIVATA!";
    header('Location: dashboard.php');
    exit;
}

if (isset($_GET['under_attack_off'])) {
    $result = setCloudflareSecurityLevel("high");
    $_SESSION['messagesecurity2'] =   "Modalita  Under Attack DISATTIVATA!";
    header('Location: dashboard.php');
    exit;
}
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pannello di Amministrazione</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
:root {
--primary-color: #4a90e2;
--danger-color: #e74c3c;
--success-color: #2ecc71;
--background-color: #f5f7fa;
--card-color: #ffffff;
--text-color: #2c3e50;
}

* {
margin: 0;
padding: 0;
box-sizing: border-box;
}

body {
font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
background-color: var(--background-color);
color: var(--text-color);
line-height: 1.6;
padding: 20px;
}

.container {
max-width: 1200px;
margin: 0 auto;
}

.header {
display: flex;
justify-content: space-between;
align-items: center;
margin-bottom: 30px;
background: var(--card-color);
padding: 20px;
border-radius: 10px;
box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.card {
background: var(--card-color);
padding: 20px;
border-radius: 10px;
box-shadow: 0 2px 10px rgba(0,0,0,0.1);
margin-bottom: 20px;
}

h1 {
color: var(--primary-color);
font-size: 24px;
margin-bottom: 10px;
}

h2 {
color: var(--text-color);
font-size: 20px;
margin-bottom: 15px;
}

.maintenance-controls {
display: flex;
gap: 10px;
margin-bottom: 20px;
}

.btn {
padding: 10px 20px;
border: none;
border-radius: 5px;
cursor: pointer;
font-size: 14px;
transition: opacity 0.2s;
text-decoration: none;
display: inline-block;
}

.btn:hover {
opacity: 0.9;
}

.btn-primary {
background-color: var(--primary-color);
color: white;
}

.btn-danger {
background-color: var(--danger-color);
color: white;
}

.btn-success {
background-color: var(--success-color);
color: white;
}

.form-group {
margin-bottom: 15px;
}

.form-control {
width: 100%;
padding: 10px;
border: 1px solid #ddd;
border-radius: 5px;
font-size: 14px;
max-width: 300px;
}

table {
width: 100%;
border-collapse: collapse;
margin-top: 10px;
}

th, td {
padding: 12px;
text-align: left;
border-bottom: 1px solid #ddd;
}

th {
background-color: #f8f9fa;
font-weight: 600;
}

tr:hover {
background-color: #f8f9fa;
}

.message {
padding: 10px;
border-radius: 5px;
margin-bottom: 20px;
}

.message-info {
background-color: #d4edda;
color: #155724;
border: 1px solid #c3e6cb;
}

.message-success {
background-color: #d4edda;
color: #155724;
border: 1px solid #c3e6cb;
}

.message-error {
background-color: #f8d7da;
color: #721c24;
border: 1px solid #f5c6cb;
}

.icon-btn {
font-size: 16px;
margin-right: 5px;
}

@media (max-width: 768px) {
.header {
    flex-direction: column;
    text-align: center;
}

.maintenance-controls {
    flex-direction: column;
}

.btn {
    width: 100%;
    text-align: center;
}

table {
    display: block;
    overflow-x: auto;
}
}

.info-box {
    background-color: #f8f9fa;
    padding: 1.5rem;
    border-left: 5px solid #3498db;
    margin: 1.5rem 0;
    border-radius: 0 8px 8px 0;
}

.info-box h4 {
    color: #2c3e50;
    margin-top: 0;
    margin-bottom: 1rem;
    font-size: 1.2rem;
}

.info-box ul {
    list-style-type: none;
    padding-left: 0;
    margin: 0;
}

.info-box li {
    margin-bottom: 1rem;
    line-height: 1.6;
    color: #4a5568;
}

.info-box li:last-child {
    margin-bottom: 0;
}

.info-box code {
    background-color: #e9ecef;
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    font-family: monospace;
    color: #e83e8c;
}

.info-box b {
    color: #2c3e50;
    font-weight: 600;
}
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($_SESSION['message1'])): ?>
            <div class="message message-success">
                <?php echo $_SESSION['message1']; ?>
		<?php unset($_SESSION['message1']); ?>
            </div>
        <?php endif; ?>

	<?php if (isset($_SESSION['message2'])): ?>
            <div class="message message-error">
                <?php echo $_SESSION['message2']; ?>
                <?php unset($_SESSION['message2']); ?>
            </div>
        <?php endif; ?>


	<?php if (isset($_SESSION['sending_status'])): ?>
    	   <div class="message message-info">
        	<?php echo $_SESSION['sending_status']; ?>
    	   </div>
	<?php endif; ?>

	<?php if (isset($_SESSION['message_email'])): ?>
    	    <div class="message message-success">
        	<?php echo $_SESSION['message_email']; ?>
        	<?php unset($_SESSION['message_email']); ?>
            </div>
	<?php endif; ?>

	<?php if (isset($_SESSION['error'])): ?>
    	    <div class="message message-error">
        	<?php echo $_SESSION['error']; ?>
        	<?php unset($_SESSION['error']); ?>
    	    </div>
	<?php endif; ?>







	<?php if (isset($_SESSION['messagepass1'])): ?>
            <div class="message message-success">
                <?php echo $_SESSION['messagepass1']; ?>
                <?php unset($_SESSION['messagepass1']); ?>
            </div>
        <?php endif; ?>

	<?php if (isset($_SESSION['messagepass2'])): ?>
            <div class="message message-error">
                <?php echo $_SESSION['messagepass2']; ?>
                <?php unset($_SESSION['messagepass2']); ?>
            </div>
        <?php endif; ?>
	
	<?php if (isset($_SESSION['messagedelete1'])): ?>
            <div class="message message-success">
                <?php echo $_SESSION['messagedelete1']; ?>
                <?php unset($_SESSION['messagedelete1']); ?>
            </div>
        <?php endif; ?>

	<?php if (isset($_SESSION['messagesecurity1'])): ?>
            <div class="message message-success">
                <?php echo $_SESSION['messagesecurity1']; ?>
                <?php unset($_SESSION['messagesecurity1']); ?>
            </div>
        <?php endif; ?>

	<?php if (isset($_SESSION['messagesecurity2'])): ?>
            <div class="message message-error">
                <?php echo $_SESSION['messagesecurity2']; ?>
                <?php unset($_SESSION['messagesecurity2']); ?>
            </div>
        <?php endif; ?>


        <div class="header">
            <h1><i class="fas fa-cog icon-btn"></i>Pannello di Amministrazione</h1>
            <a href="logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt icon-btn"></i>Logout
            </a>
        </div>

        <div class="card">
            <h2><i class="fas fa-tools icon-btn"></i>Gestione Manutenzione</h2>
            <div class="maintenance-controls">
                <a href="?maintenance_on" class="btn btn-danger">
                    <i class="fas fa-lock icon-btn"></i>Attiva Manutenzione
                </a>
                <a href="?maintenance_off" class="btn btn-success">
                    <i class="fas fa-lock-open icon-btn"></i>Disattiva Manutenzione
                </a>
            </div>
        </div>


	 <div class="card">
                <h2><i class="fas fa-tools icon-btn"></i>Notifica di sistema</h2>
		<form action="send_email.php" method="POST">
        <label>Oggetto:</label><br>
        <input type="text" name="subject" required><br><br>
        
        <label>Messaggio:</label><br>
        <textarea name="message" rows="6" required></textarea><br><br>

        <button type="submit">Invia Email</button>

        <div class="info-box">
            <h4>Come formattare il messaggio</h4>
            <ul>
                <li><b>Per aggiungere un link:</b> Scrivi direttamente l'URL, es. <code>https://mrtc.cc</code>. Il sistema lo renderà automaticamente cliccabile.</li>
                <li><b>Per andare a capo:</b> Premi "Invio" per separare i paragrafi.</li>
            </ul>
        </div>
		</form>
        </div>




	<div class="card">
		<h2><i class="fas fa-tools icon-btn"></i>Firewall Rules</h2>
		<form action="cloudflare_logs.php" method="get">
    			<button type="submit" class="btn btn-primary">
        		 <i class="fas fa-save icon-btn"></i> Visualizza le Firewall Rules
    			</button>
		</form>
	</div>

	<div class="card">
            <h2><i class="fas fa-tools icon-btn"></i>Gestione Protezione</h2>
            <div class="maintenance-controls">
                <a href="?under_attack_on" class="btn btn-danger">
                    <i class="fas fa-lock icon-btn"></i>Attiva modalita' Under Attack
                </a>
                <a href="?under_attack_off" class="btn btn-success">
                    <i class="fas fa-lock-open icon-btn"></i>Disattiva modalita' Under Attack
                </a>
            </div>
        </div>

        <div class="card">
            <h2><i class="fas fa-key icon-btn"></i>Cambia Password</h2>
            <form method="post">
                <div class="form-group">
                    <label for="new_password">Nuova Password:</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save icon-btn"></i>Cambia Password
                </button>
            </form>
        </div>

        <div class="card">
            <h2><i class="fas fa-users icon-btn"></i>Ultimi 5 utenti</h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <a href="?delete_user=<?php echo $row['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Sei sicuro di voler eliminare questo utente?');">
                                        <i class="fas fa-trash icon-btn"></i>Elimina
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>



