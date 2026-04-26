<?php
include("config.php");
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PatchPulse - Informazioni Vulnerabilità</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .vuln-content { max-width: 900px; margin: 0 auto; padding: 0 1rem 4rem; }
        .category-nav { display: flex; justify-content: center; gap: 0.7rem; margin-bottom: 2.5rem; flex-wrap: wrap; }
        .category-btn {
            padding: 0.6rem 1.3rem; background: #fff; border: 1.5px solid rgba(0,0,0,0.1);
            border-radius: 50px; font-family: 'DM Sans', sans-serif; font-size: 0.88rem;
            font-weight: 500; color: #777; cursor: pointer; transition: all 0.2s; text-decoration: none;
        }
        .category-btn:hover, .category-btn.active { border-color: var(--purple); color: var(--purple); background: rgba(139,124,248,0.06); }
        .info-card {
            background: #fff; border: 1px solid rgba(0,0,0,0.07); border-radius: 16px;
            padding: 1.8rem; margin-bottom: 1.5rem; transition: border-color 0.2s, box-shadow 0.2s;
        }
        .info-card:hover { border-color: rgba(139,124,248,0.25); box-shadow: 0 4px 16px rgba(0,0,0,0.06); }
        .info-card h2 { color: #1a1a1a; font-size: 1.2rem; font-weight: 600; margin-bottom: 0.8rem; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
        .info-card h3 { color: #1a1a1a; font-size: 1rem; font-weight: 600; margin: 1.2rem 0 0.5rem; }
        .info-card p { color: #666; font-size: 0.92rem; line-height: 1.7; margin-bottom: 0.8rem; }
        .info-card ul { color: #666; margin-left: 1.2rem; margin-bottom: 0.8rem; font-size: 0.9rem; }
        .info-card li { margin-bottom: 0.4rem; line-height: 1.6; }
        .risk-level { padding: 0.2rem 0.65rem; border-radius: 20px; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.04em; }
        .risk-low { background: rgba(34,160,107,0.1); color: #22a06b; }
        .risk-medium { background: rgba(217,119,6,0.1); color: #d97706; }
        .risk-high { background: rgba(220,38,38,0.1); color: #dc2626; }
        .protection-tip {
            background: rgba(139,124,248,0.06); border-left: 3px solid var(--purple);
            padding: 0.8rem 1rem; margin: 0.8rem 0; border-radius: 0 10px 10px 0;
            font-size: 0.9rem; color: #555; line-height: 1.6;
        }
        .protection-tip strong { color: var(--purple); }
        @media (max-width: 768px) { .category-nav { flex-direction: column; align-items: center; } .category-btn { width: 100%; max-width: 280px; text-align: center; } }
    </style>
</head>
<body>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <a href="home.php" class="logo"><img src="images/PatchPulseLogo.svg" alt="PatchPulse" style="width:35px;height:35px;object-fit:contain;">PatchPulse</a>
        <div style="display:flex;align-items:center;gap:0.5rem"><button class="hamburger" id="hamburger" aria-label="Menu"><span></span><span></span><span></span></button></div>
    </div>
    <div class="nav-section">
        <a href="home.php" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>Homepage</a>
        <a href="home.php#servizi" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>Applications</a>
        <a href="browser-scan.php" class="nav-item active"><span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>Browser Scanner</a>
    </div>
    <div class="sidebar-bottom">
        <?php if (isset($_SESSION['user_id'])): ?><a href="account.php" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>Area Personale</a><?php else: ?><a href="log-reg.php" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg></span>Login</a><?php endif; ?>
    </div>
</aside>
<main class="main-wrapper" id="main">
    <div class="page-header">
        <a href="browser-scan.php" class="page-header-back"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg> Torna al Browser Scanner</a>
        <p class="page-header-eyebrow">Guida Sicurezza</p>
        <h1 class="page-header-title">Informazioni Vulnerabilità</h1>
        <p class="page-header-desc">Comprendi i rischi per la privacy e la sicurezza rilevati dal nostro scanner e scopri come proteggerti.</p>
    </div>
    <div class="vuln-content">
<!-- Category Navigation -->
            <div class="category-nav">
                <a href="#web-tracking" class="category-btn">Web Tracking</a>
                <a href="#functionality" class="category-btn">Funzionalità</a>
                <a href="#device-info" class="category-btn">Informazioni Dispositivo</a>
            </div>

            <!-- WEB TRACKING SECTION -->
            <section id="web-tracking">
                <div id="cookies" class="info-card">
                    <h2>Cookies Tracking <span class="risk-level risk-medium">Rischio Medio</span></h2>
                    <h3>Cosa sono i Cookies?</h3>
                    <p>I cookies sono piccoli file di testo memorizzati nel tuo browser dai siti web che visiti. Vengono utilizzati per ricordare le tue preferenze, mantenerti collegato e tracciare la tua attività online.</p>
                    
                    <h3>Rischi per la Privacy</h3>
                    <ul>
                        <li>Tracciamento comportamentale attraverso più siti web</li>
                        <li>Profilazione per pubblicità mirata</li>
                        <li>Possibile vendita dei dati a terze parti</li>
                        <li>Creazione di profili dettagliati delle abitudini di navigazione</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Configura il browser per bloccare cookies di terze parti, usa modalità incognito, cancella regolarmente i cookies e utilizza estensioni per la privacy come uBlock Origin.
                    </div>

                </div>

                <div id="hardware" class="info-card">
                    <h2>Hardware Information <span class="risk-level risk-medium">Rischio Medio</span></h2>
                    <h3>Informazioni Hardware Rivelate</h3>
                    <p>Il browser può rivelare dettagli specifici sull'hardware del tuo dispositivo che contribuiscono al fingerprinting.</p>
                    
                    <h3>Dati Raccolti</h3>
                    <ul>
                        <li>Modello e produttore GPU</li>
                        <li>Numero di core CPU</li>
                        <li>Quantità di RAM</li>
                        <li>Capacità di storage</li>
                        <li>Sensori disponibili</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Usa browser con protezioni hardware spoofing, limita l'accesso JavaScript alle API hardware.
                    </div>
                
                </div>

                <div id="dnt" class="info-card">
                    <h2>Do Not Track <span class="risk-level risk-low">Rischio Basso</span></h2>
                    <h3>Cos'è Do Not Track?</h3>
                    <p>Do Not Track (DNT) è un'impostazione del browser che invia una richiesta ai siti web di non tracciare la tua attività. Tuttavia, è solo una richiesta e molti siti la ignorano.</p>
                    
                    <h3>Limitazioni</h3>
                    <ul>
                        <li>Non è vincolante legalmente</li>
                        <li>Molti siti web ignorano la richiesta</li>
                        <li>Non impedisce tutti i tipi di tracciamento</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Attiva DNT nelle impostazioni del browser, ma non fare affidamento solo su questo. Combina con altre misure di protezione.
                    </div>
                </div>

                <div id="fingerprinting" class="info-card">
                    <h2>Browser Fingerprinting <span class="risk-level risk-high">Rischio Alto</span></h2>
                    <h3>Cos'è il Browser Fingerprinting?</h3>
                    <p>Il fingerprinting del browser è una tecnica che raccoglie informazioni uniche sul tuo browser e dispositivo per creare un'impronta digitale che può identificarti anche senza cookies.</p>
                    
                    <h3>Informazioni Raccolte</h3>
                    <ul>
                        <li>Risoluzione schermo e profondità colori</li>
                        <li>Font installati nel sistema</li>
                        <li>Plugin e estensioni del browser</li>
                        <li>Fuso orario e lingua</li>
                        <li>Caratteristiche hardware (GPU, CPU)</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Usa browser focalizzati sulla privacy come Tor Browser, disabilita JavaScript quando possibile, usa estensioni anti-fingerprinting.
                    </div>
                </div>

                <div id="webrtc" class="info-card">
                    <h2>WebRTC Support <span class="risk-level risk-medium">Rischio Medio</span></h2>
                    <h3>Cos'è WebRTC?</h3>
                    <p>WebRTC (Real-Time Communication) è una tecnologia che permette comunicazioni audio/video direttamente nel browser. Può rivelare il tuo vero indirizzo IP anche se usi una VPN.</p>
                    
                    <h3>Rischi</h3>
                    <ul>
                        <li>Bypass delle VPN rivelando l'IP reale</li>
                        <li>Possibile fingerprinting attraverso le capacità multimediali</li>
                        <li>Raccolta di informazioni sulla rete locale</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Disabilita WebRTC nelle impostazioni del browser o usa estensioni specifiche. Verifica che la tua VPN blocchi le fughe WebRTC.
                    </div>
                </div>

                <div id="https" class="info-card">
                    <h2>HTTPS Only <span class="risk-level risk-low">Rischio Basso</span></h2>
                    <h3>Cos'è HTTPS Only?</h3>
                    <p>HTTPS Only è una modalità che forza il browser a usare sempre connessioni crittografate (HTTPS) invece di HTTP non sicuro.</p>
                    
                    <h3>Benefici</h3>
                    <ul>
                        <li>Protezione contro intercettazioni (man-in-the-middle)</li>
                        <li>Crittografia dei dati in transito</li>
                        <li>Verifica dell'identità del sito web</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Attiva sempre la modalità HTTPS Only nel browser. Evita siti che non supportano HTTPS per informazioni sensibili.
                    </div>
                </div>

                <div id="blocked-resources" class="info-card">
                    <h2>Blocked Resources <span class="risk-level risk-low">Rischio Basso</span></h2>
                    <h3>Cosa sono le Risorse Bloccate?</h3>
                    <p>Le risorse bloccate sono script, immagini, CSS o altri contenuti che il browser impedisce di caricare per motivi di sicurezza o privacy.</p>
                    
                    <h3>Cause del Blocco</h3>
                    <ul>
                        <li>Adblocker attivi</li>
                        <li>Impostazioni di sicurezza del browser</li>
                        <li>Protezione anti-tracking</li>
                        <li>Firewall o filtri di rete</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Beneficio:</strong> Il blocco di risorse migliora la privacy e la sicurezza, riducendo il tracciamento e i potenziali malware.
                    </div>
                </div>

                <div id="adblock" class="info-card">
                    <h2>AdBlocker <span class="risk-level risk-low">Rischio Basso</span></h2>
                    <h3>Cos'è un AdBlocker?</h3>
                    <p>Gli AdBlocker sono strumenti che bloccano pubblicità, tracker e script malevoli sui siti web, migliorando privacy e sicurezza.</p>
                    
                    <h3>Benefici</h3>
                    <ul>
                        <li>Blocco di tracker pubblicitari</li>
                        <li>Protezione da malware nelle pubblicità</li>
                        <li>Navigazione più veloce e pulita</li>
                        <li>Risparmio di banda e batteria</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Raccomandazione:</strong> Usa AdBlocker affidabili come uBlock Origin. Considera di supportare siti che rispettano la privacy disabilitando l'adblocker selettivamente.
                    </div>
                </div>
            </section>

            <!-- FUNCTIONALITY SECTION -->
            <section id="functionality">
                <div id="javascript" class="info-card">
                    <h2>JavaScript <span class="risk-level risk-medium">Rischio Medio</span></h2>
                    <h3>Cos'è JavaScript?</h3>
                    <p>JavaScript è un linguaggio di programmazione che rende i siti web interattivi. Tuttavia, può essere utilizzato per tracciamento e fingerprinting.</p>
                    
                    <h3>Rischi</h3>
                    <ul>
                        <li>Esecuzione di codice di tracciamento</li>
                        <li>Fingerprinting avanzato del browser</li>
                        <li>Raccolta di informazioni dettagliate sul dispositivo</li>
                        <li>Possibili vulnerabilità di sicurezza</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Usa estensioni come NoScript per controllare l'esecuzione di JavaScript. Disabilita JS su siti non attendibili.
                    </div>
                </div>

                <div id="webgl" class="info-card">
                    <h2>WebGL <span class="risk-level risk-high">Rischio Alto</span></h2>
                    <h3>Cos'è WebGL?</h3>
                    <p>WebGL è una API per rendering grafico 3D nel browser. Può essere utilizzata per fingerprinting molto preciso attraverso informazioni sulla GPU.</p>
                    
                    <h3>Rischi per la Privacy</h3>
                    <ul>
                        <li>Fingerprinting basato sulla GPU</li>
                        <li>Informazioni dettagliate sui driver grafici</li>
                        <li>Capacità di rendering uniche per ogni dispositivo</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Disabilita WebGL se non necessario, usa browser con protezioni anti-fingerprinting integrate.
                    </div>
                </div>

                <div id="developer-mode" class="info-card">
                    <h2>Developer Mode <span class="risk-level risk-medium">Rischio Medio</span></h2>
                    <h3>Cos'è la Modalità Sviluppatore?</h3>
                    <p>La modalità sviluppatore è un insieme di strumenti avanzati nel browser che permettono di ispezionare, modificare e debuggare pagine web.</p>
                    
                    <h3>Rischi di Rilevamento</h3>
                    <ul>
                        <li>I siti possono rilevare se gli strumenti di sviluppo sono aperti</li>
                        <li>Possibile fingerprinting basato su questa informazione</li>
                        <li>Alcuni siti bloccano funzionalità se rilevano dev tools</li>
                        <li>Tracciamento del comportamento "tecnico" dell'utente</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Chiudi gli strumenti di sviluppo quando non necessari. Usa browser separati per navigazione normale e sviluppo.
                    </div>
                </div>

                <div id="webassembly" class="info-card">
                    <h2>WebAssembly <span class="risk-level risk-medium">Rischio Medio</span></h2>
                    <h3>Cos'è WebAssembly?</h3>
                    <p>WebAssembly (WASM) è una tecnologia che permette di eseguire codice ad alte prestazioni nel browser, scritto in linguaggi come C++ o Rust.</p>
                    
                    <h3>Rischi Potenziali</h3>
                    <ul>
                        <li>Possibile esecuzione di codice malevolo più difficile da rilevare</li>
                        <li>Fingerprinting attraverso le prestazioni di esecuzione</li>
                        <li>Maggiore difficoltà nell'analisi del codice</li>
                        <li>Possibili vulnerabilità di sicurezza</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Considera di disabilitare WebAssembly se non necessario. Usa browser con sandbox robusti.
                    </div>
                </div>

                <div id="web-workers" class="info-card">
                    <h2>Web Workers <span class="risk-level risk-low">Rischio Basso</span></h2>
                    <h3>Cosa sono i Web Workers?</h3>
                    <p>I Web Workers permettono di eseguire JavaScript in background, separatamente dal thread principale della pagina web.</p>
                    
                    <h3>Considerazioni di Privacy</h3>
                    <ul>
                        <li>Possono essere usati per fingerprinting delle prestazioni</li>
                        <li>Esecuzione di codice in background meno visibile</li>
                        <li>Potenziale per attività di mining nascosto</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Informazione:</strong> I Web Workers sono generalmente sicuri ma possono essere usati per attività non trasparenti.
                    </div>
                </div>

                <div id="media-queries" class="info-card">
                    <h2>Media Queries <span class="risk-level risk-low">Rischio Basso</span></h2>
                    <h3>Cosa sono le Media Queries?</h3>
                    <p>Le Media Queries sono una tecnologia CSS che permette di adattare il design della pagina web a diverse dimensioni e caratteristiche dello schermo.</p>
                    
                    <h3>Utilizzo per Fingerprinting</h3>
                    <ul>
                        <li>Rilevamento di caratteristiche specifiche del dispositivo</li>
                        <li>Informazioni su risoluzione e orientamento</li>
                        <li>Tipo di dispositivo (desktop, tablet, mobile)</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Informazione:</strong> Le Media Queries sono essenziali per il web responsive e raramente rappresentano un rischio significativo.
                    </div>
                </div>

                <div id="web-notifications" class="info-card">
                    <h2>Web Notifications <span class="risk-level risk-medium">Rischio Medio</span></h2>
                    <h3>Cosa sono le Notifiche Web?</h3>
                    <p>Le notifiche web permettono ai siti di inviare messaggi anche quando la pagina non è aperta nel browser.</p>
                    
                    <h3>Rischi per la Privacy</h3>
                    <ul>
                        <li>Tracciamento dell'engagement dell'utente</li>
                        <li>Possibile spam di notifiche</li>
                        <li>Raccolta di dati sui pattern di utilizzo</li>
                        <li>Identificazione univoca del dispositivo</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Blocca le richieste di notifica per impostazione predefinita. Abilita solo per siti fidati e necessari.
                    </div>
                </div>

                <div id="permissions-api" class="info-card">
                    <h2>Permissions API <span class="risk-level risk-medium">Rischio Medio</span></h2>
                    <h3>Cos'è l'API Permissions?</h3>
                    <p>L'API Permissions permette ai siti web di verificare lo stato dei permessi per varie funzionalità come geolocalizzazione, notifiche, fotocamera, ecc.</p>
                    
                    <h3>Implicazioni per la Privacy</h3>
                    <ul>
                        <li>Raccolta di informazioni sui permessi concessi</li>
                        <li>Fingerprinting basato sui permessi disponibili</li>
                        <li>Profilazione del comportamento dell'utente</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Revoca regolarmente i permessi non necessari nelle impostazioni del browser.
                    </div>
                </div>

                <div id="payment-api" class="info-card">
                    <h2>Payment Request API <span class="risk-level risk-low">Rischio Basso</span></h2>
                    <h3>Cos'è l'API Payment Request?</h3>
                    <p>L'API Payment Request semplifica il processo di pagamento online fornendo un'interfaccia standardizzata per i metodi di pagamento.</p>
                    
                    <h3>Considerazioni</h3>
                    <ul>
                        <li>Miglioramento della sicurezza nei pagamenti</li>
                        <li>Riduzione dell'inserimento manuale di dati sensibili</li>
                        <li>Possibile raccolta di informazioni sui metodi di pagamento disponibili</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Beneficio:</strong> Generalmente migliora la sicurezza dei pagamenti online riducendo l'esposizione dei dati sensibili.
                    </div>
                </div>

                <div id="html-css" class="info-card">
                    <h2>HTML5/CSS3 Support <span class="risk-level risk-low">Rischio Basso</span></h2>
                    <h3>Cosa sono HTML5 e CSS3?</h3>
                    <p>HTML5 e CSS3 sono le versioni moderne degli standard web che introducono nuove funzionalità e API avanzate.</p>
                    
                    <h3>Possibili Utilizzi per Tracking</h3>
                    <ul>
                        <li>Canvas fingerprinting tramite HTML5 Canvas</li>
                        <li>Audio fingerprinting tramite Web Audio API</li>
                        <li>Font detection tramite CSS</li>
                        <li>Feature detection per fingerprinting</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Bilanciamento:</strong> HTML5/CSS3 migliorano l'esperienza web ma possono essere usati per fingerprinting. Il supporto è necessario per la navigazione moderna.
                    </div>
                </div>

                <div id="sensors" class="info-card">
                    <h2>Sensors Support <span class="risk-level risk-high">Rischio Alto</span></h2>
                    <h3>Cosa sono i Sensori Web?</h3>
                    <p>I sensori web permettono l'accesso a accelerometro, giroscopio, magnetometro e altri sensori del dispositivo tramite JavaScript.</p>
                    
                    <h3>Rischi per la Privacy</h3>
                    <ul>
                        <li>Fingerprinting basato sui pattern di movimento</li>
                        <li>Possibile keylogging tramite accelerometro</li>
                        <li>Tracciamento dei movimenti fisici</li>
                        <li>Identificazione di pattern comportamentali unici</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Blocca l'accesso ai sensori per siti non fidati. Controlla i permessi nelle impostazioni del browser.
                    </div>
                </div>

                <div id="popups" class="info-card">
                    <h2>Pop-ups <span class="risk-level risk-medium">Rischio Medio</span></h2>
                    <h3>Cosa sono i Pop-ups?</h3>
                    <p>I pop-ups sono finestre che si aprono automaticamente, spesso utilizzate per pubblicità, notifiche o contenuti aggiuntivi.</p>
                    
                    <h3>Rischi</h3>
                    <ul>
                        <li>Veicolo per malware e phishing</li>
                        <li>Interruzione dell'esperienza utente</li>
                        <li>Possibile social engineering</li>
                        <li>Raccolta non autorizzata di dati</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Mantieni attivo il blocco pop-up del browser. Abilita pop-up solo per siti fidati quando necessario.
                    </div>
                </div>

                <div id="geolocation" class="info-card">
                    <h2>Geolocation <span class="risk-level risk-high">Rischio Alto</span></h2>
                    <h3>Cos'è la Geolocalizzazione?</h3>
                    <p>L'API di geolocalizzazione permette ai siti web di richiedere la tua posizione geografica precisa utilizzando GPS, WiFi o dati cellulari.</p>
                    
                    <h3>Rischi per la Privacy</h3>
                    <ul>
                        <li>Tracciamento della posizione fisica</li>
                        <li>Profilazione basata sui luoghi visitati</li>
                        <li>Possibile stalking o targeting fisico</li>
                        <li>Correlazione con altri dati personali</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Blocca sempre le richieste di geolocalizzazione tranne quando strettamente necessario. Controlla le autorizzazioni del browser regolarmente.
                    </div>
                </div>
            </section>

            <!-- DEVICE INFO SECTION -->
            <section id="device-info">
                <div id="ipv4" class="info-card">
                    <h2>Public IPv4 <span class="risk-level risk-high">Rischio Alto</span></h2>
                    <h3>Cos'è l'Indirizzo IP Pubblico?</h3>
                    <p>Il tuo indirizzo IP pubblico è l'identificatore unico assegnato alla tua connessione internet dal provider. Rivela la tua posizione geografica approssimativa.</p>
                    
                    <h3>Informazioni Rivelate</h3>
                    <ul>
                        <li>Posizione geografica approssimativa (città/regione)</li>
                        <li>Provider di servizi internet (ISP)</li>
                        <li>Tipo di connessione (domestica, aziendale, mobile)</li>
                        <li>Possibile correlazione con altri dati online</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Usa una VPN affidabile per mascherare il tuo vero IP. Considera l'uso di Tor per anonimato massimo.
                    </div>
                </div>

                <div id="ipv6" class="info-card">
                    <h2>Public IPv6 <span class="risk-level risk-high">Rischio Alto</span></h2>
                    <h3>Cos'è IPv6?</h3>
                    <p>IPv6 è la nuova versione del protocollo Internet che può contenere informazioni ancora più specifiche del tuo dispositivo e della tua rete.</p>
                    
                    <h3>Rischi Aggiuntivi</h3>
                    <ul>
                        <li>Identificatori unici del dispositivo</li>
                        <li>Tracciamento più preciso rispetto a IPv4</li>
                        <li>Possibili fughe anche con VPN configurate solo per IPv4</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Assicurati che la tua VPN supporti IPv6 o disabilita IPv6 se non necessario.
                    </div>
                </div>

                <div id="screen" class="info-card">
                    <h2>Screen Resolution <span class="risk-level risk-medium">Rischio Medio</span></h2>
                    <h3>Perché la Risoluzione è Importante?</h3>
                    <p>La risoluzione dello schermo, combinata con altre informazioni, contribuisce al fingerprinting del dispositivo rendendo più facile identificarti.</p>
                    
                    <h3>Informazioni Utilizzate per Tracking</h3>
                    <ul>
                        <li>Risoluzione schermo esatta</li>
                        <li>Profondità colori</li>
                        <li>Orientamento del dispositivo</li>
                        <li>Numero di monitor</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Usa browser che mascherano o randomizzano le informazioni dello schermo, o utilizza risoluzioni comuni.
                    </div>
                </div>

                <div id="incognito" class="info-card">
                    <h2>Incognito Mode <span class="risk-level risk-low">Rischio Basso</span></h2>
                    <h3>Cos'è la Modalità Incognito?</h3>
                    <p>La modalità incognito impedisce al browser di salvare cronologia, cookies e dati di sessione localmente, ma non ti rende anonimo online.</p>
                    
                    <h3>Limitazioni</h3>
                    <ul>
                        <li>Non nasconde il tuo IP address</li>
                        <li>I siti web possono ancora tracciarti</li>
                        <li>ISP e amministratori di rete vedono ancora il traffico</li>
                        <li>Fingerprinting del browser ancora possibile</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Uso Corretto:</strong> Usa incognito per navigazione locale privata, ma combina con VPN e altre protezioni per vera privacy online.
                    </div>
                </div>

                <div id="browser-type" class="info-card">
                    <h2>Browser Type <span class="risk-level risk-medium">Rischio Medio</span></h2>
                    <h3>Perché il Tipo di Browser è Importante?</h3>
                    <p>Il tipo di browser (Chrome, Firefox, Safari, Edge) è una delle informazioni più basilari raccolte per il fingerprinting e può rivelare preferenze e caratteristiche dell'utente.</p>
                    
                    <h3>Informazioni Rivelate</h3>
                    <ul>
                        <li>Preferenze tecnologiche dell'utente</li>
                        <li>Possibile sistema operativo</li>
                        <li>Livello di consapevolezza sulla privacy</li>
                        <li>Compatibilità con specifiche tecnologie web</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Limitazioni:</strong> È difficile nascondere completamente il tipo di browser. Considera l'uso di browser focalizzati sulla privacy come Tor Browser.
                    </div>
                </div>

                <div id="browser-version" class="info-card">
                    <h2>Browser Version <span class="risk-level risk-medium">Rischio Medio</span></h2>
                    <h3>Rischi della Versione del Browser</h3>
                    <p>La versione specifica del browser fornisce informazioni dettagliate sulle funzionalità supportate e può indicare quanto l'utente sia aggiornato sulla sicurezza.</p>
                    
                    <h3>Implicazioni</h3>
                    <ul>
                        <li>Identificazione di vulnerabilità note</li>
                        <li>Fingerprinting preciso del software</li>
                        <li>Targeting di exploit specifici</li>
                        <li>Inferenze sul comportamento di aggiornamento</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Mantieni sempre il browser aggiornato. Considera l'uso di User-Agent spoofing, ma attenzione alla compatibilità.
                    </div>
                </div>

                <div id="browser-language" class="info-card">
                    <h2>Browser Language <span class="risk-level risk-medium">Rischio Medio</span></h2>
                    <h3>Informazioni Linguistiche</h3>
                    <p>La lingua del browser può rivelare informazioni geografiche e culturali sull'utente, contribuendo al profiling demografico.</p>
                    
                    <h3>Dati Raccolti</h3>
                    <ul>
                        <li>Lingua primaria e secondarie</li>
                        <li>Localizzazione geografica approssimativa</li>
                        <li>Background culturale</li>
                        <li>Possibili preferenze di contenuto</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Considera di impostare inglese come lingua primaria per maggiore anonimato, ma questo può influire sull'usabilità.
                    </div>
                </div>

                <div id="operating-system" class="info-card">
                    <h2>Operating System <span class="risk-level risk-medium">Rischio Medio</span></h2>
                    <h3>Rilevamento del Sistema Operativo</h3>
                    <p>Il sistema operativo fornisce informazioni cruciali sul tipo di dispositivo, versione software e possibili vulnerabilità.</p>
                    
                    <h3>Informazioni Esposte</h3>
                    <ul>
                        <li>Tipo di dispositivo (Windows, macOS, Linux, mobile)</li>
                        <li>Versione specifica del sistema</li>
                        <li>Architettura (32-bit, 64-bit, ARM)</li>
                        <li>Possibili vulnerabilità del sistema</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Usa browser che mascherano o generalizzano le informazioni del sistema operativo.
                    </div>
                </div>

                <div id="touch-support" class="info-card">
                    <h2>Touch Support <span class="risk-level risk-low">Rischio Basso</span></h2>
                    <h3>Rilevamento del Supporto Touch</h3>
                    <p>Il supporto touch indica se il dispositivo ha uno schermo tattile, fornendo informazioni sul tipo di dispositivo utilizzato.</p>
                    
                    <h3>Informazioni Dedotte</h3>
                    <ul>
                        <li>Tipo di dispositivo (smartphone, tablet, laptop touch)</li>
                        <li>Modalità di interazione preferita</li>
                        <li>Possibili dimensioni dello schermo</li>
                        <li>Context d'uso (mobile vs desktop)</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Informazione:</strong> Il supporto touch è difficile da nascondere ed è generalmente considerato a basso rischio per la privacy.
                    </div>
                </div>

                <div id="mime-types" class="info-card">
                    <h2>MIME Types <span class="risk-level risk-low">Rischio Basso</span></h2>
                    <h3>Cosa sono i Tipi MIME?</h3>
                    <p>I tipi MIME indicano quali formati di file e applicazioni il browser può gestire, rivelando software installati e capacità del sistema.</p>
                    
                    <h3>Informazioni Rivelate</h3>
                    <ul>
                        <li>Plugin e estensioni installate</li>
                        <li>Software specifico presente nel sistema</li>
                        <li>Capacità multimediali del browser</li>
                        <li>Possibili applicazioni di terze parti</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Limita i plugin installati e usa browser con supporto MIME standardizzato.
                    </div>
                </div>

                <div id="referrer-policy" class="info-card">
                    <h2>Referrer Policy <span class="risk-level risk-low">Rischio Basso</span></h2>
                    <h3>Cos'è la Referrer Policy?</h3>
                    <p>La Referrer Policy controlla quali informazioni vengono inviate nell'header Referer quando si naviga tra siti web.</p>
                    
                    <h3>Opzioni di Privacy</h3>
                    <ul>
                        <li>"no-referrer" - Massima privacy, nessuna informazione inviata</li>
                        <li>"origin" - Solo il dominio di origine</li>
                        <li>"strict-origin-when-cross-origin" - Bilanciamento privacy/funzionalità</li>
                        <li>"unsafe-url" - Tutte le informazioni (meno privato)</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Raccomandazione:</strong> Una policy restrittiva migliora la privacy. "strict-origin-when-cross-origin" è un buon compromesso.
                    </div>
                </div>

                <div id="battery-status" class="info-card">
                    <h2>Battery Status <span class="risk-level risk-high">Rischio Alto</span></h2>
                    <h3>API Stato Batteria</h3>
                    <p>L'API Battery Status può fornire informazioni dettagliate sulla batteria del dispositivo, utilizzabili per fingerprinting molto preciso.</p>
                    
                    <h3>Rischi per la Privacy</h3>
                    <ul>
                        <li>Fingerprinting basato sul livello di carica</li>
                        <li>Tempo di ricarica come identificatore unico</li>
                        <li>Correlazione temporale tra sessioni</li>
                        <li>Identificazione del tipo di dispositivo specifico</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Come Proteggersi:</strong> Blocca l'accesso all'API Battery Status. Molti browser moderni l'hanno già limitata o rimossa per questioni di privacy.
                    </div>
                </div>

                <div id="security-protocols" class="info-card">
                    <h2>Security Protocols <span class="risk-level risk-low">Rischio Basso</span></h2>
                    <h3>Protocolli di Sicurezza Supportati</h3>
                    <p>I protocolli di sicurezza supportati (TLS versioni, cipher suites) indicano il livello di sicurezza delle connessioni web.</p>
                    
                    <h3>Informazioni Raccolte</h3>
                    <ul>
                        <li>Versioni TLS/SSL supportate</li>
                        <li>Cipher suites disponibili</li>
                        <li>Certificati supportati</li>
                        <li>Configurazioni di sicurezza del browser</li>
                    </ul>
                    
                    <div class="protection-tip">
                        <strong>💡 Beneficio:</strong> Protocolli di sicurezza aggiornati sono essenziali per la sicurezza. Il fingerprinting basato su questi è generalmente meno preoccupante.
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Back Button -->
    </div>
    <footer>
        <div class="footer-grid">
            <div class="footer-col"><h4>PatchPulse</h4><p>Scanner di sicurezza gratuiti.</p></div>
            <div class="footer-col"><h4>Scanner</h4><a href="browser-scan.php">Browser Scanner</a><a href="maintenance-website-security-scanner.php">Vulnerability Scanner</a></div>
            <div class="footer-col"><h4>Contatti</h4><p>support@patchpulse.org</p></div>
        </div>
        <div class="footer-bottom"><p>&copy; <?= date("Y") ?> PatchPulse. | <a href="policy/privacy_policy.php">Privacy</a> | <a href="policy/terms&condition.php">Terms</a></p></div>
    </footer>
</main>
<script src="script.js"></script>
</body>
</html>
