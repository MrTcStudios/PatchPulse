<?php include("config.php"); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PatchPulse - Hall of Fame</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .hof-content { max-width: 700px; margin: 0 auto; padding: 0 1rem 4rem; }
        .hof-intro { color: #777; font-size: 0.95rem; line-height: 1.7; margin-bottom: 2.5rem; }
        .hof-intro a { color: var(--purple); }
        .hof-empty {
            text-align: center;
            padding: 3rem 2rem;
            background: #fff;
            border: 1px solid rgba(0,0,0,0.07);
            border-radius: 16px;
        }
        .hof-empty-icon { font-size: 2.5rem; margin-bottom: 1rem; }
        .hof-empty p { color: #999; font-size: 0.95rem; }
        .hof-empty a { color: var(--purple); }
        .hof-entry {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.2rem 1.4rem;
            background: #fff;
            border: 1px solid rgba(0,0,0,0.07);
            border-radius: 14px;
            margin-bottom: 0.8rem;
            transition: border-color 0.2s;
        }
        .hof-entry:hover { border-color: rgba(139,124,248,0.3); }
        .hof-rank {
            width: 36px; height: 36px;
            background: var(--purple);
            color: #fff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
        }
        .hof-name { font-weight: 600; color: #1a1a1a; font-size: 0.95rem; }
        .hof-detail { font-size: 0.82rem; color: #999; }
        .hof-date { margin-left: auto; font-size: 0.8rem; color: #bbb; flex-shrink: 0; }
    </style>
</head>
<body>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <a href="home.php" class="logo"><img src="images/PatchPulseLogo.svg" alt="PatchPulse" style="width:50px;height:50px;object-fit:contain;">PatchPulse</a>
        <button class="hamburger" id="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
    </div>
    <div class="nav-section">
        <a href="home.php" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>Homepage</a>
    </div>
    <div class="sidebar-bottom">
        <?php if (isset($_SESSION['user_id'])): ?><a href="account.php" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>Area Personale</a><?php else: ?><a href="log-reg.php#login" class="nav-item"><span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg></span>Login</a><?php endif; ?>
    </div>
</aside>
<main class="main-wrapper" id="main">
    <div class="page-header">
        <a href="home.php" class="page-header-back"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg> Torna alla Home</a>
        <p class="page-header-eyebrow">Sicurezza</p>
        <h1 class="page-header-title">Hall of Fame</h1>
    </div>
    <div class="hof-content">
        <p class="hof-intro">Ringraziamo i ricercatori di sicurezza che ci aiutano a proteggere PatchPulse e i nostri utenti. Se trovi una vulnerabilità, segnalala secondo la nostra <a href="policy/security-policy.php">Vulnerability Disclosure Policy</a>.</p>

        <!-- Quando qualcuno segnala una vulnerabilità, aggiungi un blocco così:
        <div class="hof-entry">
            <div class="hof-rank">1</div>
            <div>
                <div class="hof-name">Nome Ricercatore</div>
                <div class="hof-detail">Tipo di vulnerabilità trovata</div>
            </div>
            <span class="hof-date">Apr 2026</span>
        </div>
        -->

        <div class="hof-empty">
            <div class="hof-empty-icon">🛡️</div>
            <p>Nessuna segnalazione ancora.<br>Vuoi essere il primo? <a href="policy/security-policy.php">Leggi la nostra policy</a>.</p>
        </div>
    </div>
</main>
<script src="script.js"></script>
</body>
</html>
