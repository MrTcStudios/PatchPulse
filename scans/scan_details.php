<?php
ini_set('display_errors', 0);
error_reporting(0);

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

include("../config.php");

$encKey = getenv('ENC_KEY');
if (empty($encKey)) {
    header("Location: ../account.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../log-reg.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../account.php");
    exit();
}

function decryptData($data) {
    global $encKey;
    if (empty($data)) return '';
    $raw = base64_decode($data, true);
    if ($raw === false || strlen($raw) < 48) return '';
    $iv = substr($raw, 0, 16);
    $hmac = substr($raw, 16, 32);
    $ciphertext = substr($raw, 48);
    if (!hash_equals($hmac, hash_hmac('sha256', $iv . $ciphertext, $encKey, true))) return '';
    $decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', $encKey, OPENSSL_RAW_DATA, $iv);
    return $decrypted !== false ? $decrypted : '';
}

$scan_id = (int)$_GET['id'];
$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM scans WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $scan_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$scan = $result->fetch_assoc();
$stmt->close();

if (!$scan) {
    header("Location: ../account.php");
    exit();
}

foreach ($scan as $key => $value) {
    if (!in_array($key, ['id', 'user_id', 'created_at'])) {
        $scan[$key] = decryptData($value);
    }
}

$details = [];
$stmt = $conn->prepare("SELECT parameter, value, risk_level FROM scan_details WHERE scan_id = ?");
$stmt->bind_param("i", $scan_id);
$stmt->execute();
$dr = $stmt->get_result();
while ($row = $dr->fetch_assoc()) $details[] = $row;
$stmt->close();

// Helper
function sv($val, $default = 'Unknown') {
    $v = htmlspecialchars($val ?: $default, ENT_QUOTES, 'UTF-8');
    return $v;
}

