<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PatchPulse - Browser Scanner</title>
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <a href="home.php" class="logo">
            <img src="images/PatchPulseLogo.svg" alt="PatchPulse" style="width:35px;height:35px;object-fit:contain;">
            PatchPulse
        </a>
        <div style="display:flex;align-items:center;gap:0.5rem">
            <button class="bell-btn" title="Notifiche" aria-label="Notifiche">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </button>
            <button class="hamburger" id="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
        </div>
    </div>

    <!-- Search -->
    <div class="search-bar">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:#666;flex-shrink:0"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Search" aria-label="Search">
        <span class="search-shortcut">S</span>
    </div>

    <!-- Main nav -->
    <div class="nav-section">
        <a href="home.php" class="nav-item" data-section="home">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>
            Homepage
        </a>
        <a href="home.php#servizi" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>
            Applications
        </a>
        <a href="browser-scan.php" class="nav-item active">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
            Browser Scanner
        </a>
        <a href="VulnerabilityScanner.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
            Website Overview
        </a>
        <a href="vpn-checker.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
            VPN Checker
        </a>
        <a href="data-breach-checker.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
            Data Breach Monitor
        </a>
    </div>

    <div class="nav-divider"></div>

    <!-- Bottom nav -->
    <div class="sidebar-bottom">
        <a href="home.php#faq" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span>
            FAQ
        </a>
        <?php if ($isLoggedIn): ?>
        <a href="account.php" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
            Area Personale
        </a>
        <?php else: ?>
        <a href="log-reg.php#login" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg></span>
            Login
        </a>
        <?php endif; ?>
        <a href="#" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg></span>
            Settings
        </a>
    </div>
</aside>

