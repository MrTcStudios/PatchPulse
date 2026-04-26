<?php include("../config.php"); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PatchPulse - Vulnerability Disclosure Policy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .legal-content { max-width: 800px; margin: 0 auto; padding: 2rem 3rem 4rem; }
        .legal-content h2 { font-family: 'DM Serif Display', serif; font-size: 1.5rem; color: #1a1a1a; margin: 2.5rem 0 0.8rem; }
        .legal-content p, .legal-content li { color: #555; font-size: 0.92rem; line-height: 1.75; margin-bottom: 0.6rem; }
        .legal-content ul { padding-left: 1.5rem; margin-bottom: 1rem; }
        .legal-content a { color: var(--purple); }
        .legal-content strong { color: #333; }
        .legal-content .note { background: rgba(139,124,248,0.06); border-left: 3px solid var(--purple); padding: 0.8rem 1rem; border-radius: 0 10px 10px 0; font-size: 0.88rem; color: #666; margin: 1.5rem 0; }
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
        <p class="page-header-eyebrow">Sicurezza</p>
        <h1 class="page-header-title">Vulnerability Disclosure Policy</h1>
    </div>
    <div class="legal-content">

        <h2>Introduzione</h2>
        <p>PatchPulse accoglie con favore il feedback dei ricercatori di sicurezza e del pubblico in generale per contribuire a migliorare la nostra sicurezza. Se ritieni di aver scoperto una vulnerabilità, un problema di privacy, dati esposti o altri problemi di sicurezza in uno qualsiasi dei nostri asset, vogliamo sentirti. Questa politica delinea i passi per segnalare le vulnerabilità, cosa ci aspettiamo e cosa puoi aspettarti da noi.</p>

        <h2>Sistemi in Scope</h2>
        <p>Questa politica si applica a qualsiasi risorsa digitale di proprietà, gestita o mantenuta da PatchPulse.</p>

        <h2>Fuori Scope</h2>
        <ul>
            <li>Asset o attrezzature non di proprietà di PatchPulse</li>
            <li>Infrastruttura di terze parti (Cloudflare, Brevo, ecc.)</li>
            <li>Attacchi di Social Engineering</li>
            <li>Attacchi Denial of Service (DoS/DDoS)</li>
        </ul>
        <p>Le vulnerabilità scoperte o sospettate nei sistemi fuori scope dovrebbero essere segnalate al fornitore appropriato o all'autorità applicabile.</p>

        <h2>I Nostri Impegni</h2>
        <p>Quando lavori con noi secondo questa politica, puoi aspettarti che noi:</p>
        <ul>
            <li>Rispondiamo prontamente alla tua segnalazione e lavoriamo con te per comprendere e validare il tuo report;</li>
            <li>Ci sforziamo di tenerti informato sullo stato di avanzamento della vulnerabilità mentre viene elaborata;</li>
            <li>Lavoriamo per rimediare alle vulnerabilità scoperte in modo tempestivo, entro i nostri vincoli operativi;</li>
            <li>Estendiamo il Safe Harbor per la tua ricerca sulla vulnerabilità condotta in conformità con questa politica.</li>
        </ul>

        <h2>Le Nostre Aspettative</h2>
        <p>Partecipando al nostro programma di divulgazione delle vulnerabilità in buona fede, ti chiediamo di:</p>
        <ul>
            <li>Rispettare le regole, inclusa questa politica e qualsiasi altro accordo pertinente. In caso di incoerenza tra questa politica e altri termini applicabili, prevarranno i termini di questa politica;</li>
            <li>Segnalare qualsiasi vulnerabilità scoperta prontamente;</li>
            <li>Evitare di violare la privacy degli altri, interrompere i nostri sistemi, distruggere dati e/o danneggiare l'esperienza utente;</li>
            <li>Utilizzare solo i canali ufficiali per discutere informazioni sulla vulnerabilità con noi;</li>
            <li>Fornirci un ragionevole lasso di tempo (almeno 90 giorni dalla segnalazione iniziale) per risolvere il problema prima di divulgarlo pubblicamente;</li>
            <li>Eseguire test solo su sistemi in scope e rispettare i sistemi e le attività fuori scope;</li>
            <li>Se una vulnerabilità fornisce un accesso non intenzionale ai dati: limitare la quantità di dati a cui si accede al minimo richiesto per dimostrare efficacemente una Proof of Concept; e cessare immediatamente i test e inviare un report se si incontrano dati utente durante i test, come informazioni di identificazione personale (PII), informazioni sanitarie personali (PHI), dati di carte di credito o informazioni proprietarie;</li>
            <li>Interagire solo con account di test di propria proprietà o con il permesso esplicito del titolare dell'account;</li>
            <li>Non impegnarsi in estorsione.</li>
        </ul>

        <h2>Canali Ufficiali</h2>
        <p>Segnala problemi di sicurezza a <strong><a href="mailto:support@patchpulse.org">support@patchpulse.org</a></strong>, fornendo tutte le informazioni pertinenti. Più dettagli fornisci, più facile sarà per noi valutare e risolvere il problema.</p>

        <h2>Safe Harbor</h2>
        <p>Quando conduciamo ricerche sulla vulnerabilità secondo questa politica, riteniamo che la ricerca condotta nell'ambito di questa politica sia:</p>
        <ul>
            <li><strong>Autorizzata</strong> riguardo a qualsiasi legge anti-hacking applicabile, e non avvieremo o sosterremo azioni legali contro di voi per violazioni accidentali e in buona fede di questa politica;</li>
            <li><strong>Autorizzata</strong> in merito a qualsiasi legge anti-circumvention pertinente, e non presenteremo un reclamo nei vostri confronti per elusione dei controlli tecnologici;</li>
            <li><strong>Esente</strong> dalle restrizioni nei nostri Termini di Servizio (ToS) e/o dalla Politica di Utilizzo Accettabile (AUP) che interferirebbero con la conduzione di ricerche sulla sicurezza, e rinunciamo a tali restrizioni su base limitata;</li>
            <li><strong>Lecita</strong>, utile alla sicurezza generale di Internet, e condotta in buona fede.</li>
        </ul>
        <p>Ci si aspetta, come sempre, di rispettare tutte le leggi applicabili. Se un'azione legale viene avviata da una terza parte contro di te e hai rispettato questa politica, prenderemo misure per far sapere che le tue azioni sono state condotte nel rispetto di questa politica.</p>
        <p>Se in qualsiasi momento hai dubbi o sei incerto se la tua ricerca sulla sicurezza è coerente con questa politica, ti preghiamo di inviare un report attraverso i nostri canali ufficiali prima di procedere oltre.</p>

        <div class="note">Si noti che il Safe Harbor si applica solo alle rivendicazioni legali sotto il controllo dell'organizzazione che partecipa a questa politica e che la politica non vincola terzi indipendenti.</div>

    </div>
</main>
<script src="../script.js"></script>
</body>
</html>