// Scan sections
$sections = [
    'Privacy & Tracking' => [
        ['Cookies', $scan['cookiesEnabled'] ?? '', '🍪'],
        ['Do Not Track', $scan['doNotTrack'] ?? '', '🚫'],
        ['Browser Fingerprinting', $scan['browserFingerprinting'] ?? '', '🕵️'],
        ['WebRTC Support', $scan['webrtcSupport'] ?? '', '🌐'],
        ['HTTPS Only', $scan['httpsOnly'] ?? '', '🔒'],
        ['Ad Block', $scan['adBlockEnabled'] ?? '', '🛡️'],
        ['Incognito Mode', $scan['incognitoMode'] ?? '', '👁️'],
        ['Referrer Policy', $scan['referrerPolicy'] ?? '', '📋'],
    ],
    'Browser & System' => [
        ['Browser', $scan['browserType'] ?? '', '🌍'],
        ['Version', $scan['browserVersion'] ?? '', '📌'],
        ['Language', $scan['browserLanguage'] ?? '', '🗣️'],
        ['OS', $scan['osVersion'] ?? '', '💻'],
        ['JavaScript', $scan['javascriptStatus'] ?? '', '⚙️'],
        ['Developer Mode', $scan['developerMode'] ?? '', '🔧'],
    ],
    'Hardware' => [
        ['CPU Cores', $scan['cpuCores'] ?? '', '🧠'],
        ['CPU Threads', $scan['cpuThreads'] ?? '', '🧵'],
        ['Device Memory', $scan['deviceMemory'] ?? '', '💾'],
        ['GPU', $scan['gpuName'] ?? '', '🎮'],
        ['Screen Resolution', $scan['screenResolution'] ?? '', '🖥️'],
        ['Color Depth', $scan['colorDepth'] ?? '', '🎨'],
        ['Touch Support', $scan['touchSupport'] ?? '', '👆'],
        ['Battery Status', $scan['batteryStatus'] ?? '', '🔋'],
    ],
    'Web APIs' => [
        ['WebGL Fingerprinting', $scan['webglFingerprinting'] ?? '', '🖼️'],
        ['WebAssembly', $scan['webAssemblySupport'] ?? '', '⚡'],
        ['Web Workers', $scan['webWorkersSupported'] ?? '', '👷'],
        ['Web Notifications', $scan['webNotificationsSupported'] ?? '', '🔔'],
        ['Permissions API', $scan['permissionsAPISupported'] ?? '', '🔑'],
        ['Payment Request API', $scan['paymentRequestAPISupported'] ?? '', '💳'],
        ['Geolocation', $scan['geolocationInfo'] ?? '', '📍'],
        ['Sensors', $scan['sensorsSupported'] ?? '', '📡'],
    ],
    'Network' => [
        ['Public IPv4', $scan['publicIpv4'] ?? '', '🌐'],
        ['Public IPv6', $scan['publicIpv6'] ?? '', '🌐'],
        ['Security Protocols', $scan['securityProtocols'] ?? '', '🔐'],
        ['MIME Types', $scan['mimeTypes'] ?? '', '📄'],
    ],
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PatchPulse - Scan Details</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .detail-section { margin-bottom: 2rem; }
        .detail-section h3 {
            font-size: 0.95rem; font-weight: 600; color: #1a1a1a;
            margin-bottom: 0.8rem; display: flex; align-items: center; gap: 0.5rem;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 0.8rem;
        }
        .detail-item {
            background: #fff;
            border: 1px solid rgba(0,0,0,0.07);
            border-radius: 12px;
            padding: 1rem 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.7rem;
        }
        .detail-icon { font-size: 1.2rem; flex-shrink: 0; }
        .detail-label { font-size: 0.78rem; color: #999; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
        .detail-value { font-size: 0.9rem; color: #333; word-break: break-word; }
        .scan-meta {
            display: flex; gap: 1rem; flex-wrap: wrap;
            margin-bottom: 1.5rem; font-size: 0.85rem; color: #888;
        }
        .reco-card {
            background: rgba(139,124,248,0.04);
            border: 1px solid rgba(139,124,248,0.12);
            border-radius: 12px;
            padding: 1rem 1.2rem;
            margin-bottom: 0.7rem;
            font-size: 0.9rem;
            color: #555;
            line-height: 1.6;
        }
        .reco-card strong { color: #1a1a1a; }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
            margin-top: 0.5rem;
        }
        .details-table th { text-align: left; color: var(--purple); font-size: 0.78rem; text-transform: uppercase; padding: 0.5rem 0.8rem; border-bottom: 2px solid rgba(139,124,248,0.15); }
        .details-table td { padding: 0.5rem 0.8rem; border-bottom: 1px solid rgba(0,0,0,0.05); color: #555; }
        .risk-low { color: #22a06b; } .risk-medium { color: #d97706; } .risk-high { color: #dc2626; }
        @media (max-width: 768px) { .detail-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <a href="../home.php" class="logo"><img src="../images/PatchPulseLogo.svg" alt="PatchPulse" style="width:35px;height:35px;object-fit:contain;">PatchPulse</a>
        <div style="display:flex;align-items:center;gap:0.5rem">
            <button class="hamburger" id="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
        </div>
    </div>
    <div class="nav-section">
        <a href="../home.php" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>Homepage</a>
        <a href="../home.php#servizi" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>Applications</a>
    </div>
    <div class="sidebar-bottom">
        <?php if (isset($_SESSION['user_id'])): ?><a href="../account.php" class="nav-item active"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>Area Personale</a><?php endif; ?>
    </div>
</aside>
<main class="main-wrapper" id="main">
    <div class="page-header">
        <a href="../account.php" class="page-header-back"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg> Torna all'Account</a>
        <p class="page-header-eyebrow">Browser Scan</p>
        <h1 class="page-header-title">Dettagli Scansione #<?= (int)$scan['id'] ?></h1>
    </div>
    <div class="scanner-section">
        <div class="scan-meta">
            <span>📅 <?= htmlspecialchars($scan['created_at']) ?></span>
            <span>🆔 Scan #<?= (int)$scan['id'] ?></span>
        </div>

        <?php foreach ($sections as $title => $items): ?>
        <div class="detail-section">
            <h3><?= htmlspecialchars($title) ?></h3>
            <div class="detail-grid">
                <?php foreach ($items as [$label, $value, $icon]): ?>
                <div class="detail-item">
                    <span class="detail-icon"><?= $icon ?></span>
                    <div>
                        <div class="detail-label"><?= htmlspecialchars($label) ?></div>
                        <div class="detail-value"><?= sv($value) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (count($details) > 0): ?>
        <div class="detail-section">
            <h3>Risultati Dettagliati</h3>
            <table class="details-table">
                <thead><tr><th>Parametro</th><th>Valore</th><th>Rischio</th></tr></thead>
                <tbody>
                    <?php foreach ($details as $d): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['parameter']) ?></td>
                        <td><?= htmlspecialchars($d['value']) ?></td>
                        <td class="risk-<?= strtolower(htmlspecialchars($d['risk_level'])) ?>"><?= htmlspecialchars($d['risk_level']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <div class="detail-section">
            <h3>Raccomandazioni di Sicurezza</h3>
            <?php if (($scan['cookiesEnabled'] ?? '') === "Sì"): ?>
            <div class="reco-card"><strong>🍪 Cookie Tracking:</strong> Considera di disabilitare i cookie di terze parti o usa la modalità privata del browser.</div>
            <?php endif; ?>
            <?php if (($scan['doNotTrack'] ?? '') === "Disattivato"): ?>
            <div class="reco-card"><strong>🚫 Do Not Track:</strong> Attiva "Do Not Track" nelle impostazioni del browser per richiedere ai siti di non tracciarti.</div>
            <?php endif; ?>
            <?php if (!empty($scan['browserFingerprinting'])): ?>
            <div class="reco-card"><strong>🕵️ Fingerprinting:</strong> Installa estensioni come Privacy Badger o Canvas Blocker per ridurre il fingerprinting digitale.</div>
            <?php endif; ?>
            <?php if (($scan['webrtcSupport'] ?? '') === "Abilitato"): ?>
            <div class="reco-card"><strong>🌐 WebRTC:</strong> Usa un'estensione per bloccare WebRTC — può rivelare il tuo IP reale anche con una VPN attiva.</div>
            <?php endif; ?>
            <?php if (($scan['httpsOnly'] ?? '') === "Non Attivo"): ?>
            <div class="reco-card"><strong>🔒 HTTPS-Only:</strong> Attiva la modalità "Solo HTTPS" nel browser per garantire connessioni crittografate.</div>
            <?php endif; ?>
            <?php if (($scan['adBlockEnabled'] ?? '') === "No"): ?>
            <div class="reco-card"><strong>🛡️ Ad Blocker:</strong> Installa un ad blocker per ridurre tracking, malware e migliorare la navigazione.</div>
            <?php endif; ?>
            <?php if (($scan['publicIpv4'] ?? 'N/D') !== 'N/D' || ($scan['publicIpv6'] ?? 'N/D') !== 'N/D'): ?>
            <div class="reco-card"><strong>🔒 IP Privacy:</strong> Considera una VPN per mascherare il tuo indirizzo IP pubblico.</div>
            <?php endif; ?>
            <?php if (($scan['webNotificationsSupported'] ?? '') === "Sì"): ?>
            <div class="reco-card"><strong>🔔 Notifiche Web:</strong> Gestisci con attenzione i permessi di notifica — possono essere usati per tracking.</div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="footer-grid">
            <div class="footer-col"><h4>PatchPulse</h4><p>Scanner di sicurezza gratuiti.</p></div>
            <div class="footer-col"><h4>Scanner</h4><a href="../browser-scan.php">Browser Scanner</a><a href="../VulnerabilityScanner.php">Vulnerability Scanner</a></div>
            <div class="footer-col"><h4>Contatti</h4><p>support@patchpulse.org</p></div>
        </div>
        <div class="footer-bottom"><p>&copy; <?= date('Y') ?> PatchPulse. | <a href="../policy/privacy_policy.php">Privacy</a> | <a href="../policy/terms&condition.php">Terms</a> | <a href="../policy/security-policy.php">Security</a></p></div>
    </footer>
</main>
<script src="../script.js"></script>
</body>
</html>
