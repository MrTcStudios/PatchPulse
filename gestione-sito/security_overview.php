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

// Gli header di sicurezza (CSP, HSTS, X-Frame-Options, X-Content-Type-Options,
// Referrer-Policy, ...) sono già applicati a tutte le risposte da Apache in
// security.conf: non vanno ripetuti qui (evita header duplicati/in conflitto).

$now = time();

// ── Pannello 1: lockout attivi adesso (aggregati per azione) ──
// rate_limit_lockouts.identifier è un HMAC dell'IP: non lo leggiamo nemmeno.
$lockouts = [];
$lockStmt = $conn->prepare(
    "SELECT action, COUNT(*) AS locked, MAX(lockout_until) AS max_until
     FROM rate_limit_lockouts
     WHERE lockout_until > ?
     GROUP BY action
     ORDER BY locked DESC, max_until DESC"
);
if ($lockStmt) {
    $lockStmt->bind_param("i", $now);
    $lockStmt->execute();
    $res = $lockStmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $lockouts[] = $row;
    }
    $lockStmt->close();
}
$totalLocked = 0;
foreach ($lockouts as $l) {
    $totalLocked += (int)$l['locked'];
}

// ── Pannello 2: pressione per endpoint nell'ultima ora ──
// rate_limit_hits viene ripulita dopo 1h (rl_gc), quindi la finestra massima
// disponibile è 60 minuti. Calcoliamo anche gli ultimi 5 min per i picchi.
$hits = [];
$hourAgo    = $now - 3600;
$fiveMinAgo = $now - 300;
$hitStmt = $conn->prepare(
    "SELECT action,
            SUM(hit_time >= ?) AS last5,
            COUNT(*)           AS lasthour
     FROM rate_limit_hits
     WHERE hit_time >= ?
     GROUP BY action
     ORDER BY lasthour DESC"
);
if ($hitStmt) {
    $hitStmt->bind_param("ii", $fiveMinAgo, $hourAgo);
    $hitStmt->execute();
    $res = $hitStmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $hits[] = $row;
    }
    $hitStmt->close();
}

// ── Helper formato durata ──
function fmtDuration(int $seconds): string {
    $seconds = max(0, $seconds);
    $m = intdiv($seconds, 60);
    $s = $seconds % 60;
    return $m > 0 ? "{$m}m {$s}s" : "{$s}s";
}