<!-- MAIN -->
<main class="main-wrapper" id="main">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="dot-grid-header"></div>
        <div class="page-header-content">
            <a href="home.php" class="page-header-back">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                Torna alla Home
            </a>
            <p class="page-header-eyebrow">Scanner di Sicurezza</p>
            <h1 class="page-header-title">Browser Scanner</h1>
            <p class="page-header-desc">Analizza cosa il tuo browser rivela sui tuoi dati personali e sulle tue informazioni di navigazione.</p>
        </div>
    </div>

    <!-- SCANNER -->
    <div class="scanner-section">
        <div class="scanner-card">
        <!-- Tab buttons -->
        <div class="tab-bar">
            <button class="tab-btn active" data-scan="webTracking">🔍 Web Tracking</button>
            <button class="tab-btn" data-scan="functionality">⚙️ Functionality Support</button>
            <button class="tab-btn" data-scan="deviceInfo">💻 Device Information</button>
        </div>

        <!-- Save All Scans (solo se loggato) -->
        <?php if ($isLoggedIn): ?>
        <div class="save-bar">
            <button id="saveScansButton" class="btn-save">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Salva tutte le scansioni
            </button>
        </div>
        <?php endif; ?>

        <!-- Scan Results -->
        <div class="scan-results-container">

            <!-- Web Tracking -->
            <div id="webTracking" class="scanResultZone active">
                <a href="vulnerabilities_info.php#cookies" class="scan-item">
                    <span class="scan-item-label">Cookies</span>
                    <span class="scan-item-value loading" id="cookiesEnabled">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#dnt" class="scan-item">
                    <span class="scan-item-label">Do Not Track</span>
                    <span class="scan-item-value loading" id="doNotTrack">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#fingerprinting" class="scan-item">
                    <span class="scan-item-label">Browser Fingerprinting</span>
                    <span class="scan-item-value loading" id="browserFingerprinting">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#webrtc" class="scan-item">
                    <span class="scan-item-label">WebRTC Support</span>
                    <span class="scan-item-value loading" id="webrtcSupport">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#https" class="scan-item">
                    <span class="scan-item-label">HTTPS Only</span>
                    <span class="scan-item-value loading" id="httpsOnly">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#adblock" class="scan-item">
                    <span class="scan-item-label">AdBlocker</span>
                    <span class="scan-item-value loading" id="adBlockEnabled">Loading...</span>
                </a>
            </div>

            <!-- Functionality -->
            <div id="functionality" class="scanResultZone">
                <a href="vulnerabilities_info.php#javascript" class="scan-item">
                    <span class="scan-item-label">JavaScript</span>
                    <span class="scan-item-value loading" id="javascriptStatus">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#webgl" class="scan-item">
                    <span class="scan-item-label">WebGL</span>
                    <span class="scan-item-value loading" id="webglFingerprinting">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#developer-mode" class="scan-item">
                    <span class="scan-item-label">Developer Mode</span>
                    <span class="scan-item-value loading" id="developerMode">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#webassembly" class="scan-item">
                    <span class="scan-item-label">WebAssembly</span>
                    <span class="scan-item-value loading" id="webAssemblySupport">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#web-workers" class="scan-item">
                    <span class="scan-item-label">Web Workers</span>
                    <span class="scan-item-value loading" id="webWorkersSupported">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#media-queries" class="scan-item">
                    <span class="scan-item-label">Media Queries</span>
                    <span class="scan-item-value loading" id="mediaQueriesSupported">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#web-notifications" class="scan-item">
                    <span class="scan-item-label">Web Notifications</span>
                    <span class="scan-item-value loading" id="webNotificationsSupported">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#permissions-api" class="scan-item">
                    <span class="scan-item-label">Permissions API</span>
                    <span class="scan-item-value loading" id="permissionsAPISupported">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#payment-api" class="scan-item">
                    <span class="scan-item-label">Payment Request API</span>
                    <span class="scan-item-value loading" id="paymentRequestAPISupported">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#html-css" class="scan-item">
                    <span class="scan-item-label">HTML5 / CSS3</span>
                    <span class="scan-item-value loading" id="htmlCssSupport">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#geolocation" class="scan-item">
                    <span class="scan-item-label">Geolocation</span>
                    <span class="scan-item-value loading" id="geolocationInfo">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#sensors" class="scan-item">
                    <span class="scan-item-label">Sensors Support</span>
                    <span class="scan-item-value loading" id="sensorsSupported">Loading...</span>
                </a>
                <iframe id="mapFrame" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src=""></iframe>
            </div>

            <!-- Device Info -->
            <div id="deviceInfo" class="scanResultZone">
                <a href="vulnerabilities_info.php#ipv4" class="scan-item">
                    <span class="scan-item-label">Public IPv4</span>
                    <span class="scan-item-value loading" id="publicIpv4">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#ipv6" class="scan-item">
                    <span class="scan-item-label">Public IPv6</span>
                    <span class="scan-item-value loading" id="publicIpv6">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#browser-type" class="scan-item">
                    <span class="scan-item-label">Browser Type</span>
                    <span class="scan-item-value loading" id="browserType">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#browser-version" class="scan-item">
                    <span class="scan-item-label">Browser Version</span>
                    <span class="scan-item-value loading" id="browserVersion">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#browser-language" class="scan-item">
                    <span class="scan-item-label">Browser Language</span>
                    <span class="scan-item-value loading" id="browserLanguage">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#operating-system" class="scan-item">
                    <span class="scan-item-label">Operating System</span>
                    <span class="scan-item-value loading" id="osVersion">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#incognito" class="scan-item">
                    <span class="scan-item-label">Incognito Mode</span>
                    <span class="scan-item-value loading" id="incognitoMode">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#hardware" class="scan-item">
                    <span class="scan-item-label">Device Memory</span>
                    <span class="scan-item-value loading" id="deviceMemory">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#hardware" class="scan-item">
                    <span class="scan-item-label">CPU Threads</span>
                    <span class="scan-item-value loading" id="cpuThreads">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#hardware" class="scan-item">
                    <span class="scan-item-label">CPU Cores</span>
                    <span class="scan-item-value loading" id="cpuCores">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#hardware" class="scan-item">
                    <span class="scan-item-label">GPU Info</span>
                    <span class="scan-item-value loading" id="gpuName">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#screen" class="scan-item">
                    <span class="scan-item-label">Color Depth</span>
                    <span class="scan-item-value loading" id="colorDepth">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#screen" class="scan-item">
                    <span class="scan-item-label">Pixel Depth</span>
                    <span class="scan-item-value loading" id="pixelDepth">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#touch-support" class="scan-item">
                    <span class="scan-item-label">Touch Support</span>
                    <span class="scan-item-value loading" id="touchSupport">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#screen" class="scan-item">
                    <span class="scan-item-label">Screen Resolution</span>
                    <span class="scan-item-value loading" id="screenResolutionDisplay">Loading...</span>
                    <span id="width" style="display:none">Loading...</span>
                    <span id="height" style="display:none">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#mime-types" class="scan-item">
                    <span class="scan-item-label">MIME Types</span>
                    <span class="scan-item-value loading" id="mimeTypes">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#referrer-policy" class="scan-item">
                    <span class="scan-item-label">Referrer Policy</span>
                    <span class="scan-item-value loading" id="referrerPolicy">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#battery-status" class="scan-item">
                    <span class="scan-item-label">Battery Status</span>
                    <span class="scan-item-value loading" id="batteryStatus">Loading...</span>
                </a>
                <a href="vulnerabilities_info.php#security-protocols" class="scan-item">
                    <span class="scan-item-label">Security Protocols</span>
                    <span class="scan-item-value loading" id="securityProtocols">Loading...</span>
                </a>
            </div>
          </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer id="contatti">
        <div class="footer-grid">
            <div class="footer-col">
                <h4>PatchPulse</h4>
                <p>Scanner di sicurezza gratuiti per migliorare la tua sicurezza online. Identifica vulnerabilità e rischi di privacy.</p>
            </div>
            <div class="footer-col">
                <h4>Scanner</h4>
                <a href="browser-scan.php">Browser Scanner</a>
                <a href="VulnerabilityScanner.php">Website Vulnerability Scanner</a>
                <a href="vpn-checker.php">VPN Security Checker</a>
                <a href="data-breach-checker.php">Data Breach Checker</a>
                <a href="#">Coming Soon...</a>
            </div>
            <div class="footer-col">
                <h4>Contatti</h4>
                <p>Email: support@patchpulse.com</p>
                <a href="https://github.com/MrTcStudios/PatchPulse" target="_blank">GitHub MrTcStudios/PatchPulse</a>
            </div>
            <div class="footer-col">
                <h4>Risorse</h4>
                <a href="account.php">Area Account</a>
                <a href="home.php#about">Documentazione</a>
                <a href="home.php#faq">FAQ</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> PatchPulse. Tutti i diritti riservati.
                <a href="policy/privacy_policy.php" target="_blank">Privacy Policy</a>
                <a href="policy/terms&condition.php" target="_blank">Terms of Service</a>
            	<a href="policy/security-policy.php" target="_blank">Security Policy</a>
	    </p>
        </div>
    </footer>

