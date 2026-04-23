<?php include("../config.php"); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PatchPulse - Termini e Condizioni</title>
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
        .legal-content strong { color: #333; }
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
        <p class="page-header-eyebrow">Legale</p>
        <h1 class="page-header-title">Termini e Condizioni</h1>
    </div>
    <div class="legal-content">
        <p class="updated">Ultimo aggiornamento: <?= date('d/m/Y') ?></p>

        <h2>1. Accettazione dei Termini</h2>
        <p>Utilizzando PatchPulse ("il Servizio"), l'utente accetta integralmente i presenti Termini e Condizioni ("Termini"). Se non accetti questi Termini, non utilizzare il Servizio. La registrazione di un account e l'utilizzo degli strumenti di scansione costituiscono accettazione vincolante di questi Termini.</p>

        <h2>2. Descrizione del Servizio</h2>
        <p>PatchPulse fornisce strumenti gratuiti di analisi della sicurezza informatica, tra cui: Website Vulnerability Scanner (scansione porte, analisi SSL/TLS, verifica vulnerabilità web, enumerazione DNS), Browser Scanner (analisi delle informazioni esposte dal browser), VPN Security Checker (verifica della configurazione VPN), Data Breach Checker (verifica della presenza di email in violazioni note).</p>
        <p>Il Servizio è fornito "così com'è" (as-is) e "come disponibile" (as-available), senza garanzie di alcun tipo, esplicite o implicite.</p>

        <h2>3. Autorizzazione alla Scansione e Verifica DNS</h2>

        <h3>3.1 Obbligo di Autorizzazione</h3>
        <p><strong>L'utente dichiara e garantisce di possedere l'autorizzazione legale per scansionare ogni dominio sottoposto al Servizio.</strong> La scansione di sistemi informatici senza autorizzazione è un reato ai sensi dell'art. 615-ter del Codice Penale italiano e di normative equivalenti in altre giurisdizioni.</p>

        <h3>3.2 Verifica della Proprietà del Dominio</h3>
        <p>Prima di eseguire qualsiasi scansione, il Servizio richiede la verifica della proprietà del dominio tramite l'inserimento di un record DNS TXT specifico. Questa verifica costituisce prova tecnica che l'utente ha accesso amministrativo al dominio e pertanto è autorizzato a scansionarlo. Il sistema verifica la presenza del record DNS prima di ogni scansione.</p>

        <h3>3.3 Responsabilità dell'Utente</h3>
        <p>Nonostante la verifica DNS, <strong>l'utente rimane l'unico responsabile</strong> per assicurarsi che la scansione sia conforme a tutte le leggi applicabili, incluse ma non limitate a: leggi sulla criminalità informatica del proprio paese, termini di servizio del provider di hosting del dominio target, regolamenti aziendali interni (se il dominio appartiene a un'organizzazione), eventuali accordi contrattuali con terze parti.</p>

        <h3>3.4 Manleva</h3>
        <p><strong>L'utente manleva e tiene indenne PatchPulse, i suoi sviluppatori, amministratori e collaboratori</strong> da qualsiasi reclamo, danno, perdita, costo, spesa (incluse le spese legali) derivante dall'uso non autorizzato del Servizio o dalla violazione dei presenti Termini. Questa manleva si estende a qualsiasi azione legale, reclamo o procedimento promosso da terze parti in relazione alle scansioni effettuate dall'utente.</p>

        <h2>4. Uso Consentito</h2>
        <p>Il Servizio può essere utilizzato esclusivamente per: valutare la sicurezza di domini di propria proprietà, eseguire audit di sicurezza autorizzati, scopi educativi e di ricerca su sistemi propri.</p>

        <h2>5. Uso Vietato</h2>
        <p>È severamente vietato utilizzare il Servizio per: scansionare domini senza autorizzazione del proprietario, tentare di aggirare il sistema di verifica DNS, eseguire attacchi Denial of Service (DoS/DDoS), sfruttare le vulnerabilità scoperte per scopi illeciti, rivendere o redistribuire i risultati delle scansioni a fini commerciali senza autorizzazione, interferire con il funzionamento del Servizio o di altri utenti, tentare di accedere a dati di altri utenti.</p>
        <p>La violazione di queste disposizioni comporta la sospensione immediata dell'account senza preavviso e la possibile segnalazione alle autorità competenti.</p>

        <h2>6. Limitazione di Responsabilità</h2>
        <p><strong>PatchPulse non è responsabile per:</strong> danni diretti, indiretti, incidentali, consequenziali o punitivi derivanti dall'uso del Servizio; l'accuratezza, completezza o affidabilità dei risultati delle scansioni; interruzioni del Servizio, errori o malfunzionamenti; azioni intraprese dall'utente sulla base dei risultati delle scansioni; violazioni di legge commesse dall'utente attraverso il Servizio.</p>
        <p>I risultati delle scansioni sono forniti a scopo informativo e non costituiscono una valutazione professionale di sicurezza. Per audit di sicurezza completi, si consiglia di rivolgersi a professionisti certificati.</p>
        <p><strong>In ogni caso, la responsabilità massima di PatchPulse non potrà eccedere la somma eventualmente pagata dall'utente per il Servizio (che, essendo gratuito, è pari a zero).</strong></p>

        <h2>7. Account Utente</h2>
        <p>L'utente è responsabile della riservatezza delle proprie credenziali di accesso. L'utente deve utilizzare una password robusta (minimo 8 caratteri, con maiuscole, minuscole e numeri) e non condividerla con terzi. Qualsiasi attività svolta con le credenziali dell'utente è responsabilità dell'utente stesso. In caso di accesso non autorizzato, l'utente deve comunicarlo immediatamente a support@patchpulse.org.</p>

        <h2>8. Proprietà Intellettuale</h2>
        <p>Il Servizio, il suo codice sorgente, il design e i contenuti sono protetti dalle leggi sulla proprietà intellettuale. L'utente non acquisisce alcun diritto di proprietà intellettuale sul Servizio. I risultati delle scansioni generati per i domini dell'utente appartengono all'utente stesso.</p>

        <h2>9. Disponibilità del Servizio</h2>
        <p>PatchPulse si riserva il diritto di: modificare, sospendere o interrompere il Servizio in qualsiasi momento, senza preavviso; limitare l'accesso al Servizio per motivi di manutenzione o sicurezza; modificare i limiti di utilizzo (rate limiting, numero di scansioni, ecc.).</p>

        <h2>10. Privacy</h2>
        <p>Il trattamento dei dati personali è regolato dalla nostra <a href="privacy_policy.php">Privacy Policy</a>, che costituisce parte integrante dei presenti Termini.</p>

        <h2>11. Cessazione</h2>
        <p>L'utente può cessare di utilizzare il Servizio in qualsiasi momento eliminando il proprio account. PatchPulse può sospendere o eliminare un account in caso di violazione dei presenti Termini, senza obbligo di preavviso. Alla cessazione, tutti i dati dell'utente vengono eliminati permanentemente come descritto nella Privacy Policy.</p>

        <h2>12. Modifiche ai Termini</h2>
        <p>PatchPulse si riserva il diritto di modificare i presenti Termini in qualsiasi momento. Le modifiche saranno pubblicate su questa pagina con la data di aggiornamento. L'uso continuato del Servizio dopo la pubblicazione delle modifiche costituisce accettazione dei Termini aggiornati.</p>

        <h2>13. Legge Applicabile e Foro Competente</h2>
        <p>I presenti Termini sono regolati dalla legge italiana. Per qualsiasi controversia derivante dall'uso del Servizio, il foro competente è quello di Udine, Italia, salvo quanto diversamente previsto da norme inderogabili a tutela del consumatore.</p>

        <h2>14. Clausola di Salvaguardia</h2>
        <p>Se una qualsiasi disposizione dei presenti Termini dovesse essere dichiarata invalida o inapplicabile, le restanti disposizioni rimarranno in vigore a tutti gli effetti.</p>

        <h2>15. Contatti</h2>
        <p>Per domande sui presenti Termini: <strong>support@patchpulse.org</strong></p>
    </div>
</main>
<script>
const h=document.getElementById('hamburger'),s=document.getElementById('sidebar');
h.addEventListener('click',()=>{h.classList.toggle('active');s.classList.toggle('open');});
</script>
</body>
</html>
