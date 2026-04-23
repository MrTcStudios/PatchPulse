<?php
ini_set('display_errors', 0);
error_reporting(0);

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

include("../config.php");

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: index.php");
    exit;
}

if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['admin_csrf'];

function checkCsrf() {
    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) || !isset($_SESSION['admin_csrf']) || !hash_equals($_SESSION['admin_csrf'], $token)) {
        $_SESSION['admin_error'] = "Richiesta non valida (CSRF).";
        header("Location: dashboard.php");
        exit;
    }
}

function cloudflareApi($endpoint, $method = 'GET', $data = null) {
    $zoneId = getenv('CF_ZONE_ID');
    $apiToken = getenv('CF_API_TOKEN');
    if (empty($zoneId) || empty($apiToken)) {
        return ['success' => false, 'error' => 'CF_ZONE_ID o CF_API_TOKEN non configurati nel .env'];
    }

    $url = "https://api.cloudflare.com/client/v4/zones/{$zoneId}/{$endpoint}";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$apiToken}",
        "Content-Type: application/json",
    ]);
    if ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $resp = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErr) return ['success' => false, 'error' => "cURL: {$curlErr}"];
    if ($httpCode === 0) return ['success' => false, 'error' => 'Connessione fallita (il container potrebbe non avere accesso a internet)'];

    $result = json_decode($resp, true);
    if (!($result['success'] ?? false)) {
        $errors = $result['errors'] ?? [];
        $errMsg = !empty($errors) ? ($errors[0]['message'] ?? 'Errore sconosciuto') : "HTTP {$httpCode}";
        return ['success' => false, 'error' => $errMsg, 'result' => $result['result'] ?? null];
    }
    return $result;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    checkCsrf();
    $action = $_POST['action'];

    switch ($action) {
        case 'change_password':
            $currentPass = $_POST['current_password'] ?? '';
            $newPass = $_POST['new_password'] ?? '';
            $adminHash = getenv('ADMIN_PASSWORD_HASH');

            if (empty($currentPass) || empty($newPass)) {
                $_SESSION['admin_error'] = "Compila tutti i campi.";
            } elseif (!password_verify($currentPass, $adminHash)) {
                $_SESSION['admin_error'] = "Password attuale non corretta.";
            } elseif (mb_strlen($newPass) < 12) {
                $_SESSION['admin_error'] = "La nuova password deve avere almeno 12 caratteri.";
            } else {
                $newHash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
                $_SESSION['admin_success'] = "Nuova password hash generata. Aggiorna ADMIN_PASSWORD_HASH nel .env:\n" . $newHash;
            }
            break;

        case 'delete_user':
            $id = (int)($_POST['user_id'] ?? 0);
            if ($id > 0) {
                $conn->begin_transaction();
                try {
                    $s1 = $conn->prepare("DELETE FROM activity_logs WHERE user_id = ?");
                    $s1->bind_param("i", $id); $s1->execute(); $s1->close();

                    $s2 = $conn->prepare("DELETE FROM scans WHERE user_id = ?");
                    $s2->bind_param("i", $id); $s2->execute(); $s2->close();

                    $s3 = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $s3->bind_param("i", $id); $s3->execute();

                    if ($s3->affected_rows > 0) {
                        $conn->commit();
                        $_SESSION['admin_success'] = "Utente #{$id} eliminato.";
                    } else {
                        $conn->rollback();
                        $_SESSION['admin_error'] = "Utente non trovato.";
                    }
                    $s3->close();
                } catch (Exception $e) {
                    $conn->rollback();
                    $_SESSION['admin_error'] = "Errore durante l'eliminazione.";
                }
            }
            break;

        case 'maintenance_on':
            $ms = $conn->prepare("UPDATE site_settings SET setting_value = 'on' WHERE setting_key = 'maintenance_mode'");
            if ($ms && $ms->execute() && $ms->affected_rows >= 0) {
                $_SESSION['admin_success'] = "Manutenzione attivata.";
            } else {
                $_SESSION['admin_error'] = "Errore DB. Hai eseguito la migration site_settings?";
            }
            if ($ms) $ms->close();
            break;

        case 'maintenance_off':
            $ms = $conn->prepare("UPDATE site_settings SET setting_value = 'off' WHERE setting_key = 'maintenance_mode'");
            if ($ms && $ms->execute()) {
                $_SESSION['admin_success'] = "Manutenzione disattivata.";
            } else {
                $_SESSION['admin_error'] = "Errore DB.";
            }
            if ($ms) $ms->close();
            break;

        case 'under_attack_on':
            $resp = cloudflareApi('settings/security_level', 'PATCH', ['value' => 'under_attack']);
            if ($resp && ($resp['success'] ?? false)) {
                $_SESSION['admin_success'] = "Under Attack attivato.";
            } else {
                $_SESSION['admin_error'] = "Cloudflare: " . ($resp['error'] ?? 'Errore sconosciuto');
            }
            break;

        case 'under_attack_off':
            $resp = cloudflareApi('settings/security_level', 'PATCH', ['value' => 'high']);
            if ($resp && ($resp['success'] ?? false)) {
                $_SESSION['admin_success'] = "Under Attack disattivato.";
            } else {
                $_SESSION['admin_error'] = "Cloudflare: " . ($resp['error'] ?? 'Errore sconosciuto');
            }
            break;
    }

    header("Location: dashboard.php");
    exit;
}