</main>

<script>
    const hamburger = document.getElementById('hamburger');
    const sidebar = document.getElementById('sidebar');
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        sidebar.classList.toggle('open');
    });
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', () => {
            hamburger.classList.remove('active');
            sidebar.classList.remove('open');
        });
    });

    function animateZone(zone) {
        zone.querySelectorAll('.scan-item').forEach((el, i) => {
            el.classList.remove('visible');
            setTimeout(() => el.classList.add('visible'), i * 40);
        });
    }

    document.querySelectorAll('[data-scan]').forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.dataset.scan;
            const target = document.getElementById(targetId);
            if (!target) return;

            document.querySelectorAll('.scanResultZone').forEach(z => z.classList.remove('active'));
            document.querySelectorAll('[data-scan]').forEach(b => b.classList.remove('active'));

            target.classList.add('active');
            this.classList.add('active');
            animateZone(target);
        });
    });

    window.addEventListener('load', () => {
        const active = document.querySelector('.scanResultZone.active');
        if (active) animateZone(active);
    });

    const observer = new MutationObserver(mutations => {
        mutations.forEach(m => {
            const el = m.target;
            if (el.classList.contains('scan-item-value') && el.textContent.trim() !== 'Loading...') {
                el.classList.remove('loading');
            }
        });
    });
    document.querySelectorAll('.scan-item-value').forEach(el => {
        observer.observe(el, { childList: true, subtree: true, characterData: true });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const saveBtn = document.getElementById('saveScansButton');
        if (saveBtn) saveBtn.addEventListener('click', saveScans);
    });

    async function saveScans() {
        const ids = [
            'cookiesEnabled','doNotTrack','browserFingerprinting','webrtcSupport','httpsOnly','adBlockEnabled',
            'javascriptStatus','webglFingerprinting','developerMode','webAssemblySupport','webWorkersSupported',
            'mediaQueriesSupported','webNotificationsSupported','permissionsAPISupported','paymentRequestAPISupported',
            'htmlCssSupport','geolocationInfo','sensorsSupported',
            'publicIpv4','publicIpv6','browserType','browserVersion','browserLanguage','osVersion','incognitoMode',
            'deviceMemory','cpuThreads','cpuCores','gpuName','colorDepth','pixelDepth','touchSupport',
            'width','height','mimeTypes','referrerPolicy','batteryStatus','securityProtocols'
        ];

        async function tryRevealAndRead(el) {
            if (!el) return '';
            let text = el.innerText?.trim();
            if (text && text !== 'Loading...') return text;
            const zone = el.closest('.scanResultZone');
            if (!zone || zone.classList.contains('active')) return text;
            const prev = zone.style.transition;
            zone.style.transition = 'none';
            zone.classList.add('active');
            await new Promise(r => setTimeout(r, 200));
            text = el.innerText?.trim();
            zone.classList.remove('active');
            zone.style.transition = prev;
            return text;
        }

        const saveBtn = document.getElementById('saveScansButton');
        if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = 'Salvataggio...'; }

        const scanData = {};
        for (let id of ids) {
            const el = document.getElementById(id);
            let value = el ? el.innerText?.trim() : '';
            if (!value || value === 'Loading...') value = await tryRevealAndRead(el);
            scanData[id] = value;
        }
        const w = document.getElementById('width')?.innerText?.trim();
        const h = document.getElementById('height')?.innerText?.trim();
        scanData['screenResolution'] = (w && h) ? `${w} x ${h}` : '';

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const payload = JSON.stringify({
            csrf_token: csrfMeta ? csrfMeta.content : '',
            data: btoa(unescape(encodeURIComponent(JSON.stringify(scanData))))
        });

        try {
            const response = await fetch('scans/save_scan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: payload
            });
            const text = await response.text();
            console.log('Save response:', response.status, text);
            if (response.ok && text.includes('successo')) {
                if (saveBtn) { saveBtn.textContent = '✓ Salvato'; setTimeout(() => { saveBtn.disabled = false; saveBtn.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Salva tutte le scansioni'; }, 2000); }
            } else {
                console.error('Save failed:', text);
                if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = 'Errore: ' + text.substring(0, 50); }
            }
        } catch (err) {
            console.error('Save error:', err);
            if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = 'Errore di connessione'; }
        }
    }
</script>
<script src="script.js"></script>
<script type="module" src="javascript/fastScan.js"></script>
</body>
</html>
