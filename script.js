/**
 * PatchPulse — script unificato di tutto il sito.
 *
 * Tutti gli script inline che erano sparsi nei .php sono stati spostati qui
 * in modo da poter rimuovere `'unsafe-inline'` dalla `script-src` della CSP.
 *
 * Pattern: ogni sezione è racchiusa in un IIFE e gated da un check di esistenza
 * dell'elemento principale; se non c'è, la sezione non fa nulla. Questo permette
 * di avere un solo file caricato in tutte le pagine senza interferenze.
 */
(function () {
    'use strict';

    // ----------------------------------------------------------------------
    // Helper
    // ----------------------------------------------------------------------
    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
    const escHtml = (s) => {
        const d = document.createElement('div');
        d.textContent = (s == null) ? '' : String(s);
        return d.innerHTML;
    };

    document.addEventListener('DOMContentLoaded', init);

    function init() {
        initNavMobile();
        initFaqAccordionLegacy();
        initPasswordToggleLegacy();
        initTermsOverlay();
        initHomePage();
        initLogReg();
        initBrowserScan();
        initDataBreach();
        initVpnChecker();
        initVulnerabilityScanner();
        initVulnerabilitiesInfo();
        initConfirmButtons();
    }

    // ======================================================================
    // 1) Navigazione mobile (hamburger) — comune a praticamente ogni pagina
    // ======================================================================
    function initNavMobile() {
        const hamburger = document.getElementById('hamburger');
        const sidebar   = document.getElementById('sidebar');
        if (!hamburger || !sidebar) return;

        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            sidebar.classList.toggle('open');
        });

        $$('.nav-item').forEach(item => {
            item.addEventListener('click', () => {
                hamburger.classList.remove('active');
                sidebar.classList.remove('open');
            });
        });
    }

    // ======================================================================
    // 2) FAQ accordion legacy (non rimossa per compatibilità con vecchi template)
    // ======================================================================
    function initFaqAccordionLegacy() {
        const faqButtons  = $$('.faqZone .faqButton');
        const plusSymbols = $$('.plusSym');
        if (faqButtons.length === 0) return;

        faqButtons.forEach((button, index) => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                $$('.faq-answer').forEach(a => a.classList.remove('active'));
                faqButtons.forEach(btn => btn.classList.remove('active'));
                plusSymbols.forEach(s => s.classList.remove('active'));

                const target = document.getElementById(this.getAttribute('data-faq'));
                if (target) target.classList.add('active');
                this.classList.add('active');
                if (plusSymbols[index]) plusSymbols[index].classList.add('active');
            });
        });
    }

    // ======================================================================
    // 3) Password toggle legacy + Terms overlay (log-reg.php vecchi id)
    // ======================================================================
    function initPasswordToggleLegacy() {
        const togglePassword = document.getElementById('togglePassword');
        const passwordField  = document.getElementById('passwordField');
        if (!togglePassword || !passwordField) return;

        togglePassword.addEventListener('click', function () {
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;
            this.textContent = type === 'password' ? '👁' : '👁‍🗨';
        });
    }

    function initTermsOverlay() {
        const termsLink   = document.getElementById('termsLink');
        const overlay     = document.getElementById('termsOverlay');
        const closeButton = document.getElementById('closeOverlay');
        if (!termsLink || !overlay || !closeButton) return;

        termsLink.addEventListener('click', (e) => { e.preventDefault(); overlay.style.display = 'flex'; });
        closeButton.addEventListener('click', () => { overlay.style.display = 'none'; });
        overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.style.display = 'none'; });
    }

    // ======================================================================
    // 4) home.php — scroll-spy nav, smooth scroll, FAQ, counter, fade-in
    // ======================================================================
    function initHomePage() {
        const mainEl = document.getElementById('main');
        if (!mainEl) return;

        // Active nav link on scroll
        const sections = $$('section[id], footer[id]');
        const navItems = $$('.nav-item[data-section]');
        if (sections.length > 0 && navItems.length > 0) {
            mainEl.addEventListener('scroll', () => {
                let current = '';
                sections.forEach(sec => {
                    if (mainEl.scrollTop >= sec.offsetTop - 120) current = sec.id;
                });
                navItems.forEach(item => {
                    item.classList.toggle('active', item.dataset.section === current);
                });
            });
        }

        // Smooth scroll per anchor links
        $$('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (!href || href === '#') return;
                const target = document.querySelector(href);
                if (!target) return;
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        // FAQ toggle (versione home.php — diversa da quella legacy)
        $$('.faq-question').forEach(q => {
            q.addEventListener('click', () => {
                const answer = q.nextElementSibling;
                const toggle = q.querySelector('.faq-toggle');
                const isOpen = answer && answer.style.display === 'block';

                $$('.faq-answer').forEach(a => a.style.display = 'none');
                $$('.faq-toggle').forEach(t => {
                    t.textContent = '+';
                    t.style.transform = 'rotate(0deg)';
                });

                if (!isOpen && answer) {
                    answer.style.display = 'block';
                    if (toggle) {
                        toggle.textContent = '−';
                        toggle.style.transform = 'rotate(180deg)';
                    }
                }
            });
        });

        // Counter animation per stats
        const animateCounter = (element, target) => {
            let current = 0;
            const increment = target / 80;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                const txt = element.dataset.suffix || '';
                if (txt === '%')        element.textContent = current.toFixed(1) + '%';
                else if (txt === '/7')  element.textContent = '24/7';
                else                    element.textContent = Math.floor(current) + txt;
            }, 16);
        };

        const statsBar = document.querySelector('.stats-bar');
        if (statsBar && typeof IntersectionObserver !== 'undefined') {
            const statsObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) return;
                    entry.target.querySelectorAll('.stat-number').forEach(stat => {
                        const text = stat.textContent.trim();
                        if (text.includes('500'))      { stat.dataset.suffix = '+'; animateCounter(stat, 500); }
                        else if (text.includes('99.9')){ stat.dataset.suffix = '%'; animateCounter(stat, 99.9); }
                        else if (text.includes('24'))  { stat.dataset.suffix = '/7'; animateCounter(stat, 24); }
                        else if (text === '4')         { stat.dataset.suffix = '';  animateCounter(stat, 4); }
                    });
                    statsObserver.unobserve(entry.target);
                });
            }, { root: mainEl, threshold: 0.4 });
            statsObserver.observe(statsBar);
        }

        // Fade-in on scroll
        const fadeTargets = $$('.tool-card, .faq-item, .about-box, .account-card');
        if (fadeTargets.length > 0 && typeof IntersectionObserver !== 'undefined') {
            const fadeObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { root: mainEl, threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

            fadeTargets.forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(24px)';
                el.style.transition = 'opacity 0.55s cubic-bezier(.16,1,.3,1), transform 0.55s cubic-bezier(.16,1,.3,1)';
                fadeObserver.observe(el);
            });
        }
    }

    // ======================================================================
    // 5) log-reg.php — login/register/forgot toggle + password validation
    // ======================================================================
    function initLogReg() {
        const loginForm    = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        if (!loginForm || !registerForm) return;

        const forgotForm   = document.getElementById('forgot-form');
        const formTitle    = document.getElementById('form-title');
        const formSubtitle = document.getElementById('form-subtitle');
        const toggleText   = document.getElementById('toggle-text');
        const toggleBtn    = document.getElementById('toggle-btn');

        let isLoginForm = true;

        function toggleForm() {
            if (forgotForm) forgotForm.classList.remove('active');
            if (isLoginForm) {
                loginForm.classList.remove('active');
                registerForm.classList.add('active');
                if (formTitle)    formTitle.textContent    = 'Registrati';
                if (formSubtitle) formSubtitle.textContent = 'Crea il tuo account PatchPulse';
                if (toggleText)   toggleText.textContent   = 'Hai già un account?';
                if (toggleBtn)    toggleBtn.textContent    = 'Accedi qui';
                isLoginForm = false;
            } else {
                registerForm.classList.remove('active');
                loginForm.classList.add('active');
                if (formTitle)    formTitle.textContent    = 'Accedi';
                if (formSubtitle) formSubtitle.textContent = 'Benvenuto di nuovo su PatchPulse';
                if (toggleText)   toggleText.textContent   = 'Non hai un account?';
                if (toggleBtn)    toggleBtn.textContent    = 'Registrati qui';
                isLoginForm = true;
            }
        }

        function showForgotPassword() {
            if (!forgotForm) return;
            loginForm.classList.remove('active');
            registerForm.classList.remove('active');
            forgotForm.classList.add('active');

            if (formTitle)    formTitle.textContent    = 'Recupera Password';
            if (formSubtitle) formSubtitle.textContent = 'Inserisci la tua email per ricevere il link di reset';
            if (toggleText)   toggleText.textContent   = 'Ricordi la password?';
            if (toggleBtn) {
                toggleBtn.textContent = 'Torna al login';
                toggleBtn.onclick = function () {
                    forgotForm.classList.remove('active');
                    loginForm.classList.add('active');
                    if (formTitle)    formTitle.textContent    = 'Accedi';
                    if (formSubtitle) formSubtitle.textContent = 'Benvenuto di nuovo su PatchPulse';
                    if (toggleText)   toggleText.textContent   = 'Non hai un account?';
                    if (toggleBtn)    toggleBtn.textContent    = 'Registrati qui';
                    toggleBtn.onclick = toggleForm;
                    isLoginForm = true;
                };
            }
        }

        function togglePasswordField(fieldId) {
            const field = document.getElementById(fieldId);
            if (!field) return;
            const button = field.nextElementSibling;
            if (field.type === 'password') {
                field.type = 'text';
                if (button) button.textContent = '🙈';
            } else {
                field.type = 'password';
                if (button) button.textContent = '👁️';
            }
        }

        // Bind toggle/forgot buttons (precedentemente onclick inline)
        if (toggleBtn) toggleBtn.addEventListener('click', toggleForm);
        $$('[data-action="show-forgot"]').forEach(el => {
            el.addEventListener('click', (e) => { e.preventDefault(); showForgotPassword(); });
        });
        $$('[data-toggle-password]').forEach(el => {
            el.addEventListener('click', () => togglePasswordField(el.dataset.togglePassword));
        });

        // Validazione registrazione
        registerForm.addEventListener('submit', function (e) {
            const password        = document.getElementById('PasswordOfUserUnCryptReg');
            const confirmPassword = document.getElementById('confirm-password');
            if (!password || !confirmPassword) return;
            const pw  = password.value;
            const cpw = confirmPassword.value;
            if (pw !== cpw) { e.preventDefault(); alert('Le password non coincidono!'); return false; }
            if (pw.length < 8) { e.preventDefault(); alert('La password deve avere almeno 8 caratteri.'); return false; }
            if (!/[A-Z]/.test(pw) || !/[a-z]/.test(pw) || !/[0-9]/.test(pw)) {
                e.preventDefault();
                alert('La password deve contenere almeno una maiuscola, una minuscola e un numero.');
                return false;
            }
        });

        // Pre-selezione registrazione via URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('action') === 'register' || window.location.hash === '#register') {
            toggleForm();
        }
    }

    // ======================================================================
    // 6) browser-scan.php — tab switching + observer + saveScans
    // ======================================================================
    function initBrowserScan() {
        const zones = $$('.scanResultZone');
        if (zones.length === 0) return;

        function animateZone(zone) {
            zone.querySelectorAll('.scan-item').forEach((el, i) => {
                el.classList.remove('visible');
                setTimeout(() => el.classList.add('visible'), i * 40);
            });
        }

        $$('[data-scan]').forEach(btn => {
            btn.addEventListener('click', function () {
                const targetId = this.dataset.scan;
                const target = document.getElementById(targetId);
                if (!target) return;

                $$('.scanResultZone').forEach(z => z.classList.remove('active'));
                $$('[data-scan]').forEach(b => b.classList.remove('active'));

                target.classList.add('active');
                this.classList.add('active');
                animateZone(target);
            });
        });

        window.addEventListener('load', () => {
            const active = document.querySelector('.scanResultZone.active');
            if (active) animateZone(active);
        });

        // Osservatore: rimuove la classe `loading` quando un modulo JS scrive il valore.
        // IMPORTANTE: fastScan.js gira PRIMA di DOMContentLoaded (è un module defer),
        // quindi alcuni valori sono già stati scritti prima che l'observer venga
        // registrato. Per questo facciamo anche uno sweep iniziale subito DOPO la
        // registrazione, e un secondo sweep dopo `load` per coprire i moduli async.
        if (typeof MutationObserver !== 'undefined') {
            const sweep = () => {
                $$('.scan-item-value').forEach(el => {
                    const txt = (el.textContent || '').trim();
                    if (txt && txt !== 'Loading...') el.classList.remove('loading');
                });
            };
            const observer = new MutationObserver(mutations => {
                mutations.forEach(m => {
                    const el = m.target.classList && m.target.classList.contains('scan-item-value')
                        ? m.target
                        : m.target.parentElement;
                    if (el && el.classList && el.classList.contains('scan-item-value')) {
                        const txt = (el.textContent || '').trim();
                        if (txt && txt !== 'Loading...') el.classList.remove('loading');
                    }
                });
            });
            $$('.scan-item-value').forEach(el => {
                observer.observe(el, { childList: true, subtree: true, characterData: true });
            });
            // Sweep immediato (cattura i valori già scritti da fastScan.js prima del DOMContentLoaded)
            sweep();
            // Sweep finale dopo `load` (cattura i valori dei moduli async — geo/incognito/securityProtocols)
            window.addEventListener('load', () => setTimeout(sweep, 100));
        }

        // Save All Scans
        const saveBtn = document.getElementById('saveScansButton');
        if (saveBtn) saveBtn.addEventListener('click', saveScans);

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
                let text = el.innerText && el.innerText.trim();
                if (text && text !== 'Loading...') return text;
                const zone = el.closest('.scanResultZone');
                if (!zone || zone.classList.contains('active')) return text;
                const prev = zone.style.transition;
                zone.style.transition = 'none';
                zone.classList.add('active');
                await new Promise(r => setTimeout(r, 200));
                text = el.innerText && el.innerText.trim();
                zone.classList.remove('active');
                zone.style.transition = prev;
                return text;
            }

            const btn = document.getElementById('saveScansButton');
            if (btn) { btn.disabled = true; btn.textContent = 'Salvataggio...'; }

            const scanData = {};
            for (const id of ids) {
                const el = document.getElementById(id);
                let value = el ? (el.innerText && el.innerText.trim()) : '';
                if (!value || value === 'Loading...') value = await tryRevealAndRead(el);
                scanData[id] = value;
            }
            const w = document.getElementById('width');
            const h = document.getElementById('height');
            const ws = w ? (w.innerText && w.innerText.trim()) : '';
            const hs = h ? (h.innerText && h.innerText.trim()) : '';
            scanData['screenResolution'] = (ws && hs) ? (ws + ' x ' + hs) : '';

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
                    if (btn) {
                        btn.textContent = '✓ Salvato';
                        setTimeout(() => {
                            btn.disabled = false;
                            btn.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Salva tutte le scansioni';
                        }, 2000);
                    }
                } else {
                    console.error('Save failed:', text);
                    if (btn) { btn.disabled = false; btn.textContent = 'Errore: ' + text.substring(0, 50); }
                }
            } catch (err) {
                console.error('Save error:', err);
                if (btn) { btn.disabled = false; btn.textContent = 'Errore di connessione'; }
            }
        }
    }

    // ======================================================================
    // 7) data-breach-checker.php — form di controllo
    // ======================================================================
    function initDataBreach() {
        const form = document.getElementById('checkForm');
        if (!form) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const emailInput  = document.getElementById('emailInput');
            const resultsDiv  = document.getElementById('results');
            const loadingDiv  = document.getElementById('loading');
            const checkButton = document.getElementById('checkButton');
            if (!emailInput || !resultsDiv || !loadingDiv || !checkButton) return;

            const email = emailInput.value.trim();

            resultsDiv.style.display = 'none';
            resultsDiv.className = 'results';
            loadingDiv.style.display = 'flex';
            checkButton.disabled = true;
            checkButton.textContent = 'Controllo...';

            try {
                const ts = document.querySelector('[name="cf-turnstile-response"]');
                const turnstileToken = ts ? ts.value : '';
                const response = await fetch(
                    'proxy/proxy-data-breach-checker.php?email=' + encodeURIComponent(email) +
                    '&cf-turnstile-response=' + encodeURIComponent(turnstileToken)
                );
                if (!response.ok) throw new Error('Errore HTTP: ' + response.status);

                const data = await response.json();
                loadingDiv.style.display = 'none';
                resultsDiv.style.display = 'block';

                if (data.success === false && data.error === 'Not found') {
                    resultsDiv.className = 'results safe';
                    resultsDiv.innerHTML =
                        '<div class="breach-count">✅ Email Sicura</div>' +
                        '<p>La tua email non è stata trovata in nessuna violazione di dati conosciuta. ' +
                        'Continua a mantenere buone pratiche di sicurezza!</p>';
                } else if (data.success === true && data.found > 0) {
                    resultsDiv.className = 'results danger';

                    let html = '<div class="breach-count"><span>⚠️</span> ' + data.found +
                               ' Violazioni Trovate</div><p>La tua email è stata trovata nelle seguenti violazioni di dati:</p>';

                    (data.sources || []).forEach(source => {
                        html += '<div class="breach-item">' +
                                '<div class="breach-name">' + escHtml(source.name) + '</div>' +
                                '<div class="breach-date">Data: ' + escHtml(source.date) + '</div>' +
                                '</div>';
                    });

                    if (data.fields && data.fields.length > 0) {
                        html += '<div class="fields-section"><div class="fields-title">Dati potenzialmente compromessi:</div><div class="fields-list">';
                        const fieldTranslations = {
                            'username': 'Nome utente', 'password': 'Password', 'email': 'Email',
                            'phone': 'Telefono', 'address': 'Indirizzo', 'first_name': 'Nome',
                            'last_name': 'Cognome', 'city': 'Città', 'country': 'Paese',
                            'zip': 'CAP', 'location': 'Posizione', 'province': 'Provincia', 'name': 'Nome completo'
                        };
                        data.fields.forEach(field => {
                            const translated = fieldTranslations[field] || field;
                            html += '<span class="field-tag">' + escHtml(translated) + '</span>';
                        });
                        html += '</div></div>';
                    }

                    html += '<div class="recommendations"><strong>Raccomandazioni:</strong>' +
                            ' • Cambia immediatamente le password degli account compromessi<br>' +
                            ' • Attiva l\'autenticazione a due fattori dove possibile<br>' +
                            ' • Monitora i tuoi account per attività sospette</div>';

                    resultsDiv.innerHTML = html;
                } else {
                    throw new Error('Risposta API non valida');
                }
            } catch (error) {
                console.error('Errore:', error);
                loadingDiv.style.display = 'none';
                resultsDiv.style.display = 'block';
                resultsDiv.className = 'results error';
                resultsDiv.innerHTML =
                    '<div class="breach-count">❌ Errore</div>' +
                    '<p>Si è verificato un errore durante il controllo. Riprova più tardi.</p>' +
                    '<small>Errore: ' + escHtml(error.message) + '</small>';
            } finally {
                checkButton.disabled = false;
                checkButton.textContent = 'Controlla Email';
                if (typeof turnstile !== 'undefined') turnstile.reset();
            }
        });
    }

    // ======================================================================
    // 8) vpn-checker.php — runVPNCheck + WebRTC leak detection
    // ======================================================================
    function initVpnChecker() {
        const testBtn = document.getElementById('testBtn');
        if (!testBtn) return;

        testBtn.addEventListener('click', runVPNCheck);

        async function runVPNCheck() {
            const resultsDiv = document.getElementById('results');
            if (!resultsDiv) return;

            testBtn.disabled = true;
            testBtn.textContent = '⏳ Test in corso...';
            resultsDiv.innerHTML = '<div class="loading">Analisi della connessione in corso</div>';

            let ipData = {}, rtcLeakIPs = [];

            try {
                const res = await fetch('./API/vpncheck.php');
                if (!res.ok) throw new Error('Errore nella chiamata API VPN');

                ipData = await res.json();
                if (ipData.error) throw new Error(ipData.error);

                ipData = {
                    ip: (ipData && ipData.ip) || 'Non disponibile',
                    location: (ipData && ipData.location) || {},
                    network:  (ipData && ipData.network)  || {},
                    security: (ipData && ipData.security) || {}
                };

                rtcLeakIPs = await getWebRTCIPs();
            } catch (e) {
                resultsDiv.innerHTML =
                    '<div class="result"><p class="danger">❌ Errore durante il test: ' + escHtml(e.message) + '</p>' +
                    '<p style="margin-top: 1rem; color: #888; font-size: 0.9rem;">Verifica la tua connessione internet e riprova</p></div>';
                testBtn.disabled = false;
                testBtn.textContent = '🔍 Riprova Test';
                return;
            }

            const location = ipData.location || {};
            const network  = ipData.network  || {};
            const security = ipData.security || {};

            const cityText    = (location.city    && location.city.trim()    !== '') ? location.city    : 'Non disponibile';
            const countryText = (location.country && location.country.trim() !== '') ? location.country : 'Non disponibile';
            const asnText = (network.autonomous_system_number != null && String(network.autonomous_system_number).trim() !== '')
                ? network.autonomous_system_number : 'N/A';
            const ispText = (network.autonomous_system_organization && network.autonomous_system_organization.trim() !== '')
                ? network.autonomous_system_organization : 'Non disponibile';

            let html = '<h2 style="color:#00ff88;margin-bottom:2rem;text-align:center;">📊 Risultati Analisi</h2>';

            html += '<div class="result">' +
                '<strong>🌍 Informazioni IP Pubblico</strong><br><br>' +
                '<strong>IP Pubblico:</strong> <code>' + escHtml(ipData.ip) + '</code><br>' +
                '<strong>Posizione:</strong> ' + escHtml(cityText) + ', ' + escHtml(countryText) + '<br>' +
                '<strong>ISP / ASN:</strong> ' + escHtml(asnText) + ' - ' + escHtml(ispText) +
                '</div>';

            const vpnStatus = getVPNStatus(security);
            html += '<div class="result">' +
                '<strong>🔐 Controllo VPN / Proxy / Tor</strong><br><br>' +
                (security.vpn   ? '✅ <span class="secure">Connessione VPN rilevata</span><br>' : '❌ <span class="warning">Nessuna VPN rilevata</span><br>') +
                (security.proxy ? '✅ <span class="secure">Proxy rilevato</span><br>'        : '❌ <span class="warning">Nessun proxy rilevato</span><br>') +
                (security.tor   ? '✅ <span class="secure">Rete Tor rilevata</span><br>'      : '❌ <span class="warning">Nessuna rete Tor rilevata</span><br>') +
                (security.relay ? '✅ <span class="secure">Relay attivo</span><br>' : '') +
                '<br><div style="padding:1rem;background:rgba(0,0,0,0.3);border-radius:10px;margin-top:1rem;">' +
                '<strong>Stato Generale:</strong> <span class="' + vpnStatus.class + '">' + vpnStatus.message + '</span></div>' +
                '</div>';

            const webRTCStatus  = analyzeWebRTCLeak(rtcLeakIPs, ipData.ip);
            const webRTCMessage = getWebRTCMessage(rtcLeakIPs, ipData.ip);

            html += '<div class="result">' +
                '<strong>🌐 Test WebRTC Leak</strong><br><br>' +
                (rtcLeakIPs.length > 0
                    ? '<strong>Indirizzi IP rilevati:</strong> ' + rtcLeakIPs.map(ip => '<code>' + escHtml(ip) + '</code>').join(', ') + '<br><br>'
                    : '<strong>Indirizzi IP rilevati:</strong> Nessuno<br><br>') +
                '<div style="padding:1rem;background:rgba(0,0,0,0.3);border-radius:10px;">' +
                '<span class="' + webRTCStatus + '">' + webRTCMessage + '</span></div>' +
                '</div>';

            const overall = getOverallSecurityStatus(security, webRTCStatus);
            html += '<div class="result" style="border:2px solid ' + overall.color + ';background:' + overall.bg + ';">' +
                '<strong>🛡️ Valutazione Complessiva della Sicurezza</strong><br><br>' +
                '<div style="font-size:1.2rem;"><span class="' + overall.class + '">' + overall.icon + ' ' + overall.title + '</span></div>' +
                '<p style="margin-top:1rem;color:#ccc;">' + overall.description + '</p>' +
                (overall.recommendations
                    ? '<div style="margin-top:1rem;padding:1rem;background:rgba(0,0,0,0.2);border-radius:8px;"><strong>Raccomandazioni:</strong><br>' + overall.recommendations + '</div>'
                    : '') +
                '</div>';

            resultsDiv.innerHTML = html;

            testBtn.disabled = false;
            testBtn.textContent = '🔄 Ripeti Test';
        }

        function getVPNStatus(security) {
            if (security.vpn || security.proxy || security.tor) {
                return { class: 'secure',  message: '🛡️ Connessione protetta rilevata' };
            }
            return { class: 'warning', message: '⚠️ Nessuna protezione rilevata - IP esposto' };
        }

        function getOverallSecurityStatus(security, webRTCStatus) {
            const hasVPN = security.vpn || security.proxy || security.tor;
            const webRTCSecure = webRTCStatus === 'secure';

            if (hasVPN && webRTCSecure) {
                return {
                    class: 'secure', color: '#00ff88', bg: 'rgba(0, 255, 136, 0.1)',
                    icon: '✅', title: 'PROTEZIONE OTTIMALE',
                    description: 'La tua connessione è ben protetta. VPN/Proxy attivo e nessun leak WebRTC rilevato.'
                };
            } else if (hasVPN && webRTCStatus === 'warning') {
                return {
                    class: 'warning', color: '#ffaa00', bg: 'rgba(255, 170, 0, 0.1)',
                    icon: '⚠️', title: 'PROTEZIONE BUONA',
                    description: 'VPN/Proxy attivo ma WebRTC potrebbe essere bloccato o non funzionante.',
                    recommendations: '• Verifica le impostazioni WebRTC nel browser<br>• Considera l\'uso di estensioni per bloccare WebRTC'
                };
            } else if (hasVPN && webRTCStatus === 'danger') {
                return {
                    class: 'danger', color: '#ff4444', bg: 'rgba(255, 68, 68, 0.1)',
                    icon: '❌', title: 'LEAK RILEVATO',
                    description: 'VPN/Proxy attivo ma WebRTC sta esponendo il tuo IP reale!',
                    recommendations: '• Disabilita WebRTC nel browser immediatamente<br>• Usa estensioni come "WebRTC Leak Prevent"<br>• Verifica le impostazioni della tua VPN'
                };
            } else if (!hasVPN && webRTCSecure) {
                return {
                    class: 'warning', color: '#ffaa00', bg: 'rgba(255, 170, 0, 0.1)',
                    icon: '⚠️', title: 'PROTEZIONE PARZIALE',
                    description: 'WebRTC sicuro ma non stai usando VPN/Proxy. Il tuo IP è comunque esposto.',
                    recommendations: '• Attiva una VPN per protezione completa<br>• WebRTC è già sicuro, mantienilo così'
                };
            } else {
                return {
                    class: 'danger', color: '#ff4444', bg: 'rgba(255, 68, 68, 0.1)',
                    icon: '🚨', title: 'NESSUNA PROTEZIONE',
                    description: 'Non stai usando VPN/Proxy. Il tuo IP reale è completamente esposto.',
                    recommendations: '• Attiva una VPN affidabile<br>• Considera l\'uso di Tor per maggiore anonimato<br>• Configura un proxy se necessario'
                };
            }
        }

        function getWebRTCIPs() {
            return new Promise(resolve => {
                const RTC = window.RTCPeerConnection || window.webkitRTCPeerConnection || window.mozRTCPeerConnection;
                if (!RTC) { resolve(['BLOCKED']); return; }

                const ips = new Set();
                let resolved = false;

                try {
                    const pc = new RTC({
                        iceServers: [
                            { urls: 'stun:stun.l.google.com:19302' },
                            { urls: 'stun:stun1.l.google.com:19302' }
                        ]
                    });
                    pc.createDataChannel('');

                    pc.onicecandidate = (event) => {
                        if (!event || !event.candidate) {
                            if (!resolved) { resolved = true; pc.close(); resolve([...ips]); }
                            return;
                        }
                        const candidate = event.candidate.candidate;
                        const ipRegex = /([0-9]{1,3}(\.[0-9]{1,3}){3}|[a-f0-9]*:[a-f0-9:]+)/g;
                        const matches = candidate.match(ipRegex);
                        if (matches) {
                            matches.forEach(ip => {
                                if (ip && !ip.startsWith('169.254.') && !ip.startsWith('224.') && ip !== '0.0.0.0') {
                                    ips.add(ip);
                                }
                            });
                        }
                    };

                    pc.onicegatheringstatechange = () => {
                        if (pc.iceGatheringState === 'complete' && !resolved) {
                            resolved = true; pc.close(); resolve([...ips]);
                        }
                    };

                    setTimeout(() => {
                        if (!resolved) { resolved = true; pc.close(); resolve([...ips]); }
                    }, 5000);

                    pc.createOffer()
                        .then(offer => pc.setLocalDescription(offer))
                        .catch(() => {
                            if (!resolved) { resolved = true; pc.close(); resolve([]); }
                        });
                } catch (_) {
                    resolve(['ERROR']);
                }
            });
        }

        function analyzeWebRTCLeak(rtcIPs, publicIP) {
            if (rtcIPs.includes('BLOCKED') || rtcIPs.includes('ERROR')) return 'secure';
            if (rtcIPs.length === 0) return 'secure';
            const publicRTCIPs = rtcIPs.filter(ip =>
                !ip.startsWith('192.168.') && !ip.startsWith('10.') &&
                !ip.startsWith('172.')     && !ip.startsWith('127.')
            );
            if (publicRTCIPs.length === 0) return 'secure';
            if (publicRTCIPs.includes(publicIP) && publicRTCIPs.length === 1) return 'secure';
            return 'danger';
        }

        function getWebRTCMessage(rtcIPs, publicIP) {
            if (rtcIPs.includes('BLOCKED')) return '✅ WebRTC bloccato dalle protezioni del browser - Ottimo per la privacy!';
            if (rtcIPs.includes('ERROR'))   return '⚠️ Impossibile testare WebRTC - Potrebbe essere bloccato o limitato';
            if (rtcIPs.length === 0)        return '✅ WebRTC bloccato - Nessun rischio di leak IP';
            const publicRTCIPs = rtcIPs.filter(ip =>
                !ip.startsWith('192.168.') && !ip.startsWith('10.') &&
                !ip.startsWith('172.')     && !ip.startsWith('127.')
            );
            if (publicRTCIPs.length === 0) return '✅ Solo IP privati rilevati - WebRTC sicuro';
            if (publicRTCIPs.includes(publicIP) && publicRTCIPs.length === 1)
                return '✅ WebRTC mostra solo l\'IP della VPN - Sicuro';
            return '❌ LEAK WebRTC rilevato! Il tuo IP reale potrebbe essere esposto';
        }
    }

    // ======================================================================
    // 9) VulnerabilityScanner.php — verify + scan SSE + render + PDF
    // ======================================================================
    function initVulnerabilityScanner() {
        const checkBtn = document.getElementById('checkDomainBtn');
        if (!checkBtn) return;

        // ── State ──
        let eventSource = null;
        let currentSection = null;
        let lineCount = 0;

        const MODULES = ['nmap','testssl','headers','dnsrecon','extra'];
        const data = {};
        function resetData() {
            data.nmap     = { ports: [], running: false, done: false };
            data.testssl  = { lines: [], vulns: [], running: false, done: false };
            data.headers  = { items: [], cookies: [], server: '', running: false, done: false };
            data.dnsrecon = { records: {}, subdomains: [], running: false, done: false };
            data.extra    = { sections: [], running: false, done: false };
        }
        resetData();

        let targetHost = '';
        let targetIp = '';
        let isCf = false;

        function isNoise(line) {
            if (line.startsWith('[...]')) return true;
            if (line.startsWith('Read data files from')) return true;
            if (line.startsWith('Nmap done:')) return true;
            if (/^\s*$/.test(line)) return true;
            if (line.startsWith('---')) return true;
            if (/^[\s]*[\da-f]{2}:/.test(line)) return true;
            if (line.includes('RTTVAR has grown')) return true;
            if (line.includes('STATUS: Running average')) return true;
            if (line.includes('STATUS: Completed')) return true;
            if (line.match(/^\s+pub:\s*$/)) return true;
            if (line.match(/^\s+0[0-9a-f]:/)) return true;
            return false;
        }

        const csrfMeta  = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.content : '';
        let verifiedUrl = null;

        function showStep(step) {
            ['url','verify','scan'].forEach(s => {
                const el = document.getElementById('step-' + s);
                if (el) el.style.display = (step === s) ? '' : 'none';
            });
        }

        function showError(msg) {
            const el = document.getElementById('scanError');
            if (!el) return;
            el.textContent = msg;
            el.classList.add('active');
        }

        function clearError() {
            const el = document.getElementById('scanError');
            if (!el) return;
            el.textContent = '';
            el.classList.remove('active');
        }

        function validateUrl(val) {
            let url;
            try { url = new URL(val); } catch { return null; }
            const hostname = url.hostname.toLowerCase();
            if (['localhost','127.0.0.1','::1','0.0.0.0'].includes(hostname)
                || hostname.startsWith('192.168.')
                || hostname.startsWith('10.')) return null;
            if (url.protocol !== 'https:') return null;
            return url;
        }

        // copyText: invocato via [data-copy-target]
        $$('[data-copy-target]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.copyTarget;
                const target = document.getElementById(id);
                if (!target) return;
                const text = target.textContent;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(() => {
                        btn.textContent = '✓';
                        setTimeout(() => btn.textContent = '📋', 1500);
                    });
                }
            });
        });

        // togglePanel: invocato via [data-toggle-panel]
        $$('[data-toggle-panel]').forEach(head => {
            head.addEventListener('click', () => {
                const name = head.dataset.togglePanel;
                const bd = document.getElementById('body-' + name);
                if (bd) bd.classList.toggle('open');
            });
        });

        // PDF button
        const pdfBtn = document.getElementById('pdfBtn');
        if (pdfBtn) pdfBtn.addEventListener('click', generatePDF);

        // Step 1: Check domain verification
        checkBtn.addEventListener('click', function () {
            clearError();
            const input = document.getElementById('scanInput');
            if (!input) return;
            const url = validateUrl(input.value);
            if (!url) { showError('Inserisci un URL HTTPS valido (es. https://example.com)'); return; }

            this.disabled = true;
            this.textContent = 'Verifica in corso...';

            fetch('generate_verification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ target: url.href })
            })
            .then(r => r.json())
            .then(resp => {
                this.disabled = false;
                this.textContent = 'Verifica Dominio';

                if (resp.error) { showError(resp.error); return; }

                if (resp.status === 'verified') {
                    verifiedUrl = url;
                    const vd = document.getElementById('verifiedDomain');
                    if (vd) vd.textContent = resp.domain;
                    showStep('scan');
                } else {
                    const tn = document.getElementById('txtName');
                    const tv = document.getElementById('txtValue');
                    if (tn) tn.textContent = resp.txt_name;
                    if (tv) tv.textContent = resp.txt_record;
                    showStep('verify');
                }
            })
            .catch(() => {
                this.disabled = false;
                this.textContent = 'Verifica Dominio';
                showError('Errore di connessione. Riprova.');
            });
        });

        // Step 2: Verify DNS
        const verifyDnsBtn = document.getElementById('verifyDnsBtn');
        if (verifyDnsBtn) {
            verifyDnsBtn.addEventListener('click', function () {
                const txtNameEl = document.getElementById('txtName');
                if (!txtNameEl) return;
                const domain = txtNameEl.textContent.replace('_patchpulse.', '');
                const statusEl = document.getElementById('verifyStatus');

                this.disabled = true;
                this.textContent = 'Controllo DNS...';
                if (statusEl) {
                    statusEl.style.display = 'block';
                    statusEl.className = 'verify-status info';
                    statusEl.textContent = 'Interrogazione DNS in corso...';
                }

                fetch('verify_domain.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify({ domain: domain })
                })
                .then(r => r.json())
                .then(resp => {
                    this.disabled = false;
                    this.textContent = 'Verifica DNS';

                    if (resp.error) {
                        if (statusEl) {
                            statusEl.className = 'verify-status error';
                            statusEl.textContent = resp.error;
                        }
                        return;
                    }
                    if (resp.status === 'verified') {
                        if (statusEl) {
                            statusEl.className = 'verify-status success';
                            statusEl.textContent = 'Dominio verificato con successo!';
                        }
                        const input = document.getElementById('scanInput');
                        if (input) verifiedUrl = validateUrl(input.value);
                        const vd = document.getElementById('verifiedDomain');
                        if (vd) vd.textContent = resp.domain;
                        setTimeout(() => showStep('scan'), 1200);
                    } else if (statusEl) {
                        statusEl.className = 'verify-status error';
                        statusEl.textContent = resp.message || 'Record TXT non trovato. Attendi qualche minuto e riprova.';
                    }
                })
                .catch(() => {
                    this.disabled = false;
                    this.textContent = 'Verifica DNS';
                    if (statusEl) {
                        statusEl.className = 'verify-status error';
                        statusEl.textContent = 'Errore di connessione. Riprova.';
                    }
                });
            });
        }

        const back1 = document.getElementById('backToUrlBtn');
        const back2 = document.getElementById('backToUrlBtn2');
        if (back1) back1.addEventListener('click', () => showStep('url'));
        if (back2) back2.addEventListener('click', () => { verifiedUrl = null; showStep('url'); });

        const consent = document.getElementById('legalConsent');
        const scanBtn = document.getElementById('scanBtn');
        if (consent && scanBtn) {
            consent.addEventListener('change', function () {
                scanBtn.disabled = !this.checked;
            });
        }

        // Step 3: Start scan
        if (scanBtn) {
            scanBtn.addEventListener('click', function () {
                if (!verifiedUrl || (consent && !consent.checked)) return;
                if (eventSource) { eventSource.close(); eventSource = null; }

                targetHost = verifiedUrl.hostname.toLowerCase();
                targetIp = ''; isCf = false; lineCount = 0; currentSection = null;
                resetData();

                const ra = document.getElementById('resultsArea');
                const tb = document.getElementById('targetBar');
                const cb = document.getElementById('consoleBox');
                const lc = document.getElementById('lineCount');
                const sc = document.getElementById('scanCards');
                const ct = document.getElementById('consoleToggle');
                const bm = document.getElementById('busyMessage');
                const ec = document.getElementById('scanErrorCard');
                const pw = document.getElementById('pdfBtnWrap');

                if (ra) ra.classList.add('active');
                if (tb) tb.style.display = 'none';
                if (cb) cb.innerHTML = '';
                if (lc) lc.textContent = '0 righe';
                if (sc) sc.style.display = '';
                if (ct) ct.style.display = '';
                if (bm) bm.style.display = 'none';
                if (ec) ec.style.display = 'none';

                MODULES.forEach(s => {
                    setStatus(s, 'pending', 'IN ATTESA');
                    const bd = document.getElementById('body-' + s);
                    if (bd) {
                        bd.innerHTML = '<span class="waiting">In attesa di avvio...</span>';
                        bd.classList.remove('open');
                    }
                });
                if (sc) sc.style.display = '';
                if (ct) ct.style.display = 'flex';
                if (pw) pw.style.display = 'none';

                this.disabled = true;
                this.textContent = 'Scansione in corso...';

                fetch('start_scan.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify({ target: verifiedUrl.href })
                })
                .then(r => r.json())
                .then(resp => {
                    if (resp.error) { showScanError(resp.error); resetBtn(); return; }
                    startStreaming(resp.scanId);
                })
                .catch(() => { showScanError('Errore di connessione. Riprova.'); resetBtn(); });
            });
        }

        function resetBtn() {
            const btn = document.getElementById('scanBtn');
            const cs  = document.getElementById('legalConsent');
            if (!btn) return;
            btn.disabled = !(cs && cs.checked);
            btn.textContent = 'Avvia Scansione';
        }

        function startStreaming(scanId) {
            eventSource = new EventSource('get_results.php?scanId=' + encodeURIComponent(scanId));
            eventSource.onmessage = function (e) {
                const msg = JSON.parse(e.data);
                if (msg.completed) {
                    finishAll();
                    eventSource.close(); eventSource = null;
                    resetBtn();
                    return;
                }
                if (msg.busy || msg.error) {
                    eventSource.close(); eventSource = null;
                    resetBtn();
                    if (msg.busy) showBusy(); else showScanError(msg.error);
                    return;
                }
                if (msg.line) processLine(msg.line);
            };
            eventSource.onerror = function () {
                addConsole('Connessione interrotta.', true);
                eventSource.close(); eventSource = null;
                resetBtn();
            };
        }

        function showBusy() {
            const area = document.getElementById('resultsArea');
            if (!area) return;
            area.classList.add('active');
            const sc = document.getElementById('scanCards');     if (sc) sc.style.display = 'none';
            const ct = document.getElementById('consoleToggle'); if (ct) ct.style.display = 'none';
            const cw = document.getElementById('consoleWrap');   if (cw) cw.classList.remove('open');
            const tb = document.getElementById('targetBar');     if (tb) tb.style.display = 'none';

            let busyEl = document.getElementById('busyMessage');
            if (!busyEl) {
                busyEl = document.createElement('div');
                busyEl.id = 'busyMessage';
                busyEl.className = 'busy-card';
                area.insertBefore(busyEl, area.firstChild);
            }
            busyEl.style.display = 'block';
            busyEl.innerHTML =
                '<div class="busy-icon">⏳</div>' +
                '<h3>Scanner al completo</h3>' +
                '<p>In questo momento tutte le scansioni sono occupate da altri utenti. Riprova tra qualche minuto.</p>' +
                '<button class="scan-submit" data-scan-close="busy" style="max-width:220px;margin-top:1rem">Chiudi</button>';
        }

        function showScanError(msg) {
            const area = document.getElementById('resultsArea');
            if (!area) return;
            area.classList.add('active');
            const sc = document.getElementById('scanCards');     if (sc) sc.style.display = 'none';
            const ct = document.getElementById('consoleToggle'); if (ct) ct.style.display = 'none';
            const tb = document.getElementById('targetBar');     if (tb) tb.style.display = 'none';

            let errEl = document.getElementById('scanErrorCard');
            if (!errEl) {
                errEl = document.createElement('div');
                errEl.id = 'scanErrorCard';
                errEl.className = 'busy-card error-card';
                area.insertBefore(errEl, area.firstChild);
            }
            errEl.style.display = 'block';
            errEl.innerHTML =
                '<div class="busy-icon">❌</div>' +
                '<h3>Errore</h3>' +
                '<p>' + escHtml(msg) + '</p>' +
                '<button class="scan-submit" data-scan-close="error" style="max-width:220px;margin-top:1rem">Chiudi</button>';
        }

        // Event delegation per i bottoni "Chiudi" generati dinamicamente
        document.addEventListener('click', function (e) {
            const t = e.target;
            if (!(t instanceof HTMLElement)) return;
            if (t.dataset && t.dataset.scanClose) {
                const which = t.dataset.scanClose;
                const id = which === 'busy' ? 'busyMessage' : 'scanErrorCard';
                const el = document.getElementById(id);
                if (el) el.style.display = 'none';
                const sc = document.getElementById('scanCards');     if (sc) sc.style.display = '';
                const ct = document.getElementById('consoleToggle'); if (ct) ct.style.display = '';
                const ra = document.getElementById('resultsArea');   if (ra) ra.classList.remove('active');
            }
        });

        function processLine(line) {
            if (!isNoise(line)) addConsole(line, false);

            if (line.includes('=== NMAP SCAN ==='))    { switchSection('nmap');     return; }
            if (line.includes('=== SSL SCAN ==='))     { switchSection('testssl');  return; }
            if (line.includes('=== HTTP HEADERS ===')) { switchSection('headers');  return; }
            if (line.includes('=== DNS RECON ==='))    { switchSection('dnsrecon'); return; }
            if (line.includes('=== EXTRA ==='))        { switchSection('extra');    return; }

            if (line.startsWith('[INFO]') && line.includes('->')) {
                const m = line.match(/\]\s*(.+?)\s*->\s*([\d.]+)/);
                if (m) { targetIp = m[2]; isCf = line.toLowerCase().includes('cloudflare'); showTargetBar(); }
                return;
            }

            if (currentSection === 'nmap')     parseNmap(line);
            if (currentSection === 'testssl')  parseSsl(line);
            if (currentSection === 'headers')  parseHeaders(line);
            if (currentSection === 'dnsrecon') parseDns(line);
            if (currentSection === 'extra')    parseExtra(line);
        }

        function switchSection(name) {
            if (currentSection && data[currentSection].running) {
                data[currentSection].done = true;
                data[currentSection].running = false;
                renderCard(currentSection);
            }
            currentSection = name;
            data[name].running = true;
            setStatus(name, 'running', 'IN CORSO');
            const bd = document.getElementById('body-' + name);
            if (bd) {
                bd.innerHTML = '<span class="waiting">Analisi in corso...</span>';
                bd.classList.add('open');
            }
        }

        function finishAll() {
            MODULES.forEach(k => {
                if (data[k].running) { data[k].done = true; data[k].running = false; }
                renderCard(k);
            });
            const pw = document.getElementById('pdfBtnWrap');
            if (pw) pw.style.display = 'block';
        }

        function parseNmap(line) {
            const pm = line.match(/(\d+)\/tcp\s+open\s+(\S+)/);
            if (pm) { data.nmap.ports.push({ port: pm[1], service: pm[2] }); renderCard('nmap'); }
        }

        function parseSsl(line) {
            const d = data.testssl;
            const l = line.trim();
            if (!l) return;
            if (l.startsWith('Using "')) return;
            if (l.startsWith('Testing all')) return;
            if (l.startsWith('A record via')) return;
            if (l.startsWith('rDNS ')) return;
            if (l.startsWith('Service detected')) return;
            if (l.startsWith('Hexcode ')) return;
            if (l.startsWith('SSLv2') || l.startsWith('SSLv3') || l.startsWith('TLSv1') || l.startsWith('TLSv1.1')) {
                if (l === 'SSLv2' || l === 'SSLv3' || l === 'TLSv1' || l === 'TLSv1.1') return;
            }
            if (l === '-') return;
            if (l.includes('VULNERABLE') || l.includes('NOT ok')) d.vulns.push(l);
            d.lines.push(l);
            renderCard('testssl');
        }

        function parseHeaders(line) {
            const d = data.headers;
            if (line.startsWith('HDR_OK:'))     { d.items.push({ ok: true,  text: line.substring(7).trim() });               renderCard('headers'); }
            if (line.startsWith('HDR_MISS:'))   { d.items.push({ ok: false, text: line.substring(9).trim() });               renderCard('headers'); }
            if (line.startsWith('HDR_WARN:'))   { d.items.push({ ok: false, text: line.substring(9).trim(), warn: true });   renderCard('headers'); }
            if (line.startsWith('COOKIE_OK:'))  { d.cookies.push({ ok: true,  text: line.substring(10).trim() });             renderCard('headers'); }
            if (line.startsWith('COOKIE_WARN:')){ d.cookies.push({ ok: false, text: line.substring(12).trim() });             renderCard('headers'); }
            if (line.startsWith('CSP_FULL:'))   { d.cspFull = line.substring(9).trim();                                       renderCard('headers'); }
            if (line.startsWith('SERVER:'))     { d.server  = line.substring(7).trim();                                       renderCard('headers'); }
            if (line.startsWith('TIP:'))        { if (!d.tips) d.tips = []; d.tips.push(line.substring(4).trim());            renderCard('headers'); }
        }

        function parseDns(line) {
            const d = data.dnsrecon;
            const tm = line.match(/^\s*\[([A-Z]+)\]\s*$/);
            if (tm) { d._currentType = tm[1]; return; }
            if (line.includes('[SUBDOMAIN ENUM]')) { d._currentType = 'SUB'; return; }
            if (d._currentType && line.trim() && !line.startsWith('[')) {
                const val = line.trim();
                if (d._currentType === 'SUB') {
                    if (val.includes('->')) d.subdomains.push(val);
                } else {
                    if (!d.records[d._currentType]) d.records[d._currentType] = [];
                    d.records[d._currentType].push(val);
                }
                renderCard('dnsrecon');
            }
        }

        function parseExtra(line) {
            const d = data.extra;
            if (line.startsWith('WHOIS_FIELD:')) {
                const parts = line.substring(12).split(':');
                d.sections.push({ cat: 'whois', type: 'field', key: parts[0].trim(), val: parts.slice(1).join(':').trim() });
            }
            else if (line.startsWith('WHOIS_EMPTY:')) { d.sections.push({ cat:'whois', type:'empty', text: line.substring(12).trim() }); }
            else if (line.startsWith('EMAIL_OK:'))    { d.sections.push({ cat:'email', type:'ok',   text: line.substring(9).trim() }); }
            else if (line.startsWith('EMAIL_GOOD:'))  { d.sections.push({ cat:'email', type:'good', text: line.substring(11).trim() }); }
            else if (line.startsWith('EMAIL_INFO:'))  { d.sections.push({ cat:'email', type:'info', text: line.substring(11).trim() }); }
            else if (line.startsWith('EMAIL_MISS:'))  { d.sections.push({ cat:'email', type:'miss', text: line.substring(11).trim() }); }
            else if (line.startsWith('EMAIL_WARN:'))  { d.sections.push({ cat:'email', type:'warn', text: line.substring(11).trim() }); }
            else if (line.startsWith('ROBOTS_FOUND:')){ d.sections.push({ cat:'robots', type:'ok',   text: line.substring(13).trim() }); }
            else if (line.startsWith('ROBOTS_MISS:')) { d.sections.push({ cat:'robots', type:'miss', text: line.substring(12).trim() }); }
            else if (line.startsWith('ROBOTS_LINE:')) { d.sections.push({ cat:'robots', type:'line', text: line.substring(12).trim() }); }
            else if (line.startsWith('ROBOTS_WARN:')) { d.sections.push({ cat:'robots', type:'warn', text: line.substring(12).trim() }); }
            else if (line.startsWith('SITEMAP_OK:'))  { d.sections.push({ cat:'robots', type:'ok',   text: line.substring(11).trim() }); }
            else if (line.startsWith('SITEMAP_MISS:')){ d.sections.push({ cat:'robots', type:'info', text: 'sitemap.xml non trovato' }); }
            else if (line.startsWith('REDIR_STEP:'))  { d.sections.push({ cat:'redir',  type:'step', text: line.substring(11).trim() }); }
            else if (line.startsWith('REDIR_OK:'))    { d.sections.push({ cat:'redir',  type:'ok',   text: line.substring(9).trim() }); }
            else if (line.startsWith('REDIR_WARN:'))  { d.sections.push({ cat:'redir',  type:'warn', text: line.substring(11).trim() }); }
            else if (line.startsWith('REDIR_INFO:'))  { d.sections.push({ cat:'redir',  type:'info', text: line.substring(11).trim() }); }
            else if (line.startsWith('TIP:'))         { d.sections.push({ cat:'tip',    type:'tip',  text: line.substring(4).trim() }); }
            else if (line.startsWith('EXPOSED_WARN:')) { d.sections.push({ cat:'files', type:'warn',    text: line.substring(13).trim() }); }
            else if (line.startsWith('EXPOSED_OK:'))   { d.sections.push({ cat:'files', type:'ok',      text: line.substring(11).trim() }); }
            else if (line.startsWith('EXPOSED_INFO:')) { d.sections.push({ cat:'files', type:'info',    text: line.substring(13).trim() }); }
            else if (line.startsWith('EXPOSED_CLEAN:')){ d.sections.push({ cat:'files', type:'good',    text: line.substring(14).trim() }); }
            else if (line.startsWith('EXPOSED_SUMMARY:')) { d.sections.push({ cat:'files', type:'summary', text: line.substring(16).trim() }); }
            renderCard('extra');
        }

        function renderCard(name) {
            const body = document.getElementById('body-' + name);
            if (!body) return;
            const d = data[name];
            let html = '';

            if (name === 'nmap') {
                if (d.ports.length === 0 && !d.done) { body.innerHTML = '<span class="waiting">Scansione porte in corso...</span>'; return; }
                if (d.ports.length === 0 && d.done) { html = '<div class="hdr-item"><span class="hdr-icon">✅</span><span class="hdr-val hdr-ok">Nessuna porta aperta trovata</span></div>'; }
                else {
                    html = '<div style="margin-bottom:0.5rem;font-size:0.82rem;color:#999">' + d.ports.length + ' port' + (d.ports.length > 1 ? 'e' : 'a') + ' apert' + (d.ports.length > 1 ? 'e' : 'a') + '</div><div class="port-tags">';
                    const risky = ['21','22','23','25','110','135','139','445','3306','3389','5432'];
                    d.ports.forEach(p => {
                        const danger = risky.includes(p.port);
                        html += '<span class="port-tag' + (danger ? ' port-warn' : '') + '">' + escHtml(p.port) + ' <small>' + escHtml(p.service) + '</small></span>';
                    });
                    html += '</div>';
                    const riskyFound = d.ports.filter(p => risky.includes(p.port));
                    if (riskyFound.length > 0) {
                        html += '<div class="hdr-tip" style="margin-top:0.6rem;color:#dc2626">⚠ Port' + (riskyFound.length > 1 ? 'e' : 'a') + ' sensibil' + (riskyFound.length > 1 ? 'i' : 'e') + ': ' + riskyFound.map(p => p.port).join(', ') + ' — valuta se necessari' + (riskyFound.length > 1 ? 'e' : 'a') + '</div>';
                    }
                }
            }

            if (name === 'testssl') {
                const dt = data.testssl;
                if (dt.lines.length === 0 && !d.done) { body.innerHTML = '<span class="waiting">Analisi TLS in corso...</span>'; return; }
                if (dt.lines.length === 0 && d.done) { html = '<span class="waiting">Nessun dato TLS ottenuto.</span>'; }
                else {
                    let currentGroup = '';
                    const groups = {};
                    dt.lines.forEach(l => {
                        if (l.startsWith('Testing protocols') || l.startsWith('Testing cipher')
                            || l.startsWith('Testing server') || l.startsWith('Testing robust')
                            || l.startsWith('Testing HTTP') || l.startsWith('Testing vulnerabilities')
                            || l.startsWith('Testing ')) {
                            currentGroup = l.replace('Testing ', '').replace(' via sockets except NPN+ALPN', '');
                            groups[currentGroup] = [];
                        } else if (l.startsWith('Certificate information') || l.startsWith('Certificate Validity')
                                || l.startsWith('Certificates provided') || l.startsWith('Issuer')
                                || l.startsWith('Trust') || l.startsWith('Chain of trust')
                                || l.startsWith('Common Name') || l.startsWith('subjectAltName')) {
                            if (!groups['Certificate']) groups['Certificate'] = [];
                            groups['Certificate'].push(l);
                        } else if (currentGroup && l.trim()) {
                            if (!groups[currentGroup]) groups[currentGroup] = [];
                            groups[currentGroup].push(l);
                        }
                    });

                    for (const [group, lines] of Object.entries(groups)) {
                        if (lines.length === 0) continue;
                        html += '<div style="margin-bottom:0.7rem">';
                        html += '<div style="font-size:0.75rem;font-weight:700;color:#8b7cf8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.3rem">' + escHtml(group) + '</div>';
                        lines.forEach(l => {
                            let icon = '', color = '#555';
                            if (l.includes('(OK)') || l.includes('not vulnerable') || l.includes('not offered') || l.includes('offered (OK)')) {
                                icon = '✅'; color = '#22a06b';
                            } else if (l.includes('VULNERABLE') || l.includes('NOT ok') || l.includes('CRITICAL') || l.includes('HIGH')) {
                                icon = '🚨'; color = '#dc2626';
                            } else if (l.includes('offered') || l.includes('yes')) {
                                icon = 'ℹ️'; color = '#555';
                            } else if (l.includes('MEDIUM') || l.includes('WARN')) {
                                icon = '⚠️'; color = '#d97706';
                            }
                            if (icon) {
                                html += '<div class="hdr-item"><span class="hdr-icon">' + icon + '</span><span class="hdr-val" style="color:' + color + ';font-size:0.82rem">' + escHtml(l) + '</span></div>';
                            } else {
                                html += '<div style="font-size:0.8rem;color:#555;padding:0.15rem 0;font-family:monospace">' + escHtml(l) + '</div>';
                            }
                        });
                        html += '</div>';
                    }

                    if (dt.vulns.length > 0) {
                        html += '<div style="margin-top:0.5rem;border-top:1px solid rgba(0,0,0,0.05);padding-top:0.5rem">';
                        html += '<div class="hdr-tip" style="color:#dc2626">🚨 ' + dt.vulns.length + ' vulnerabilità TLS rilevat' + (dt.vulns.length > 1 ? 'e' : 'a') + '</div>';
                        html += '</div>';
                    }
                }
            }

            if (name === 'headers') {
                const d2 = data.headers;
                if (d2.items.length === 0 && !d.done) { body.innerHTML = '<span class="waiting">Analisi header in corso...</span>'; return; }
                if (d2.items.length === 0 && d.done) { html = '<span class="waiting">Nessun dato ottenuto.</span>'; }
                else {
                    if (d2.server) html += '<div class="hdr-item"><span class="hdr-icon">🖥</span><span class="hdr-name">Server</span><span class="hdr-val">' + escHtml(d2.server) + '</span></div>';
                    const ok   = d2.items.filter(i => i.ok);
                    const miss = d2.items.filter(i => !i.ok && !i.warn);
                    const warn = d2.items.filter(i => i.warn);
                    miss.forEach(i => { html += '<div class="hdr-item"><span class="hdr-icon">❌</span><span class="hdr-val hdr-miss">' + escHtml(i.text) + '</span></div>'; });
                    warn.forEach(i => { html += '<div class="hdr-item"><span class="hdr-icon">⚠️</span><span class="hdr-val" style="color:#d97706">' + escHtml(i.text) + '</span></div>'; });
                    ok.forEach(i =>   { html += '<div class="hdr-item"><span class="hdr-icon">✅</span><span class="hdr-val hdr-ok">' + escHtml(i.text) + '</span></div>'; });

                    if (d2.cspFull) {
                        html += '<div style="margin-top:0.6rem;font-size:0.78rem;color:#999;margin-bottom:0.3rem">Content-Security-Policy completa</div>';
                        html += '<div class="csp-block">' + escHtml(d2.cspFull).replace(/;\s*/g, ';<br>') + '</div>';
                    }
                    if (d2.cookies.length > 0) {
                        html += '<div style="margin-top:0.6rem;font-size:0.78rem;color:#999;margin-bottom:0.3rem">Cookies</div>';
                        d2.cookies.forEach(ck => {
                            html += '<div class="cookie-item ' + (ck.ok ? 'cookie-ok' : 'cookie-warn') + '">' + (ck.ok ? '✅ ' : '⚠️ ') + escHtml(ck.text) + '</div>';
                        });
                    }
                    if (d2.tips && d2.tips.length > 0) {
                        html += '<div style="margin-top:0.6rem;border-top:1px solid rgba(0,0,0,0.05);padding-top:0.6rem">';
                        d2.tips.forEach(t => { html += '<div class="hdr-tip">💡 ' + escHtml(t) + '</div>'; });
                        html += '</div>';
                    }
                }
            }

            if (name === 'dnsrecon') {
                const order = ['A','AAAA','MX','NS','TXT','SOA','CNAME','CAA','SRV'];
                let hasRecords = false;
                order.forEach(type => {
                    if (d.records[type] && d.records[type].length > 0) {
                        hasRecords = true;
                        html += '<div class="dns-group"><div class="dns-type">' + escHtml(type) + '</div>';
                        html += '<div class="dns-values">' + d.records[type].map(v => '<div class="dns-row">' + escHtml(v) + '</div>').join('') + '</div></div>';
                    }
                });
                if (d.subdomains.length > 0) {
                    hasRecords = true;
                    html += '<div class="dns-group"><div class="dns-type">SUBDOMAINS (' + d.subdomains.length + ')</div>';
                    html += '<div class="dns-values">' + d.subdomains.map(v => '<span class="port-tag">' + escHtml(v) + '</span> ').join('') + '</div></div>';
                }
                if (!hasRecords && !d.done) { body.innerHTML = '<span class="waiting">Enumerazione DNS in corso...</span>'; return; }
                if (!hasRecords && d.done) html = '<span class="waiting">Nessun record trovato.</span>';
            }

            if (name === 'extra') {
                const dx = data.extra;
                if (dx.sections.length === 0 && !d.done) { body.innerHTML = '<span class="waiting">Analisi in corso...</span>'; return; }

                const cats = { whois: [], email: [], files: [], robots: [], redir: [], tip: [] };
                dx.sections.forEach(s => { if (cats[s.cat]) cats[s.cat].push(s); });

                if (cats.whois.length > 0) {
                    html += '<div style="margin-bottom:0.8rem"><div style="font-size:0.75rem;font-weight:700;color:#8b7cf8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.4rem">WHOIS</div>';
                    cats.whois.forEach(w => {
                        if (w.type === 'field') html += '<div class="hdr-item"><span class="hdr-name" style="min-width:110px">' + escHtml(w.key) + '</span><span class="hdr-val">' + escHtml(w.val) + '</span></div>';
                        if (w.type === 'empty') html += '<div class="hdr-item"><span class="hdr-val" style="color:#999">' + escHtml(w.text) + '</span></div>';
                    });
                    html += '</div>';
                }
                if (cats.email.length > 0) {
                    html += '<div style="margin-bottom:0.8rem"><div style="font-size:0.75rem;font-weight:700;color:#8b7cf8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.4rem">EMAIL SECURITY</div>';
                    cats.email.forEach(e => {
                        const icon = e.type === 'ok' || e.type === 'good' ? '✅' : e.type === 'miss' ? '❌' : e.type === 'warn' ? '⚠️' : 'ℹ️';
                        const col  = e.type === 'good' || e.type === 'ok' ? '#22a06b' : e.type === 'miss' ? '#dc2626' : e.type === 'warn' ? '#d97706' : '#555';
                        html += '<div class="hdr-item"><span class="hdr-icon">' + icon + '</span><span class="hdr-val" style="color:' + col + '">' + escHtml(e.text) + '</span></div>';
                    });
                    html += '</div>';
                }
                if (cats.robots.length > 0) {
                    html += '<div style="margin-bottom:0.8rem"><div style="font-size:0.75rem;font-weight:700;color:#8b7cf8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.4rem">ROBOTS & SITEMAP</div>';
                    const robotLines = cats.robots.filter(r => r.type === 'line');
                    const robotOther = cats.robots.filter(r => r.type !== 'line');
                    robotOther.forEach(r => {
                        const icon = r.type === 'ok' ? '✅' : r.type === 'warn' ? '⚠️' : 'ℹ️';
                        html += '<div class="hdr-item"><span class="hdr-icon">' + icon + '</span><span class="hdr-val">' + escHtml(r.text) + '</span></div>';
                    });
                    if (robotLines.length > 0) {
                        html += '<div style="max-height:120px;overflow-y:auto;background:rgba(0,0,0,0.015);border-radius:8px;padding:0.4rem 0.6rem;margin-top:0.3rem">';
                        robotLines.forEach(r => { html += '<div style="font-size:0.75rem;font-family:monospace;color:#555;padding:0.1rem 0">' + escHtml(r.text) + '</div>'; });
                        html += '</div>';
                    }
                    html += '</div>';
                }
                if (cats.files && cats.files.length > 0) {
                    html += '<div style="margin-bottom:0.8rem"><div style="font-size:0.75rem;font-weight:700;color:#8b7cf8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.4rem">FILE SENSIBILI</div>';
                    cats.files.forEach(f => {
                        const icon = f.type === 'warn' ? '🚨' : f.type === 'good' || f.type === 'ok' ? '✅' : f.type === 'summary' ? '📊' : 'ℹ️';
                        const col  = f.type === 'warn' ? '#dc2626' : f.type === 'good' || f.type === 'ok' ? '#22a06b' : '#555';
                        html += '<div class="hdr-item"><span class="hdr-icon">' + icon + '</span><span class="hdr-val" style="color:' + col + ';font-size:0.82rem">' + escHtml(f.text) + '</span></div>';
                    });
                    html += '</div>';
                }
                if (cats.redir.length > 0) {
                    html += '<div style="margin-bottom:0.8rem"><div style="font-size:0.75rem;font-weight:700;color:#8b7cf8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.4rem">REDIRECT CHAIN</div>';
                    cats.redir.forEach(r => {
                        const icon = r.type === 'ok' ? '✅' : r.type === 'warn' ? '⚠️' : r.type === 'step' ? '↪' : 'ℹ️';
                        const col  = r.type === 'ok' ? '#22a06b' : r.type === 'warn' ? '#dc2626' : '#555';
                        html += '<div class="hdr-item"><span class="hdr-icon">' + icon + '</span><span class="hdr-val" style="color:' + col + '">' + escHtml(r.text) + '</span></div>';
                    });
                    html += '</div>';
                }
                if (cats.tip.length > 0) {
                    html += '<div style="border-top:1px solid rgba(0,0,0,0.05);padding-top:0.5rem">';
                    cats.tip.forEach(t => { html += '<div class="hdr-tip">💡 ' + escHtml(t.text) + '</div>'; });
                    html += '</div>';
                }
                if (!html) html = '<span class="waiting">Nessun dato aggiuntivo.</span>';
            }

            body.innerHTML = html;

            if (d.done) {
                let warn = false;
                if (name === 'nmap'    && d.ports.some(p => ['21','22','23','25','445','3306','3389'].includes(p.port))) warn = true;
                if (name === 'testssl' && data.testssl.vulns.length > 0) warn = true;
                if (name === 'headers' && data.headers.items.some(i => !i.ok)) warn = true;
                if (name === 'extra'   && data.extra.sections.some(s => s.type === 'miss' || s.type === 'warn')) warn = true;
                setStatus(name, warn ? 'warning' : 'done', warn ? 'ATTENZIONE' : 'COMPLETATO');
            }
        }

        function setStatus(name, cls, text) {
            const el = document.getElementById('status-' + name);
            if (!el) return;
            el.className = 'scan-card-status status-' + cls;
            el.textContent = text;
        }

        function showTargetBar() {
            const bar = document.getElementById('targetBar');
            if (!bar) return;
            bar.style.display = 'flex';
            bar.innerHTML =
                '<span class="target-url">' + escHtml(targetHost) + '</span>' +
                '<span class="target-ip">'  + escHtml(targetIp)   + '</span>' +
                (isCf ? '<span class="target-badge cf">CLOUDFLARE</span>'
                       : '<span class="target-badge direct">DIRETTO</span>');
        }

        function addConsole(text /*, isSection */) {
            const box = document.getElementById('consoleBox');
            if (!box) return;
            const div = document.createElement('div');
            div.className = 'cline';
            if (text.startsWith('===')) div.classList.add('section');
            if (text.startsWith('[...]')) div.classList.add('dim');
            div.textContent = text;
            box.appendChild(div);
            box.scrollTop = box.scrollHeight;
            lineCount++;
            const lc = document.getElementById('lineCount');
            if (lc) lc.textContent = lineCount + ' righe';
        }

        const consoleToggle = document.getElementById('consoleToggle');
        if (consoleToggle) {
            consoleToggle.addEventListener('click', function () {
                this.classList.toggle('open');
                const cw = document.getElementById('consoleWrap');
                if (cw) cw.classList.toggle('open');
            });
        }

        // ── PDF Report Generator ──
        function generatePDF() {
            if (!window.jspdf || !window.jspdf.jsPDF) {
                console.error('jsPDF non disponibile');
                return;
            }
            const jsPDF = window.jspdf.jsPDF;
            const doc = new jsPDF('p', 'mm', 'a4');
            const W = 210, M = 15, CW = W - M * 2;
            let y = 15;
            const purple = [139, 124, 248];
            const dk = [26, 26, 26], gr = [100, 100, 100];
            const gn = [34, 160, 107], rd = [220, 38, 38], og = [217, 119, 6];

            function ck(n) { if (y + n > 280) { doc.addPage(); y = 15; } }
            function hd(t) {
                ck(12);
                doc.setFillColor(purple[0], purple[1], purple[2]);
                doc.roundedRect(M, y, CW, 8, 2, 2, 'F');
                doc.setFont('helvetica', 'bold'); doc.setFontSize(10);
                doc.setTextColor(255, 255, 255);
                doc.text(t, M + 3, y + 5.5); y += 12;
            }
            function rw(label, value, color) {
                ck(7);
                doc.setFont('helvetica', 'bold'); doc.setFontSize(8.5);
                doc.setTextColor(dk[0], dk[1], dk[2]);
                doc.text(label, M + 2, y + 4);
                doc.setFont('helvetica', 'normal');
                const c2 = color || gr;
                doc.setTextColor(c2[0], c2[1], c2[2]);
                const ls = doc.splitTextToSize(String(value), CW - 55);
                doc.text(ls, M + 52, y + 4);
                y += Math.max(7, ls.length * 4.5);
            }
            function ln(text, color, bold) {
                ck(6);
                doc.setFont('helvetica', bold ? 'bold' : 'normal'); doc.setFontSize(8);
                const c2 = color || gr;
                doc.setTextColor(c2[0], c2[1], c2[2]);
                const ls = doc.splitTextToSize(String(text), CW - 4);
                doc.text(ls, M + 2, y + 3.5);
                y += Math.max(5.5, ls.length * 4);
            }
            function sep() {
                y += 2; doc.setDrawColor(220, 220, 220);
                doc.line(M, y, W - M, y); y += 3;
            }

            doc.setFillColor(purple[0], purple[1], purple[2]);
            doc.rect(0, 0, W, 35, 'F');
            doc.setFont('helvetica', 'bold'); doc.setFontSize(20);
            doc.setTextColor(255, 255, 255);
            doc.text('PatchPulse', M, 16);
            doc.setFontSize(11); doc.setFont('helvetica', 'normal');
            doc.text('Security Scan Report', M, 24);
            doc.setFontSize(8);
            doc.text(new Date().toLocaleString('it-IT'), W - M, 24, { align: 'right' });
            y = 42;

            doc.setFont('helvetica', 'bold'); doc.setFontSize(12);
            doc.setTextColor(dk[0], dk[1], dk[2]);
            doc.text(targetHost || 'N/A', M, y); y += 5;
            doc.setFont('helvetica', 'normal'); doc.setFontSize(9);
            doc.setTextColor(gr[0], gr[1], gr[2]);
            let ti = 'IP: ' + (targetIp || 'N/A');
            if (isCf) ti += '  |  Cloudflare';
            doc.text(ti, M, y); y += 10;

            hd('PORT SCAN');
            if (data.nmap.ports.length === 0) ln('Nessuna porta aperta trovata', gn);
            else {
                const risky = ['21','22','23','25','110','135','139','445','3306','3389','5432'];
                data.nmap.ports.forEach(p => {
                    const danger = risky.indexOf(p.port) >= 0;
                    rw('Porta ' + p.port, p.service + (danger ? '  - porta sensibile' : ''), danger ? rd : gr);
                });
            }

            hd('SSL / TLS');
            if (data.testssl.lines.length === 0) ln('Nessun dato TLS disponibile', gr);
            else {
                data.testssl.lines.forEach(l => {
                    if (l.match(/^x[0-9a-f]{4}\s/)) return;
                    if (l === '-' || l === 'SSLv2' || l === 'SSLv3' || l === 'TLSv1' || l === 'TLSv1.1') return;
                    let color = gr;
                    if (l.indexOf('(OK)') >= 0 || l.indexOf('not vulnerable') >= 0) color = gn;
                    else if (l.indexOf('VULNERABLE') >= 0 || l.indexOf('NOT ok') >= 0) color = rd;
                    else if (l.indexOf('MEDIUM') >= 0 || l.indexOf('WARN') >= 0) color = og;
                    if (l.indexOf('Testing ') === 0) { sep(); ln(l, purple, true); }
                    else ln(l, color);
                });
                if (data.testssl.vulns.length > 0) {
                    sep(); ln(data.testssl.vulns.length + ' vulnerabilita TLS rilevate', rd, true);
                }
            }

            hd('HTTP HEADERS & COOKIES');
            if (data.headers.server) rw('Server', data.headers.server);
            data.headers.items.forEach(i => {
                ln((i.ok ? '+ ' : '- ') + i.text, i.ok ? gn : (i.warn ? og : rd));
            });
            if (data.headers.cookies.length > 0) {
                sep(); ln('Cookies:', dk, true);
                data.headers.cookies.forEach(ck2 => {
                    ln((ck2.ok ? '+ ' : '! ') + ck2.text, ck2.ok ? gn : og);
                });
            }
            if (data.headers.cspFull) {
                sep(); ln('CSP completa:', dk, true);
                data.headers.cspFull.split(';').forEach(p => {
                    const t = p.trim(); if (t) ln('  ' + t + ';', gr);
                });
            }
            if (data.headers.tips && data.headers.tips.length > 0) {
                sep();
                data.headers.tips.forEach(t => ln('> ' + t, purple));
            }

            hd('DNS RECORDS');
            const dnsOrder = ['A','AAAA','MX','NS','TXT','SOA','CNAME','CAA','SRV'];
            dnsOrder.forEach(type => {
                if (data.dnsrecon.records[type] && data.dnsrecon.records[type].length > 0) {
                    rw(type, data.dnsrecon.records[type].join(', '));
                }
            });
            if (data.dnsrecon.subdomains.length > 0) {
                sep(); ln('Subdomains:', dk, true);
                data.dnsrecon.subdomains.forEach(s => ln('  ' + s, gr));
            }

            hd('INFORMAZIONI AGGIUNTIVE');
            const cats = { whois: [], email: [], files: [], robots: [], redir: [], tip: [] };
            data.extra.sections.forEach(s => { if (cats[s.cat]) cats[s.cat].push(s); });

            if (cats.whois.length > 0) {
                ln('WHOIS', purple, true);
                cats.whois.forEach(w => {
                    if (w.type === 'field') rw(w.key, w.val);
                    else ln(w.text, gr);
                });
                sep();
            }
            if (cats.email.length > 0) {
                ln('Email Security', purple, true);
                cats.email.forEach(e => {
                    const col = (e.type === 'ok' || e.type === 'good') ? gn : e.type === 'miss' ? rd : e.type === 'warn' ? og : gr;
                    ln(e.text, col);
                });
                sep();
            }
            if (cats.files.length > 0) {
                ln('File Sensibili', purple, true);
                cats.files.forEach(f => {
                    const col = f.type === 'warn' ? rd : (f.type === 'good' || f.type === 'ok') ? gn : gr;
                    ln(f.text, col);
                });
                sep();
            }
            if (cats.robots.length > 0) {
                ln('Robots & Sitemap', purple, true);
                cats.robots.filter(r => r.type !== 'line').forEach(r => {
                    ln(r.text, r.type === 'warn' ? og : r.type === 'ok' ? gn : gr);
                });
                sep();
            }
            if (cats.redir.length > 0) {
                ln('Redirect Chain', purple, true);
                cats.redir.forEach(r => {
                    const col = r.type === 'ok' ? gn : r.type === 'warn' ? rd : gr;
                    ln(r.text, col);
                });
            }
            if (cats.tip.length > 0) {
                sep(); ln('Suggerimenti:', dk, true);
                cats.tip.forEach(t => ln('> ' + t.text, purple));
            }

            const pages = doc.internal.getNumberOfPages();
            for (let i = 1; i <= pages; i++) {
                doc.setPage(i);
                doc.setFont('helvetica', 'normal'); doc.setFontSize(7);
                doc.setTextColor(180, 180, 180);
                doc.text('PatchPulse Security Report - ' + targetHost + ' - ' + new Date().toLocaleDateString('it-IT'), M, 292);
                doc.text('Pagina ' + i + '/' + pages, W - M, 292, { align: 'right' });
            }

            const safeName = (targetHost || 'scan').replace(/[^a-zA-Z0-9.\-]/g, '_');
            doc.save('PatchPulse_Report_' + safeName + '.pdf');
        }
    }

    // ======================================================================
    // 10) vulnerabilities_info.php — smooth scroll su categorie
    // ======================================================================
    function initVulnerabilitiesInfo() {
        const catBtns = $$('.category-btn');
        if (catBtns.length === 0) return;

        catBtns.forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (!target) return;
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                catBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }

    // ======================================================================
    // 11) Bottoni con conferma — sostituisce gli onclick="return confirm(...)"
    //     Uso: <button data-confirm="Sei sicuro?">…</button>
    // ======================================================================
    function initConfirmButtons() {
        $$('[data-confirm]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const msg = btn.dataset.confirm;
                if (msg && !confirm(msg)) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                }
            });
        });
    }

})();
