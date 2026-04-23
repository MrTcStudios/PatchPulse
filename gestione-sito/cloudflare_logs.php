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

$rules = [];
$error = '';

$zoneId = getenv('CF_ZONE_ID');
$apiToken = getenv('CF_API_TOKEN');

if (empty($zoneId) || empty($apiToken)) {
    $error = "CF_ZONE_ID o CF_API_TOKEN non configurati nel .env.";
} else {
    $ch = curl_init("https://api.cloudflare.com/client/v4/zones/{$zoneId}/firewall/rules");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$apiToken}",
        "Content-Type: application/json",
    ]);
    $resp = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErr) {
        $error = "Connessione fallita: {$curlErr}. Il container potrebbe non avere accesso a internet.";
    } elseif ($httpCode === 0) {
        $error = "Nessuna risposta da Cloudflare. Verifica che il container app sia sulla rete proxy (esterna).";
    } else {
        $data = json_decode($resp, true);
        if (!($data['success'] ?? false)) {
            $cfErrors = $data['errors'] ?? [];
            $error = "Cloudflare API (HTTP {$httpCode}): " . (!empty($cfErrors) ? ($cfErrors[0]['message'] ?? 'Errore') : 'Risposta non valida');
        } else {
            $rules = $data['result'] ?? [];
        }
    }
}

$appDomain = getenv('APP_DOMAIN') ?: 'patchpulse.org';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PatchPulse - Firewall Rules</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f5f7fa;color:#2c3e50;padding:20px;line-height:1.6}
        .container{max-width:1100px;margin:0 auto}
        .header{display:flex;justify-content:space-between;align-items:center;background:#fff;padding:1.2rem 1.5rem;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.08);margin-bottom:1.5rem}
        .header h1{font-size:1.3rem;color:#4a90e2}
        .card{background:#fff;padding:1.5rem;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.08);margin-bottom:1.2rem}
        .err{background:#f8d7da;color:#721c24;padding:0.8rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem}
        .btn{padding:0.5rem 1rem;border:none;border-radius:6px;cursor:pointer;font-size:0.85rem;text-decoration:none;background:#4a90e2;color:#fff}
        table{width:100%;border-collapse:collapse;font-size:0.85rem}
        th,td{padding:0.6rem 0.8rem;text-align:left;border-bottom:1px solid #eee}
        th{background:#f8f9fa;font-weight:600;font-size:0.78rem;text-transform:uppercase;color:#888}
        .badge{display:inline-block;padding:0.2rem 0.6rem;border-radius:20px;font-size:0.75rem;font-weight:600}
        .badge-block{background:rgba(231,76,60,.1);color:#e74c3c}
        .badge-allow{background:rgba(46,204,113,.1);color:#2ecc71}
        code{background:#f1f1f1;padding:0.15rem 0.4rem;border-radius:4px;font-size:0.8rem}
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Firewall Rules (<?= count($rules) ?>)</h1>
        <a href="dashboard.php" class="btn">← Dashboard</a>
    </div>

    <?php if ($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <?php if (!empty($rules)): ?>
    <div class="card" style="overflow-x:auto">
        <table>
            <thead><tr><th>ID</th><th>Descrizione</th><th>Azione</th><th>Espressione</th></tr></thead>
            <tbody>
            <?php foreach ($rules as $rule): ?>
                <tr>
                    <td><code><?= htmlspecialchars(substr($rule['id'] ?? '', 0, 12)) ?></code></td>
                    <td><?= htmlspecialchars($rule['description'] ?? 'N/A') ?></td>
                    <td><span class="badge <?= ($rule['action'] ?? '') === 'block' ? 'badge-block' : 'badge-allow' ?>"><?= htmlspecialchars($rule['action'] ?? '') ?></span></td>
                    <td><code><?= htmlspecialchars($rule['filter']['expression'] ?? '') ?></code></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php elseif (!$error): ?>
    <div class="card"><p style="color:#888">Nessuna regola firewall trovata.</p></div>
    <?php endif; ?>
</div>
</body>
</html>
