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
<link rel="stylesheet" href="/css/fonts/primary.css">
<style>
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

:root {
    --bg:        #0d0d10;
    --ink:       #ece9e1;
    --ink-2:     #b4afa3;
    --mut:       #87837a;
    --faint:     #5e5b54;
    --line:      rgba(255,255,255,0.11);
    --violet:    #9d86ff;
    --violet-2:  #b7a6ff;
    --ok:        #5fd6a3;
    --amber:     #e3b457;
    --red:       #e8736b;

    --purple:        #8b7cf8;
    --sidebar-bg:    #161616;
    --sidebar-border:#2a2a2a;
    --sidebar-text:  #c8c8c8;
    --sidebar-width: 280px;

    --disp: 'Fraunces', Georgia, 'Times New Roman', serif;
    --sans: 'DM Sans', system-ui, -apple-system, sans-serif;
    --mono: ui-monospace, 'SFMono-Regular', Menlo, Consolas, monospace;
}

html { height: 100%; }
body {
    font-family: var(--sans);
    background: var(--bg);
    color: var(--ink);
    display: flex;
    height: 100vh;
    overflow: hidden;
    -webkit-font-smoothing: antialiased;
    text-rendering: optimizeLegibility;
}
a { color: inherit; }
::selection { background: var(--violet); color: #14121f; }
:focus-visible { outline: 2px solid var(--violet); outline-offset: 3px; }

#vanta-bg { position: fixed; inset: 0; z-index: 0; background: var(--bg); }
#scrim { position: fixed; inset: 0; z-index: 0; pointer-events: none;
    background: linear-gradient(180deg, rgba(13,13,16,0.46) 0%, rgba(13,13,16,0.64) 55%, rgba(13,13,16,0.8) 100%); }

