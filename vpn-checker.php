<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PatchPulse - VPN Checker</title>
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
    <div class="search-bar">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:#666;flex-shrink:0"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Search" aria-label="Search">
        <span class="search-shortcut">S</span>
    </div>
    <div class="nav-section">
        <a href="home.php" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>Homepage
        </a>
        <a href="home.php#servizi" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>Applications
        </a>
        <a href="browser-scan.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>Browser Scanner
        </a>
        <a href="VulnerabilityScanner.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>Website Overview
        </a>
        <a href="vpn-checker.php" class="nav-item active">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>VPN Checker
        </a>
        <a href="data-breach-checker.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>Data Breach Monitor
        </a>
    </div>
    <div class="nav-divider"></div>
    <div class="sidebar-bottom">
        <a href="home.php#faq" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span>FAQ
        </a>
	<?php if (isset($_SESSION['user_id'])): ?>
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
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg></span>Settings
        </a>
    </div>
</aside>

<!-- MAIN -->
<main class="main-wrapper" id="main">

    <div class="page-header">
        <a href="home.php" class="page-header-back">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Torna alla Home
        </a>
        <p class="page-header-eyebrow">Scanner di Sicurezza</p>
        <h1 class="page-header-title">VPN Checker</h1>
        <p class="page-header-desc">Verifica se la tua VPN sta proteggendo realmente la tua privacy online. Questo strumento analizza il tuo IP pubblico, rileva connessioni VPN/Proxy/Tor e controlla eventuali leak WebRTC.</p>
    </div>

    <div class="scanner-section">
        <div class="scanner-card">

            <button class="test-button" id="testBtn">
                Avvia Test Sicurezza VPN
            </button>

            <div id="results"></div>

            <div class="info-section">
                <h3>Cosa Viene Verificato</h3>
                <p>Il nostro test di sicurezza VPN controlla diversi aspetti della tua connessione:</p>
                <ul>
                    <li><strong>IP Pubblico e Geolocalizzazione:</strong> Mostra il tuo IP attuale e la posizione rilevata</li>
                    <li><strong>Rilevamento VPN/Proxy/Tor:</strong> Verifica se stai utilizzando servizi di anonimizzazione</li>
                    <li><strong>Test WebRTC Leak:</strong> Controlla se il tuo browser sta esponendo il tuo IP reale</li>
                    <li><strong>Informazioni ISP:</strong> Mostra il provider di servizi internet e l'organizzazione</li>
                </ul>
                <p style="margin-top:1rem">
                    <strong>Privacy:</strong> Tutti i test vengono eseguiti localmente nel tuo browser.
                    Non salviamo né registriamo i tuoi dati di connessione.
                </p>
            </div>

        </div>
    </div>

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
            </div>
            <div class="footer-col">
                <h4>Contatti</h4>
                <p>Email: support@patchpulse.org</p>
                <a href="https://github.com/MrTcStudios/PatchPulse" target="_blank">GitHub MrTcStudios/PatchPulse</a>
            </div>
            <div class="footer-col">
                <h4>Risorse</h4>
                <a href="account">Area Account</a>
                <a href="home.php#faq">FAQ</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 PatchPulse. Tutti i diritti riservati.
                <a href="policy/privacypolicy.php" target="_blank">Privacy Policy</a> |
                <a href="policy/termscondition.php" target="_blank">Terms of Service</a> |
           	<a href="policy/security-policy.php" target="_blank">Security Policy</a>
	    </p>
        </div>
    </footer>

</main>

<script src="script.js"></script>
</body>
</html>
