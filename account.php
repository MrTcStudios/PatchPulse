<?php

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

include("config.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: log-reg.php");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    session_destroy();
    header("Location: home.php");
    exit();
}

$stmt->bind_result($uid, $userName, $userEmail);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("
    SELECT id, cookiesEnabled, doNotTrack, browserFingerprinting,
        webrtcSupport, httpsOnly, adBlockEnabled, javascriptStatus,
        webglFingerprinting, developerMode, webAssemblySupport, webWorkersSupported,
        mediaQueriesSupported, webNotificationsSupported, permissionsAPISupported,
        paymentRequestAPISupported, htmlCssSupport, geolocationInfo, sensorsSupported,
        publicIpv4, publicIpv6, browserType, browserVersion,
        browserLanguage, osVersion, incognitoMode, deviceMemory, cpuThreads, cpuCores,
        gpuName, colorDepth, pixelDepth, touchSupport, screenResolution, mimeTypes,
        referrerPolicy, batteryStatus, securityProtocols, created_at
    FROM scans WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$storage_used = 0;
$scan_count = 0;
while ($row = $result->fetch_assoc()) {
    $scan_count++;
    foreach ($row as $value) {
        if ($value !== null) $storage_used += strlen($value);
    }
}
$storage_used += $scan_count * 100;
$storage_used = round($storage_used / (1024 * 1024), 2);
$total_storage = 1000;
$storage_percentage = min(100, round(($storage_used / $total_storage) * 100, 1));

$stmt->close();

$msg_password = $_SESSION['password_change_message'] ?? '';
$msg_email = $_SESSION['email_change_message'] ?? '';
$msg_delete = $_SESSION['account_delete_message'] ?? '';
$msg_general = $_SESSION['account_message'] ?? '';
unset($_SESSION['password_change_message'], $_SESSION['email_change_message'], $_SESSION['account_delete_message'], $_SESSION['account_message']);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PatchPulse - Account</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .acc-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .acc-card {
            background: #fff;
            border: 1px solid rgba(0,0,0,0.07);
            border-radius: 16px;
            padding: 1.8rem;
        }
        .acc-card.full { grid-column: 1 / -1; }
        .acc-card h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .acc-card h3 .icon { font-size: 1.2rem; }

        /* Profile header */
        .profile-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .profile-avatar {
            width: 52px; height: 52px;
            border-radius: 50%;
            background: var(--purple);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.3rem;
            flex-shrink: 0;
        }
        .profile-name { font-weight: 600; color: #1a1a1a; font-size: 1.05rem; }
        .profile-email { color: #888; font-size: 0.88rem; }

        /* Storage */
        .storage-bar-bg {
            background: rgba(0,0,0,0.06);
            border-radius: 20px;
            height: 10px;
            overflow: hidden;
            margin: 0.8rem 0 0.5rem;
        }
        .storage-bar-fill {
            height: 100%;
            background: var(--purple);
            border-radius: 20px;
            transition: width 0.6s;
        }
        .storage-text { font-size: 0.82rem; color: #888; }

        /* Forms */
        .acc-input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: #f8f7ff;
            border: 1.5px solid rgba(139,124,248,0.2);
            border-radius: 10px;
            color: #1a1a1a;
            font-size: 0.92rem;
            font-family: 'DM Sans', sans-serif;
            margin-bottom: 0.7rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .acc-input:focus { outline: none; border-color: var(--purple); box-shadow: 0 0 0 3px rgba(139,124,248,0.12); }
        .acc-input::placeholder { color: #bbb; }

        .acc-btn {
            padding: 0.7rem 1.5rem;
            background: var(--purple);
            color: #fff;
            border: none;
            border-radius: 50px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
            width: 100%;
            margin-top: 0.3rem;
        }
        .acc-btn:hover { background: var(--purple-dark); transform: translateY(-1px); }

        .acc-btn.danger {
            background: #dc2626;
        }
        .acc-btn.danger:hover { background: #b91c1c; }

        .acc-btn.warning {
            background: #d97706;
        }
        .acc-btn.warning:hover { background: #b45309; }

        /* Messages */
        .acc-msg {
            padding: 0.7rem 1rem;
            border-radius: 10px;
            font-size: 0.88rem;
            margin-bottom: 1rem;
            background: rgba(139,124,248,0.08);
            color: var(--purple);
            border: 1px solid rgba(139,124,248,0.2);
        }

        /* Logs */
        .log-list { max-height: 320px; overflow-y: auto; }
        .log-row {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.6rem 0.8rem;
            border-radius: 8px;
            margin-bottom: 0.35rem;
            background: rgba(0,0,0,0.02);
            font-size: 0.85rem;
        }
        .log-row .log-date { color: var(--purple); font-weight: 600; font-size: 0.78rem; white-space: nowrap; }
        .log-row .log-action { color: #555; flex: 1; }
        .log-row .log-ip { color: #bbb; font-family: monospace; font-size: 0.78rem; }

        /* Scans */
        .scan-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.6rem 0.8rem;
            border-radius: 8px;
            margin-bottom: 0.35rem;
            background: rgba(0,0,0,0.02);
        }
        .scan-row .scan-date { color: #555; font-size: 0.85rem; }
        .scan-row a {
            color: var(--purple);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.85rem;
        }
        .scan-row a:hover { text-decoration: underline; }

        /* Danger zone */
        .danger-card {
            border-color: rgba(220,38,38,0.2) !important;
            background: rgba(220,38,38,0.02) !important;
        }
        .danger-card h3 { color: #dc2626 !important; }
        .danger-warning { color: #dc2626; font-size: 0.85rem; margin-bottom: 1rem; }
        .danger-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem; }

        /* Logout */
        .logout-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: #dc2626;
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            padding: 0.5rem 0;
            transition: opacity 0.2s;
        }
        .logout-link:hover { opacity: 0.7; }

        @media (max-width: 768px) {
            .acc-grid { grid-template-columns: 1fr; }
            .danger-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <a href="home.php" class="logo">
            <img src="images/PatchPulseLogo.svg" alt="PatchPulse" style="width:35px;height:35px;object-fit:contain;">
            PatchPulse
        </a>
        <div style="display:flex;align-items:center;gap:0.5rem;">
            <button class="bell-btn" title="Notifiche" aria-label="Notifiche">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </button>
            <button class="hamburger" id="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
        </div>
    </div>
    <div class="search-bar">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:#666;flex-shrink:0"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Search" aria-label="Search">
        <span class="search-shortcut">S</span>
    </div>
    <div class="nav-section">
        <a href="home.php#home" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>Homepage</a>
        <a href="home.php#servizi" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>Applications</a>
    </div>
    <div class="sidebar-bottom">
        <a href="home.php#faq" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span>FAQ</a>
        <a href="account.php" class="nav-item active"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>Account</a>
        <a href="#" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg></span>Settings</a>
    </div>
</aside>

<!-- MAIN -->
<main class="main-wrapper" id="main">

    <div class="page-header">
        <a href="home.php" class="page-header-back">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Torna alla Home
        </a>
        <p class="page-header-eyebrow">Area Personale</p>
        <h1 class="page-header-title">Account</h1>
    </div>

    <div class="scanner-section">

        <?php if ($msg_password): ?><div class="acc-msg"><?= htmlspecialchars($msg_password, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($msg_email): ?><div class="acc-msg"><?= htmlspecialchars($msg_email, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($msg_delete): ?><div class="acc-msg"><?= htmlspecialchars($msg_delete, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($msg_general): ?><div class="acc-msg"><?= htmlspecialchars($msg_general, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

        <div class="acc-grid">

            <!-- Profile -->
            <div class="acc-card">
                <div class="profile-header">
                    <div class="profile-avatar"><?= strtoupper(mb_substr(htmlspecialchars($userName), 0, 1)) ?></div>
                    <div>
                        <div class="profile-name"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="profile-email"><?= htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                </div>
                <div class="storage-bar-bg"><div class="storage-bar-fill" style="width:<?= $storage_percentage ?>%"></div></div>
                <div class="storage-text"><?= $storage_used ?> MB / <?= $total_storage ?> MB &middot; <?= $scan_count ?> scansioni</div>
                <br>
                <a href="logout.php" class="logout-link">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Logout
                </a>
            </div>

            <!-- Change Password -->
            <div class="acc-card">
                <h3><span class="icon">🔒</span> Cambia Password</h3>
                <form action="dataBase/change_password.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="password" class="acc-input" name="current_password" placeholder="Password attuale" required>
                    <input type="password" class="acc-input" name="new_password" placeholder="Nuova password" required minlength="8">
                    <input type="password" class="acc-input" name="confirm_new_password" placeholder="Conferma nuova password" required>
                    <button type="submit" name="change_password" class="acc-btn">Aggiorna Password</button>
                </form>
            </div>

            <!-- Change Email -->
            <div class="acc-card">
                <h3><span class="icon">✉️</span> Cambia Email</h3>
                <form action="dataBase/change_email.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="password" class="acc-input" name="current_password" placeholder="Password attuale" required>
                    <input type="email" class="acc-input" name="new_email" placeholder="Nuova email" required>
                    <button type="submit" name="change_email" class="acc-btn">Aggiorna Email</button>
                </form>
            </div>

            <!-- Activity Logs -->
            <div class="acc-card">
                <h3><span class="icon">📋</span> Attività Recente</h3>
                <div class="log-list">
                    <?php
                    $log_stmt = $conn->prepare("SELECT action, ip_address, created_at FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
                    $log_stmt->bind_param("i", $user_id);
                    $log_stmt->execute();
                    $log_stmt->bind_result($l_action, $l_ip, $l_date);
                    $has_logs = false;
                    while ($log_stmt->fetch()):
                        $has_logs = true;
                    ?>
                    <div class="log-row">
                        <span class="log-date"><?= htmlspecialchars($l_date) ?></span>
                        <span class="log-action"><?= htmlspecialchars($l_action) ?></span>
                        <span class="log-ip"><?= htmlspecialchars($l_ip) ?></span>
                    </div>
                    <?php endwhile; $log_stmt->close(); ?>
                    <?php if (!$has_logs): ?><p style="color:#bbb;font-size:0.88rem">Nessuna attività registrata.</p><?php endif; ?>
                </div>
                <form action="dataBase/clear_logs.php" method="post" style="margin-top:0.8rem">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <button type="submit" class="acc-btn warning">Cancella Log</button>
                </form>
            </div>

            <!-- Scans -->
            <div class="acc-card">
                <h3><span class="icon">🔍</span> Scansioni Recenti</h3>
                <div class="log-list">
                    <?php
                    $s_stmt = $conn->prepare("SELECT id, created_at FROM scans WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
                    $s_stmt->bind_param("i", $user_id);
                    $s_stmt->execute();
                    $s_result = $s_stmt->get_result();
                    $has_scans = false;
                    while ($scan = $s_result->fetch_assoc()):
                        $has_scans = true;
                    ?>
                    <div class="scan-row">
                        <span class="scan-date"><?= htmlspecialchars($scan['created_at']) ?></span>
                        <a href="scans/scan_details.php?id=<?= (int)$scan['id'] ?>">Dettagli</a>
                    </div>
                    <?php endwhile; $s_stmt->close(); ?>
                    <?php if (!$has_scans): ?><p style="color:#bbb;font-size:0.88rem">Nessuna scansione trovata.</p><?php endif; ?>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="acc-card full danger-card">
                <h3><span class="icon">⚠️</span> Zona Pericolosa</h3>
                <p class="danger-warning">Queste azioni non possono essere annullate.</p>
                <div class="danger-grid">
                    <form action="dataBase/delete_all_logs.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <button type="submit" class="acc-btn danger" onclick="return confirm('Sei sicuro di voler eliminare tutti i log e le scansioni?')">Elimina Tutti i Dati</button>
                    </form>
                    <form action="dataBase/delete_account.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="password" class="acc-input" name="current_password" placeholder="Password per confermare" required>
                        <button type="submit" class="acc-btn danger" onclick="return confirm('ATTENZIONE: il tuo account verrà eliminato permanentemente. Continuare?')">Elimina Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-grid">
            <div class="footer-col"><h4>PatchPulse</h4><p>Scanner di sicurezza gratuiti per migliorare la tua sicurezza online.</p></div>
            <div class="footer-col">
                <h4>Scanner</h4>
                <a href="browser-scan.php">Browser Scanner</a>
                <a href="VulnerabilityScanner.php">Vulnerability Scanner</a>
                <a href="vpn-checker.php">VPN Checker</a>
                <a href="data-breach-checker.php">Data Breach Checker</a>
            </div>
            <div class="footer-col"><h4>Contatti</h4><p>Email: support@patchpulse.org</p><a href="https://github.com/MrTcStudios/PatchPulse" target="_blank">GitHub</a></div>
            <div class="footer-col"><h4>Risorse</h4><a href="home.php#about">Documentazione</a><a href="home.php#faq">FAQ</a></div>
        </div>
        <div class="footer-bottom"><p>&copy; <?= date('Y') ?> PatchPulse. | <a href="policy/privacy_policy.php">Privacy Policy</a> | <a href="policy/terms&condition.php">Terms</a> | <a href="../policy/security-policy.php">Security</a></p></div>
    </footer>
</main>

<script>
const hamburger = document.getElementById('hamburger');
const sidebar = document.getElementById('sidebar');
hamburger.addEventListener('click', () => { hamburger.classList.toggle('active'); sidebar.classList.toggle('open'); });
document.querySelectorAll('.nav-item').forEach(l => l.addEventListener('click', () => { if(window.innerWidth<=768){hamburger.classList.remove('active');sidebar.classList.remove('open');} }));
</script>
</body>
</html>
