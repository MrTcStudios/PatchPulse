<?php
ini_set('display_errors', 0);
error_reporting(0);

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

include("../config.php");
require_once __DIR__ . "/../lang/lang.php";

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
function sv($val, $default = null) {
    if ($default === null) $default = t('sd.unknown', false);
    $v = htmlspecialchars($val ?: $default, ENT_QUOTES, 'UTF-8');
    return $v;
}

// Robust check that a stored scan value matches a "yes/enabled/active" answer,
// regardless of the language the user was using when they ran the scan.
// We never compare to a single localized string — we accept the IT *and* EN
// equivalents because historical scans may be saved in either language.
function sd_matches(string $value, array $tokens): bool {
    $value = trim(mb_strtolower($value));
    foreach ($tokens as $t) {
        if ($value === mb_strtolower($t)) return true;
    }
    return false;
}

// Scan sections
$sections = [
    t('sd.section.privacy', false) => [
        ['Cookies', $scan['cookiesEnabled'] ?? '', '🍪'],
        ['Do Not Track', $scan['doNotTrack'] ?? '', '🚫'],
        ['Browser Fingerprinting', $scan['browserFingerprinting'] ?? '', '🕵️'],
        ['WebRTC Support', $scan['webrtcSupport'] ?? '', '🌐'],
        ['HTTPS Only', $scan['httpsOnly'] ?? '', '🔒'],
        ['Ad Block', $scan['adBlockEnabled'] ?? '', '🛡️'],
        ['Incognito Mode', $scan['incognitoMode'] ?? '', '👁️'],
        ['Referrer Policy', $scan['referrerPolicy'] ?? '', '📋'],
    ],
    t('sd.section.browser_sys', false) => [
        ['Browser', $scan['browserType'] ?? '', '🌍'],
        ['Version', $scan['browserVersion'] ?? '', '📌'],
        ['Language', $scan['browserLanguage'] ?? '', '🗣️'],
        ['OS', $scan['osVersion'] ?? '', '💻'],
        ['JavaScript', $scan['javascriptStatus'] ?? '', '⚙️'],
        ['Developer Mode', $scan['developerMode'] ?? '', '🔧'],
    ],
    t('sd.section.hardware', false) => [
        ['CPU Cores', $scan['cpuCores'] ?? '', '🧠'],
        ['CPU Threads', $scan['cpuThreads'] ?? '', '🧵'],
        ['Device Memory', $scan['deviceMemory'] ?? '', '💾'],
        ['GPU', $scan['gpuName'] ?? '', '🎮'],
        ['Screen Resolution', $scan['screenResolution'] ?? '', '🖥️'],
        ['Color Depth', $scan['colorDepth'] ?? '', '🎨'],
        ['Touch Support', $scan['touchSupport'] ?? '', '👆'],
        ['Battery Status', $scan['batteryStatus'] ?? '', '🔋'],
    ],
    t('sd.section.web_apis', false) => [
        ['WebGL Fingerprinting', $scan['webglFingerprinting'] ?? '', '🖼️'],
        ['WebAssembly', $scan['webAssemblySupport'] ?? '', '⚡'],
        ['Web Workers', $scan['webWorkersSupported'] ?? '', '👷'],
        ['Web Notifications', $scan['webNotificationsSupported'] ?? '', '🔔'],
        ['Permissions API', $scan['permissionsAPISupported'] ?? '', '🔑'],
        ['Payment Request API', $scan['paymentRequestAPISupported'] ?? '', '💳'],
        ['Geolocation', $scan['geolocationInfo'] ?? '', '📍'],
        ['Sensors', $scan['sensorsSupported'] ?? '', '📡'],
    ],
    t('sd.section.network', false) => [
        ['Public IPv4', $scan['publicIpv4'] ?? '', '🌐'],
        ['Public IPv6', $scan['publicIpv6'] ?? '', '🌐'],
        ['Security Protocols', $scan['securityProtocols'] ?? '', '🔐'],
        ['MIME Types', $scan['mimeTypes'] ?? '', '📄'],
    ],
];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(pp_lang_current(), ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('sd.title_tag') ?></title>
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
            <button class="hamburger" id="hamburger" aria-label="<?= t('nav.menu') ?>"><span></span><span></span><span></span></button>
        </div>
    </div>
    <div class="nav-section">
        <a href="../home.php" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span><?= t('nav.homepage') ?></a>
        <a href="../home.php#servizi" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span><?= t('nav.applications') ?></a>
    </div>
    <div class="sidebar-bottom">
        <?php if (isset($_SESSION['user_id'])): ?><a href="../account.php" class="nav-item active"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span><?= t('nav.account') ?></a><?php endif; ?>
        <a href="../settings.php" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg></span><?= t('nav.settings') ?></a>
    </div>
</aside>
<main class="main-wrapper" id="main">
    <div class="page-header">
        <a href="../account.php" class="page-header-back"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg> <?= t('sd.back_account') ?></a>
        <p class="page-header-eyebrow"><?= t('sd.eyebrow') ?></p>
        <h1 class="page-header-title"><?= htmlspecialchars(str_replace('{0}', (string)(int)$scan['id'], t('sd.title', false)), ENT_QUOTES, 'UTF-8') ?></h1>
    </div>
    <div class="scanner-section">
        <div class="scan-meta">
            <span>📅 <?= htmlspecialchars($scan['created_at']) ?></span>
            <span>🆔 <?= htmlspecialchars(str_replace('{0}', (string)(int)$scan['id'], t('sd.scan_no', false)), ENT_QUOTES, 'UTF-8') ?></span>
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
            <h3><?= t('sd.detailed_results') ?></h3>
            <table class="details-table">
                <thead><tr><th><?= t('sd.col_parameter') ?></th><th><?= t('sd.col_value') ?></th><th><?= t('sd.col_risk') ?></th></tr></thead>
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
            <h3><?= t('sd.recommendations') ?></h3>
            <?php
            // Accept both IT and EN token equivalents so historical scans saved
            // in either language still trigger the right recommendation.
            $cookies   = $scan['cookiesEnabled']      ?? '';
            $dnt       = $scan['doNotTrack']          ?? '';
            $webrtc    = $scan['webrtcSupport']       ?? '';
            $https     = $scan['httpsOnly']           ?? '';
            $adblock   = $scan['adBlockEnabled']      ?? '';
            $ipv4      = $scan['publicIpv4']          ?? '';
            $ipv6      = $scan['publicIpv6']          ?? '';
            $webnotif  = $scan['webNotificationsSupported'] ?? '';
            ?>
            <?php if (sd_matches($cookies, ['Sì', 'Yes'])): ?>
            <div class="reco-card"><strong>🍪 Cookie Tracking:</strong> <?= t('sd.reco.cookies') ?></div>
            <?php endif; ?>
            <?php if (sd_matches($dnt, ['Disattivato', 'Disabled'])): ?>
            <div class="reco-card"><strong>🚫 Do Not Track:</strong> <?= t('sd.reco.dnt') ?></div>
            <?php endif; ?>
            <?php if (!empty($scan['browserFingerprinting'])): ?>
            <div class="reco-card"><strong>🕵️ Fingerprinting:</strong> <?= t('sd.reco.fingerprint') ?></div>
            <?php endif; ?>
            <?php if (sd_matches($webrtc, ['Abilitato', 'Enabled'])): ?>
            <div class="reco-card"><strong>🌐 WebRTC:</strong> <?= t('sd.reco.webrtc') ?></div>
            <?php endif; ?>
            <?php if (stripos($https, 'non attivo') !== false || stripos($https, 'not active') !== false || stripos($https, 'http_unsafe') !== false): ?>
            <div class="reco-card"><strong>🔒 HTTPS-Only:</strong> <?= t('sd.reco.https') ?></div>
            <?php endif; ?>
            <?php if (sd_matches($adblock, ['No'])): ?>
            <div class="reco-card"><strong>🛡️ Ad Blocker:</strong> <?= t('sd.reco.adblock') ?></div>
            <?php endif; ?>
            <?php
            $ndTokens = ['', 'N/D', 'N/A'];
            if (!in_array(trim($ipv4), $ndTokens, true) || !in_array(trim($ipv6), $ndTokens, true)): ?>
            <div class="reco-card"><strong>🔒 IP Privacy:</strong> <?= t('sd.reco.ip') ?></div>
            <?php endif; ?>
            <?php if (sd_matches($webnotif, ['Sì', 'Yes']) || stripos($webnotif, 'sì') !== false || stripos($webnotif, 'yes') !== false): ?>
            <div class="reco-card"><strong>🔔 Web Notifications:</strong> <?= t('sd.reco.notif') ?></div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="footer-grid">
            <div class="footer-col"><h4>PatchPulse</h4><p><?= t('footer.tagline_short') ?></p></div>
            <div class="footer-col"><h4><?= t('footer.col.scanners') ?></h4><a href="../browser-scan.php"><?= t('footer.scanner.browser') ?></a><a href="../VulnerabilityScanner.php"><?= t('footer.scanner.vulnerability') ?></a></div>
            <div class="footer-col"><h4><?= t('footer.col.contacts') ?></h4><p>support@mrtc.cc</p></div>
        </div>
        <div class="footer-bottom"><p>&copy; <?= date('Y') ?> PatchPulse. | <a href="../policy/privacy_policy.php"><?= t('footer.privacy') ?></a> | <a href="../policy/terms&condition.php"><?= t('footer.terms') ?></a> | <a href="../policy/security-policy.php"><?= t('footer.security') ?></a></p></div>
    </footer>
</main>
<?php pp_lang_emit_js(); ?>
<script src="../script.js"></script>
</body>
</html>