$users = [];
$stmt = $conn->prepare("SELECT id, name, email FROM users ORDER BY id DESC LIMIT 20");
if ($stmt) {
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) $users[] = $row;
    $stmt->close();
}

$totalUsers = 0;
$r = $conn->query("SELECT COUNT(*) as c FROM users");
if ($r) { $totalUsers = $r->fetch_assoc()['c']; }

$maintenanceOn = false;
$ms = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'maintenance_mode'");
if ($ms) { $ms->execute(); $ms->bind_result($mVal); $ms->fetch(); $maintenanceOn = ($mVal === 'on'); $ms->close(); }

$msgSuccess = $_SESSION['admin_success'] ?? '';
$msgError = $_SESSION['admin_error'] ?? '';
unset($_SESSION['admin_success'], $_SESSION['admin_error'], $_SESSION['sending_status'], $_SESSION['message_email'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PatchPulse - Admin Dashboard</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f5f7fa;color:#2c3e50;padding:20px;line-height:1.6}
        .container{max-width:1000px;margin:0 auto}
        .header{display:flex;justify-content:space-between;align-items:center;background:#fff;padding:1.2rem 1.5rem;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.08);margin-bottom:1.5rem}
        .header h1{color:#4a90e2;font-size:1.4rem}
        .card{background:#fff;padding:1.5rem;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.08);margin-bottom:1.2rem}
        .card h2{font-size:1.1rem;margin-bottom:1rem;color:#333}
        .msg{padding:0.8rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem;white-space:pre-wrap}
        .msg-ok{background:#d4edda;color:#155724;border:1px solid #c3e6cb}
        .msg-err{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}
        .btn{padding:0.6rem 1.2rem;border:none;border-radius:6px;cursor:pointer;font-size:0.88rem;text-decoration:none;display:inline-block;transition:opacity .2s}
        .btn:hover{opacity:0.85}
        .btn-primary{background:#4a90e2;color:#fff}
        .btn-danger{background:#e74c3c;color:#fff}
        .btn-success{background:#2ecc71;color:#fff}
        .btn-sm{padding:0.4rem 0.8rem;font-size:0.82rem}
        .btn-group{display:flex;gap:0.5rem;flex-wrap:wrap}
        input[type=text],input[type=password],textarea{padding:0.6rem;border:1px solid #ddd;border-radius:6px;font-size:0.9rem;width:100%;max-width:350px;margin-bottom:0.5rem;font-family:inherit}
        table{width:100%;border-collapse:collapse;margin-top:0.5rem;font-size:0.9rem}
        th,td{padding:0.7rem;text-align:left;border-bottom:1px solid #eee}
        th{background:#f8f9fa;font-weight:600;font-size:0.82rem;text-transform:uppercase;color:#888}
        .status-dot{display:inline-block;width:8px;height:8px;border-radius:50%;margin-right:0.4rem}
        .status-on{background:#2ecc71}
        .status-off{background:#e74c3c}
        @media(max-width:768px){.header{flex-direction:column;gap:0.8rem}.btn-group{flex-direction:column}}
    </style>
</head>
<body>
<div class="container">
    <?php if ($msgSuccess): ?><div class="msg msg-ok"><?= htmlspecialchars($msgSuccess, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if ($msgError): ?><div class="msg msg-err"><?= htmlspecialchars($msgError, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

    <div class="header">
        <h1>Admin Dashboard</h1>
        <div style="display:flex;gap:0.5rem;align-items:center">
            <span style="font-size:0.85rem;color:#888"><?= $totalUsers ?> utenti</span>
            <span style="font-size:0.85rem;color:#888">
                <span class="status-dot <?= $maintenanceOn ? 'status-off' : 'status-on' ?>"></span>
                <?= $maintenanceOn ? 'Manutenzione' : 'Online' ?>
            </span>
            <form action="logout.php" method="post" style="display:inline">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <button type="submit" class="btn btn-danger btn-sm">Logout</button>
            </form>
        </div>
    </div>

    <!-- Manutenzione -->
    <div class="card">
        <h2>Manutenzione</h2>
        <div class="btn-group">
            <form method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="action" value="maintenance_on"><button type="submit" class="btn btn-danger" onclick="return confirm('Attivare manutenzione?')">Attiva Manutenzione</button></form>
            <form method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="action" value="maintenance_off"><button type="submit" class="btn btn-success">Disattiva Manutenzione</button></form>
        </div>
    </div>

    <!-- Cloudflare Under Attack -->
    <div class="card">
        <h2>Protezione Cloudflare</h2>
        <div class="btn-group">
            <form method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="action" value="under_attack_on"><button type="submit" class="btn btn-danger" onclick="return confirm('Attivare Under Attack mode?')">Under Attack ON</button></form>
            <form method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>"><input type="hidden" name="action" value="under_attack_off"><button type="submit" class="btn btn-success">Under Attack OFF</button></form>
        </div>
    </div>

    <!-- Notifica email -->
    <div class="card">
        <h2>Notifica di Sistema</h2>
        <form action="send_email.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <label>Oggetto:</label>
            <input type="text" name="subject" required><br>
            <label>Messaggio:</label>
            <textarea name="message" rows="5" required style="max-width:100%"></textarea><br>
            <button type="submit" class="btn btn-primary" onclick="return confirm('Inviare a tutti gli utenti?')">Invia Email</button>
        </form>
    </div>

    <!-- Firewall Rules -->
    <div class="card">
        <h2>Firewall Rules</h2>
        <a href="cloudflare_logs.php" class="btn btn-primary">Visualizza Regole</a>
    </div>

    <!-- Cambia Password -->
    <div class="card">
        <h2>Cambia Password Admin</h2>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="action" value="change_password">
            <input type="password" name="current_password" placeholder="Password attuale" required><br>
            <input type="password" name="new_password" placeholder="Nuova password (min 12 car.)" required minlength="12"><br>
            <button type="submit" class="btn btn-primary">Cambia Password</button>
        </form>
        <p style="color:#888;font-size:0.8rem;margin-top:0.5rem">La nuova password hash verrà mostrata. Copiala nel .env come ADMIN_PASSWORD_HASH.</p>
    </div>

    <!-- Utenti -->
    <div class="card">
        <h2>Ultimi Utenti (<?= $totalUsers ?> totali)</h2>
        <div style="overflow-x:auto">
            <table>
                <thead><tr><th>ID</th><th>Nome</th><th>Email</th><th>Azione</th></tr></thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= (int)$u['id'] ?></td>
                        <td><?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <form method="post" style="display:inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Eliminare utente #<?= (int)$u['id'] ?>?')">Elimina</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