function esc(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>PatchPulse - Monitoraggio Sicurezza</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f5f7fa;color:#2c3e50;padding:20px;line-height:1.6}
        .container{max-width:1100px;margin:0 auto}
        .header{display:flex;justify-content:space-between;align-items:center;background:#fff;padding:1.2rem 1.5rem;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.08);margin-bottom:1.5rem}
        .header h1{font-size:1.3rem;color:#4a90e2}
        .header .actions{display:flex;gap:0.5rem;align-items:center}
        .card{background:#fff;padding:1.5rem;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.08);margin-bottom:1.2rem}
        .card h2{font-size:1.1rem;margin-bottom:0.3rem;color:#333}
        .card .sub{color:#888;font-size:0.82rem;margin-bottom:1rem}
        .btn{padding:0.5rem 1rem;border:none;border-radius:6px;cursor:pointer;font-size:0.85rem;text-decoration:none;background:#4a90e2;color:#fff;display:inline-block}
        .btn:hover{opacity:0.9}
        .btn-light{background:#eef2f7;color:#4a5568}
        table{width:100%;border-collapse:collapse;font-size:0.88rem}
        th,td{padding:0.6rem 0.8rem;text-align:left;border-bottom:1px solid #eee}
        th{background:#f8f9fa;font-weight:600;font-size:0.78rem;text-transform:uppercase;color:#888}
        td.num{text-align:left;font-variant-numeric:tabular-nums}
        code{background:#f1f1f1;padding:0.15rem 0.4rem;border-radius:4px;font-size:0.82rem}
        .badge{display:inline-block;padding:0.15rem 0.55rem;border-radius:20px;font-size:0.74rem;font-weight:600}
        .badge-red{background:rgba(231,76,60,.12);color:#e74c3c}
        .badge-amber{background:rgba(243,156,18,.14);color:#d35400}
        .badge-green{background:rgba(46,204,113,.12);color:#27ae60}
        .empty{color:#888;font-size:0.9rem;padding:0.5rem 0}
        .summary{font-size:0.9rem;color:#555;margin-bottom:0.8rem}
        .summary strong{color:#2c3e50}
        .note{color:#999;font-size:0.78rem;margin-top:0.6rem}
        @media(max-width:768px){
            .header{flex-direction:column;align-items:flex-start;gap:0.8rem}
            .header .actions{flex-wrap:wrap}
            .card{padding:1.1rem;overflow-x:auto}
            table{font-size:0.8rem}
            th,td{padding:0.5rem 0.55rem}
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Monitoraggio Sicurezza</h1>
        <div class="actions">
            <span style="font-size:0.82rem;color:#888">Aggiornato alle <?= esc(date('H:i:s', $now)) ?></span>
            <a href="security_overview.php" class="btn btn-light">&#8635; Aggiorna</a>
            <a href="dashboard.php" class="btn">&larr; Dashboard</a>
        </div>
    </div>

    <!-- Pannello 1: lockout attivi -->
    <div class="card">
        <h2>Lockout attivi adesso</h2>
        <p class="sub">Identificatori (IP in forma di hash) attualmente bloccati dal rate limiter, raggruppati per azione.</p>

        <?php if ($totalLocked > 0): ?>
            <p class="summary"><strong><?= (int)$totalLocked ?></strong> identificatori bloccati su <strong><?= count($lockouts) ?></strong> azion<?= count($lockouts) === 1 ? 'e' : 'i' ?>.</p>
            <table>
                <thead><tr><th>Azione</th><th>Identificatori bloccati</th><th>Sblocco tra (max)</th></tr></thead>
                <tbody>
                <?php foreach ($lockouts as $l): ?>
                    <tr>
                        <td><code><?= esc((string)$l['action']) ?></code></td>
                        <td class="num"><span class="badge badge-red"><?= (int)$l['locked'] ?></span></td>
                        <td class="num"><?= esc(fmtDuration((int)$l['max_until'] - $now)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="empty">Nessun lockout attivo in questo momento. <span class="badge badge-green">tutto ok</span></p>
        <?php endif; ?>
    </div>

    <!-- Pannello 2: pressione per endpoint, ultima ora -->
    <div class="card">
        <h2>Pressione sui rate limit &mdash; ultima ora</h2>
        <p class="sub">Numero di richieste registrate per azione. La colonna &laquo;5 min&raquo; evidenzia i picchi in corso.</p>

        <?php if (!empty($hits)): ?>
            <table>
                <thead><tr><th>Azione</th><th>Ultimi 5 min</th><th>Ultima ora</th></tr></thead>
                <tbody>
                <?php foreach ($hits as $h): ?>
                    <?php
                        $last5   = (int)$h['last5'];
                        $lastH   = (int)$h['lasthour'];
                        $cls     = $last5 >= 20 ? 'badge-red' : ($last5 >= 5 ? 'badge-amber' : 'badge-green');
                    ?>
                    <tr>
                        <td><code><?= esc((string)$h['action']) ?></code></td>
                        <td class="num"><span class="badge <?= $cls ?>"><?= $last5 ?></span></td>
                        <td class="num"><?= $lastH ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="empty">Nessuna richiesta rate-limited registrata nell'ultima ora.</p>
        <?php endif; ?>

        <p class="note">Nota: <code>rate_limit_hits</code> viene ripulita automaticamente dopo 60 minuti, quindi questa &egrave; la finestra massima disponibile. I dati sono aggregati: nessun IP o identificatore in chiaro &egrave; mostrato.</p>
    </div>
</div>
</body>
</html>