/* ─────────── SIDEBAR ─────────── */
.sidebar { width: var(--sidebar-width); min-width: var(--sidebar-width); height: 100vh; background: var(--sidebar-bg); display: flex; flex-direction: column; padding: 1.5rem 1.2rem; border-right: 1px solid var(--sidebar-border); z-index: 100; overflow-y: auto; overflow-x: hidden; font-family: var(--sans); }
.sidebar-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.logo { display: flex; align-items: center; gap: 0.6rem; text-decoration: none; color: #fff; font-weight: 600; font-size: 1.15rem; letter-spacing: -0.02em; }
.logo img { width: 35px; height: 35px; object-fit: contain; }
.nav-section { margin-bottom: .5rem; }
.nav-item { display: flex; align-items: center; gap: .7rem; padding: .6rem .75rem; border-radius: 10px; text-decoration: none; color: var(--sidebar-text); font-size: .9rem; font-weight: 400; transition: background .18s, color .18s; cursor: pointer; border: none; background: none; width: 100%; text-align: left; }
.nav-item:hover { background: rgba(255,255,255,.06); color: #fff; }
.nav-item.active { background: rgba(139,124,248,.15); color: var(--purple); font-weight: 500; }
.nav-icon { width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; opacity: .8; }
.nav-item.active .nav-icon { opacity: 1; }
.sidebar-bottom { margin-top: auto; padding-top: .8rem; border-top: 1px solid var(--sidebar-border); }
.hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; padding: 8px; background: none; border: none; }
.hamburger span { display: block; width: 22px; height: 2px; background: #fff; border-radius: 2px; transition: all .3s ease; }
.hamburger.active span:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
.hamburger.active span:nth-child(2) { opacity: 0; }
.hamburger.active span:nth-child(3) { transform: rotate(-45deg) translate(5px, -5px); }

/* ─────────── MAIN ─────────── */
.main-wrapper { flex: 1; height: 100vh; overflow-y: auto; overflow-x: hidden; background: transparent; color: var(--ink); position: relative; z-index: 1; scroll-behavior: smooth; }

.page-header { max-width: 1000px; margin: 0 auto; padding: clamp(2.5rem, 7vh, 4.5rem) clamp(1.5rem, 5vw, 3rem) clamp(1rem, 3vw, 1.6rem); }
.page-header-back { display: inline-flex; align-items: center; gap: .45rem; font-size: .82rem; color: var(--mut); text-decoration: none; margin-bottom: 1.6rem; transition: color .2s; }
.page-header-back:hover { color: var(--violet); }
.page-header-back svg { width: 14px; height: 14px; }
.page-header-eyebrow { font-size: .76rem; font-weight: 500; letter-spacing: .18em; text-transform: uppercase; color: var(--mut); }
.page-header-title { font-family: var(--disp); font-optical-sizing: auto; font-weight: 500; font-size: clamp(2.2rem, 5vw, 3.4rem); line-height: 1.02; letter-spacing: -.025em; color: var(--ink); margin: .9rem 0 0; }

.scanner-section { max-width: 1000px; margin: 0 auto; padding: 0 clamp(1.5rem, 5vw, 3rem) clamp(3rem, 8vw, 5rem); }

.scan-meta { display: flex; gap: 1.2rem; flex-wrap: wrap; margin-bottom: 1.8rem; font-size: .85rem; color: var(--mut); }

.detail-section { margin-bottom: 2.2rem; }
.detail-section h3 { font-family: var(--disp); font-size: 1.3rem; font-weight: 500; color: var(--ink); margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem; letter-spacing: -.01em; }
.detail-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: .8rem; }
.detail-item { background: rgba(255,255,255,0.025); border: 1px solid var(--line); border-radius: 0; padding: 1rem 1.2rem; display: flex; align-items: center; gap: .7rem; }
.detail-icon { font-size: 1.2rem; flex-shrink: 0; }
.detail-label { font-size: .72rem; color: var(--mut); font-weight: 600; text-transform: uppercase; letter-spacing: .06em; }
.detail-value { font-size: .92rem; color: var(--ink); word-break: break-word; }

.reco-card { background: rgba(157,134,255,0.05); border: 1px solid rgba(157,134,255,0.16); border-radius: 0; padding: 1rem 1.2rem; margin-bottom: .7rem; font-size: .92rem; color: var(--ink-2); line-height: 1.6; }
.reco-card strong { color: var(--ink); }

.details-table { width: 100%; border-collapse: collapse; font-size: .88rem; margin-top: .5rem; }
.details-table th { text-align: left; color: var(--violet-2); font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; padding: .6rem .8rem; border-bottom: 2px solid rgba(157,134,255,0.2); }
.details-table td { padding: .6rem .8rem; border-bottom: 1px solid var(--line); color: var(--ink-2); }
.risk-low { color: var(--ok); }
.risk-medium { color: var(--amber); }
.risk-high { color: var(--red); }

/* footer */
.foot { max-width: 1000px; margin: 0 auto; border-top: 1px solid var(--line); padding: 1.5rem clamp(1.5rem, 5vw, 3rem) 3rem; display: flex; flex-wrap: wrap; gap: .8rem 1.6rem; align-items: baseline; justify-content: space-between; }
.foot .copy { font-size: .78rem; color: var(--faint); }
.foot .legal { display: flex; flex-wrap: wrap; gap: .5rem 1.3rem; }
.foot a { font-size: .78rem; color: var(--mut); text-decoration: none; transition: color .2s; }
.foot a:hover { color: var(--violet); }

/* ─────────── RESPONSIVE ─────────── */
@media (max-width: 768px) {
    html, body { height: 100%; overflow: hidden; }
    .sidebar { position: fixed; top: 0; left: 0; right: 0; width: 100%; height: auto; min-width: 0; border-right: none; border-bottom: 1px solid var(--sidebar-border); padding: 0; flex-direction: column; overflow: hidden; z-index: 200; }
    .sidebar-top { margin-bottom: 0; padding: 0 1.2rem; width: 100%; height: 56px; flex-shrink: 0; }
    .hamburger { display: flex; }
    .nav-section, .sidebar-bottom { display: none; }
    .sidebar.open { height: auto; max-height: 100vh; overflow-y: auto; }
    .sidebar.open .nav-section { display: flex; flex-direction: column; width: 100%; padding: 0 .6rem; max-height: 55vh; overflow-y: auto; }
    .sidebar.open .sidebar-bottom { display: flex; flex-direction: column; width: 100%; padding: .4rem .6rem .8rem; border-top: 1px solid var(--sidebar-border); margin-top: .2rem; }
    .main-wrapper { position: fixed; top: 56px; left: 0; right: 0; bottom: 0; height: auto; overflow-y: auto; -webkit-overflow-scrolling: touch; overscroll-behavior-y: contain; }
    .detail-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<div id="vanta-bg"></div>
<div id="scrim"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <a href="../home.php" class="logo"><img src="../images/PatchPulseLogo.svg" alt="PatchPulse">PatchPulse</a>
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

    <footer class="foot" id="contatti">
        <span class="copy">&copy; <?= date('Y') ?> PatchPulse</span>
        <span class="legal">
            <a href="../policy/privacy_policy.php" target="_blank"><?= t('footer.privacy') ?></a>
            <a href="../policy/terms&amp;condition.php" target="_blank"><?= t('footer.terms') ?></a>
            <a href="../policy/security-policy.php" target="_blank"><?= t('footer.security') ?></a>
        </span>
    </footer>
</main>

<?php pp_lang_emit_js(); ?>
<script src="../script.js"></script>
<script src="../js/three.min.js"></script>
<script src="../js/vanta.waves.min.js"></script>
<script>
(function () {
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!reduce && window.VANTA && window.VANTA.WAVES) {
        window.VANTA.WAVES({
            el: '#vanta-bg',
            mouseControls: true, touchControls: true, gyroControls: false,
            minHeight: 200.0, minWidth: 200.0, scale: 1.0, scaleMobile: 1.0,
            color: 0x14132a, shininess: 22.0, waveHeight: 13.0, waveSpeed: 0.72, zoom: 0.95
        });
    }
})();
</script>
</body>
</html>
