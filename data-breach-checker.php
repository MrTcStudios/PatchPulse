<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Breach Checker - PatchPulse</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>.cf-turnstile iframe{max-width:100%!important}@media(max-width:400px){.cf-turnstile{transform:scale(0.85);transform-origin:left top;margin-bottom:-8px}}</style>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
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
            <button class="bell-btn" title="Notifiche">
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
        <a href="vpn-checker.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>VPN Checker
        </a>
        <a href="data-breach-checker.php" class="nav-item active">
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
<div class="main-wrapper">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <a href="home.php" class="page-header-back">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Torna alla Home
        </a>
        <div class="page-header-eyebrow">Security Tool</div>
        <h1 class="page-header-title">Data Breach Monitor</h1>
        <p class="page-header-desc">Verifica se la tua email è stata coinvolta in violazioni di dati conosciute.</p>
    </div>

    <!-- SCANNER -->
    <div class="scanner-section">
        <div class="scanner-card">

            <form id="checkForm">
                <div class="input-group">
                    <input type="email" id="emailInput" placeholder="Inserisci la tua email..." required>
                    <div class="cf-turnstile" data-sitekey="0x4AAAAAACxRHR_H4N6K4-b5" data-theme="light" style="margin-top:12px"></div>
                </div>
                <button type="submit" class="check-button" id="checkButton">Controlla Email</button>
            </form>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <span>Controllo in corso...</span>
            </div>

            <div id="results" class="results"></div>

        </div>

        <!-- Info section -->
        <div class="info-section">
            <h3>🛡️ Come funziona</h3>
            <p>Il Data Breach Monitor controlla la tua email nei database pubblici di violazioni di dati segnalate. Se la tua email compare, ti mostreremo da quali servizi provengono i dati e quali informazioni sono state esposte.</p>
            <p>I dati vengono consultati in tempo reale tramite API sicure. La tua email non viene salvata né condivisa.</p>
            <ul>
                <li>Controlla migliaia di violazioni note</li>
                <li>Mostra i dati compromessi specifici</li>
                <li>Fornisce raccomandazioni immediate</li>
            </ul>
        </div>
    </div>

    <!-- FOOTER -->
    <footer>
        <div class="footer-grid">
            <div class="footer-col">
                <h4>PatchPulse</h4>
                <p>Scanner di sicurezza gratuiti per migliorare la tua sicurezza online.</p>
            </div>
            <div class="footer-col">
                <h4>Scanner</h4>
                <a href="browser-scan.php">Browser Scanner</a>
                <a href="VulnerabilityScanner.php">Website Overview</a>
                <a href="vpn-checker.php">VPN Checker</a>
                <a href="data-breach-checker.php">Data Breach Monitor</a>
            </div>
            <div class="footer-col">
                <h4>Contatti</h4>
                <p>Email: support@patchpulse.org</p>
                <a href="https://github.com/MrTcStudios/PatchPulse" target="_blank">GitHub</a>
            </div>
            <div class="footer-col">
                <h4>Risorse</h4>
                <a href="home.php#account">Area Account</a>
                <a href="home.php#about">Documentazione</a>
                <a href="home.php#faq">FAQ</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 PatchPulse. Tutti i diritti riservati. |
                <a href="policy/privacy_policy.php">Privacy Policy</a> |
                <a href="policy/terms&condition.php">Terms of Service</a> |
            	<a href="policy/security-policy.php" target="_blank">Security Policy</a>
	    </p>
        </div>
    </footer>

</div>

<script>
	function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; } 

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

        document.getElementById('checkForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const email = document.getElementById('emailInput').value.trim();
            const resultsDiv = document.getElementById('results');
            const loadingDiv = document.getElementById('loading');
            const checkButton = document.getElementById('checkButton');

            resultsDiv.style.display = 'none';
            resultsDiv.className = 'results';
            loadingDiv.style.display = 'flex';
            checkButton.disabled = true;
            checkButton.textContent = 'Controllo...';

            try {
                const turnstileToken = document.querySelector('[name="cf-turnstile-response"]')?.value || '';
                const response = await fetch(`proxy/proxy-data-breach-checker.php?email=${encodeURIComponent(email)}&cf-turnstile-response=${encodeURIComponent(turnstileToken)}`);

                if (!response.ok) {
                    throw new Error(`Errore HTTP: ${response.status}`);
                }

                const data = await response.json();

                loadingDiv.style.display = 'none';
                resultsDiv.style.display = 'block';

                if (data.success === false && data.error === "Not found") {
                    resultsDiv.className = 'results safe';
                    resultsDiv.innerHTML = `
                        <div class="breach-count">✅ Email Sicura</div>
                        <p>La tua email non è stata trovata in nessuna violazione di dati conosciuta. Continua a mantenere buone pratiche di sicurezza!</p>
                    `;
                } else if (data.success === true && data.found > 0) {
                    resultsDiv.className = 'results danger';

                    let html = `
                        <div class="breach-count">
                            <span>⚠️</span>
                            ${data.found} Violazioni Trovate
                        </div>
                        <p>La tua email è stata trovata nelle seguenti violazioni di dati:</p>
                    `;

                    data.sources.forEach(source => {
                        html += `
                            <div class="breach-item">
				<div class="breach-name">${esc(source.name)}</div>
				<div class="breach-date">Data: ${esc(source.date)}</div>
                            </div>
                        `;
                    });

                    if (data.fields && data.fields.length > 0) {
                        html += `<div class="fields-section"><div class="fields-title">Dati potenzialmente compromessi:</div><div class="fields-list">`;

                        data.fields.forEach(field => {
                            const fieldTranslations = {
                                'username': 'Nome utente', 'password': 'Password', 'email': 'Email',
                                'phone': 'Telefono', 'address': 'Indirizzo', 'first_name': 'Nome',
                                'last_name': 'Cognome', 'city': 'Città', 'country': 'Paese',
                                'zip': 'CAP', 'location': 'Posizione', 'province': 'Provincia', 'name': 'Nome completo'
                            };
                            const translatedField = fieldTranslations[field] || field;
                            html += `<span class="field-tag">${translatedField}</span>`;
                        });

                        html += `</div></div>`;
                    }

                    html += `
                        <div class="recommendations">
                            <strong>Raccomandazioni:</strong>
                            • Cambia immediatamente le password degli account compromessi<br>
                            • Attiva l'autenticazione a due fattori dove possibile<br>
                            • Monitora i tuoi account per attività sospette
                        </div>
                    `;

                    resultsDiv.innerHTML = html;
                } else {
                    throw new Error('Risposta API non valida');
                }

            } catch (error) {
                console.error('Errore:', error);
                loadingDiv.style.display = 'none';
                resultsDiv.style.display = 'block';
                resultsDiv.className = 'results error';
                resultsDiv.innerHTML = `
                    <div class="breach-count">❌ Errore</div>
                    <p>Si è verificato un errore durante il controllo. Riprova più tardi.</p>
		    <small>Errore: ${esc(error.message)}</small>
                `;
            } finally {
                checkButton.disabled = false;
                checkButton.textContent = 'Controlla Email';
                if (typeof turnstile !== 'undefined') turnstile.reset();
            }
        });

        
</script>
</body>
</html>
