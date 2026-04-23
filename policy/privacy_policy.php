<?php include("../config.php"); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PatchPulse - Privacy Policy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .legal-content { max-width: 800px; margin: 0 auto; padding: 2rem 3rem 4rem; }
        .legal-content h2 { font-family: 'DM Serif Display', serif; font-size: 1.5rem; color: #1a1a1a; margin: 2.5rem 0 0.8rem; }
        .legal-content h3 { font-size: 1rem; font-weight: 600; color: #1a1a1a; margin: 1.5rem 0 0.5rem; }
        .legal-content p, .legal-content li { color: #555; font-size: 0.92rem; line-height: 1.75; margin-bottom: 0.6rem; }
        .legal-content ul { padding-left: 1.5rem; margin-bottom: 1rem; }
        .legal-content .updated { color: #999; font-size: 0.82rem; margin-bottom: 2rem; }
        .legal-content a { color: var(--purple); }
        @media (max-width: 768px) { .legal-content { padding: 1.5rem; } }
    </style>
</head>
<body>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <a href="../home.php" class="logo"><img src="../images/PatchPulseLogo.svg" alt="PatchPulse" style="width:35px;height:35px;object-fit:contain;">PatchPulse</a>
        <button class="hamburger" id="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
    </div>
    <div class="nav-section">
        <a href="../home.php" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>Homepage</a>
    </div>
</aside>
<main class="main-wrapper" id="main">
    <div class="page-header">
        <a href="../home.php" class="page-header-back"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg> Torna alla Home</a>
        <p class="page-header-eyebrow">Informativa</p>
        <h1 class="page-header-title">Privacy Policy</h1>
    </div>
    <div class="legal-content">
        <p class="updated">Ultimo aggiornamento: <?= date('d/m/Y') ?></p>

        <h2>1. Titolare del Trattamento</h2>
        <p>PatchPulse ("noi", "il Servizio") con sede in Italia. Per qualsiasi richiesta relativa alla privacy: <strong>support@patchpulse.org</strong>.</p>

        <h2>2. Dati Raccolti</h2>

        <h3>2.1 Dati di Registrazione</h3>
        <p>Quando crei un account raccogliamo: nome utente, indirizzo email e password (memorizzata esclusivamente in forma di hash crittografico irreversibile con bcrypt). Non memorizziamo mai la password in chiaro.</p>

        <h3>2.2 Dati di Scansione</h3>
        <p>Quando utilizzi il Website Vulnerability Scanner raccogliamo: il dominio scansionato, il record di verifica DNS associato, i risultati della scansione (porte aperte, informazioni SSL/TLS, record DNS, risultati Nikto). Queste informazioni sono associate al tuo account e visibili solo a te.</p>

        <h3>2.3 Dati del Browser Scanner</h3>
        <p>Il Browser Scanner raccoglie informazioni tecniche sul tuo browser (user agent, risoluzione schermo, supporto tecnologie web, ecc.). Se sei autenticato, queste informazioni possono essere salvate nel tuo account. Se non sei autenticato, i dati vengono elaborati solo lato client e non vengono trasmessi ai nostri server.</p>

        <h3>2.4 Dati di Navigazione</h3>
        <p>Per ogni richiesta al Servizio, il server riceve automaticamente: indirizzo IP (tramite Cloudflare), paese di provenienza, data e ora della richiesta. Questi dati sono utilizzati per la sicurezza, la prevenzione di abusi e la registrazione nei log di attività del tuo account.</p>

        <h3>2.5 Cookies</h3>
        <p>Utilizziamo esclusivamente cookie tecnici di sessione, necessari per il funzionamento dell'autenticazione. Questi cookie sono configurati con i flag HttpOnly, Secure e SameSite=Strict. Non utilizziamo cookie di profilazione, cookie di marketing o cookie di terze parti a scopo pubblicitario.</p>

        <h2>3. Base Giuridica del Trattamento</h2>
        <p>Trattiamo i tuoi dati personali sulla base di: esecuzione contrattuale (fornitura del Servizio), consenso (accettazione dei Termini di Servizio al momento della registrazione), legittimo interesse (sicurezza del Servizio e prevenzione abusi), obbligo legale (ove applicabile).</p>

        <h2>4. Finalità del Trattamento</h2>
        <p>I dati raccolti sono utilizzati per: fornire il Servizio e le sue funzionalità, autenticare gli utenti e gestire gli account, verificare la proprietà dei domini tramite DNS, generare e visualizzare i risultati delle scansioni, garantire la sicurezza e prevenire abusi (rate limiting, brute force protection), inviare email transazionali (conferma registrazione, notifiche di sicurezza).</p>

        <h2>5. Condivisione dei Dati</h2>
        <p>Non vendiamo, affittiamo o condividiamo i tuoi dati personali con terze parti a scopo commerciale. I dati possono essere condivisi con: Cloudflare (CDN e protezione DDoS — i tuoi dati transitano attraverso la loro rete), Brevo/Sendinblue (invio email transazionali — ricezione del tuo indirizzo email), Google DNS e Cloudflare DNS (risoluzione DNS per la verifica dei domini — nessun dato personale condiviso).</p>

        <h2>6. Trasferimento Internazionale</h2>
        <p>Alcuni dei servizi di terze parti sopra menzionati possono trattare dati al di fuori dello Spazio Economico Europeo (SEE). In tali casi, i trasferimenti avvengono sulla base di clausole contrattuali standard approvate dalla Commissione Europea o di altre garanzie adeguate previste dal GDPR.</p>

        <h2>7. Conservazione dei Dati</h2>
        <p>Conserviamo i tuoi dati per il tempo necessario alla fornitura del Servizio. In particolare: dati dell'account (nome, email): fino all'eliminazione dell'account, risultati delle scansioni: fino all'eliminazione da parte dell'utente o dell'account, log di attività: fino alla cancellazione da parte dell'utente, domini verificati: fino alla rimozione del record DNS di verifica. Alla cancellazione dell'account, tutti i dati associati vengono eliminati permanentemente entro 30 giorni.</p>

        <h2>8. Sicurezza</h2>
        <p>Adottiamo misure tecniche e organizzative per proteggere i tuoi dati: password hashate con bcrypt (cost factor 12), connessioni esclusivamente HTTPS, cookie di sessione con flag di sicurezza, protezione CSRF su tutte le operazioni, rate limiting e protezione brute force, container Docker isolati con privilegi minimi, scansioni eseguite attraverso rete Tor per proteggere la privacy del target.</p>

        <h2>9. I Tuoi Diritti (GDPR)</h2>
        <p>Ai sensi del Regolamento (UE) 2016/679 (GDPR), hai diritto a: accesso ai tuoi dati personali, rettifica dei dati inesatti, cancellazione ("diritto all'oblio"), limitazione del trattamento, portabilità dei dati, opposizione al trattamento. Per esercitare questi diritti, scrivi a <strong>support@patchpulse.org</strong>. Risponderemo entro 30 giorni. Hai inoltre il diritto di proporre reclamo al Garante per la Protezione dei Dati Personali (www.garanteprivacy.it).</p>

        <h2>10. Minori</h2>
        <p>Il Servizio non è destinato a minori di 16 anni. Non raccogliamo consapevolmente dati di minori di 16 anni. Se veniamo a conoscenza di aver raccolto dati di un minore, provvederemo alla loro cancellazione immediata.</p>

        <h2>11. Modifiche</h2>
        <p>Ci riserviamo di aggiornare la presente Privacy Policy. In caso di modifiche sostanziali, ne daremo comunicazione tramite il Servizio. L'uso continuato del Servizio dopo la pubblicazione delle modifiche costituisce accettazione delle stesse.</p>

        <h2>12. Contatti</h2>
        <p>Per domande sulla privacy: <strong>support@patchpulse.org</strong></p>
    </div>
</main>
<script>
const h=document.getElementById('hamburger'),s=document.getElementById('sidebar');
h.addEventListener('click',()=>{h.classList.toggle('active');s.classList.toggle('open');});
</script>
</body>
</html>
