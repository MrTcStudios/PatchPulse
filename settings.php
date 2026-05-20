<?php
// settings.php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

include("config.php");
require_once __DIR__ . "/lang/lang.php";

// Hardening headers (CSP intentionally omitted here — site already serves
// inline styles inherited from home.php; this page adds no new script surface).
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted     = $_POST['csrf_token'] ?? '';
    $langInput  = $_POST['lang'] ?? '';

    $csrfOk = is_string($posted)
        && $posted !== ''
        && hash_equals($_SESSION['csrf_token'], $posted);

    if (!$csrfOk || !pp_lang_is_valid($langInput)) {
        $_SESSION['settings_flash'] = ['type' => 'error', 'key' => 'settings.invalid_request'];
    } else {
        pp_lang_persist($langInput);
        $_SESSION['settings_flash'] = ['type' => 'ok', 'key' => 'settings.saved'];
    }

    // Post-Redirect-Get: prevents form re-submission on refresh.
    header('Location: settings.php');
    exit;
}

if (!empty($_SESSION['settings_flash']) && is_array($_SESSION['settings_flash'])) {
    $flash = $_SESSION['settings_flash'];
    unset($_SESSION['settings_flash']);
}

$currentLang = pp_lang_current();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>PatchPulse · <?= t('settings.title') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Container: centrato, larghezza ottimale per leggibilità */
        .settings-grid {
            display: grid;
            gap: 1.2rem;
            max-width: 640px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Card */
        .settings-card {
            background: #fff;
            border: 1px solid rgba(0,0,0,0.07);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .settings-card h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.4rem;
        }
        .settings-card .settings-desc {
            font-size: 0.9rem;
            color: #888;
            margin-bottom: 1.4rem;
            line-height: 1.55;
        }

        /* Language options: card-pill grandi e tappabili */
        .lang-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.7rem;
            margin-bottom: 1.4rem;
        }
        .lang-option {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
            padding: 1rem 1.2rem;
            border: 1.5px solid rgba(0,0,0,0.08);
            border-radius: 14px;
            cursor: pointer;
            transition: border-color 0.18s, background 0.18s, transform 0.12s;
            font-size: 0.95rem;
            font-weight: 500;
            color: #1a1a1a;
            user-select: none;
        }
        .lang-option:hover { border-color: rgba(139,124,248,0.5); transform: translateY(-1px); }
        .lang-option input { accent-color: #8b7cf8; }
        .lang-option.is-current {
            border-color: #8b7cf8;
            background: rgba(139,124,248,0.06);
            box-shadow: 0 0 0 3px rgba(139,124,248,0.08);
        }

        /* Action row: pulsante allineato al contenuto (sinistra) */
        .settings-actions {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 0.8rem;
        }
        .settings-btn {
            background: #1a1a1a;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.6rem;
            font-size: 0.92rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.18s, transform 0.12s;
            font-family: inherit;
        }
        .settings-btn:hover { background: #2d2d2d; transform: translateY(-1px); }
        .settings-btn:active { transform: translateY(0); }

        /* Nota: separata dal contenuto principale */
        .settings-note {
            font-size: 0.8rem;
            color: #999;
            line-height: 1.6;
            margin-top: 1.2rem;
            padding-top: 1.2rem;
            border-top: 1px solid rgba(0,0,0,0.06);
        }

        /* Flash messages */
        .settings-flash {
            padding: 0.9rem 1.2rem;
            border-radius: 14px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .settings-flash.ok    { background: rgba(34,160,107,0.08);  color: #1c7a52; border: 1px solid rgba(34,160,107,0.25); }
        .settings-flash.error { background: rgba(244,63,94,0.08);   color: #b32440; border: 1px solid rgba(244,63,94,0.25); }

        /* Mobile: lang-options stacked */
        @media (max-width: 480px) {
            .settings-card { padding: 1.5rem; }
            .lang-options { grid-template-columns: 1fr; }
            .settings-actions { justify-content: stretch; }
            .settings-btn { width: 100%; }
        }
    </style>
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <a href="home.php" class="logo">
            <img src="images/PatchPulseLogo.svg" alt="PatchPulse" style="width:35px;height:35px;object-fit:contain;">
            PatchPulse
        </a>
        <div style="display:flex;align-items:center;gap:0.5rem;">
            <button class="hamburger" id="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
        </div>
    </div>
    <div class="nav-section">
        <a href="home.php#home" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>
            <?= t('nav.homepage') ?>
        </a>
        <a href="home.php#servizi" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span>
            <?= t('nav.applications') ?>
        </a>
    </div>
    <div class="sidebar-bottom">
        <?php if (isset($_SESSION['user_id'])): ?>
        <a href="account.php" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
            <?= t('nav.account') ?>
        </a>
        <?php else: ?>
        <a href="log-reg.php#login" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg></span>
            <?= t('nav.login') ?>
        </a>
        <?php endif; ?>
        <a href="settings.php" class="nav-item active">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg></span>
            <?= t('nav.settings') ?>
        </a>
    </div>
</aside>

<main class="main-wrapper" id="main">

    <div class="page-header">
        <a href="home.php" class="page-header-back">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            <?= t('settings.back_home') ?>
        </a>
        <p class="page-header-eyebrow"><?= t('settings.eyebrow') ?></p>
        <h1 class="page-header-title"><?= t('settings.title') ?></h1>
    </div>

    <div class="scanner-section">
        <div class="settings-grid">

            <?php if ($flash !== null && isset($flash['type'], $flash['key'])): ?>
                <?php $flashClass = $flash['type'] === 'ok' ? 'ok' : 'error'; ?>
                <div class="settings-flash <?= htmlspecialchars($flashClass, ENT_QUOTES, 'UTF-8') ?>">
                    <?= t($flash['key']) ?>
                </div>
            <?php endif; ?>

            <div class="settings-card">
                <h3><?= t('settings.language.title') ?></h3>
                <p class="settings-desc"><?= t('settings.language.desc') ?></p>

                <form action="settings.php" method="post" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="lang-options">
                        <label class="lang-option<?= $currentLang === 'it' ? ' is-current' : '' ?>">
                            <input type="radio" name="lang" value="it" <?= $currentLang === 'it' ? 'checked' : '' ?>>
                            <span>🇮🇹 <?= t('settings.language.italian') ?></span>
                        </label>
                        <label class="lang-option<?= $currentLang === 'en' ? ' is-current' : '' ?>">
                            <input type="radio" name="lang" value="en" <?= $currentLang === 'en' ? 'checked' : '' ?>>
                            <span>🇬🇧 <?= t('settings.language.english') ?></span>
                        </label>
                    </div>

                    <div class="settings-actions">
                        <button type="submit" class="settings-btn"><?= t('settings.save') ?></button>
                    </div>

                    <p class="settings-note"><?= t('settings.language.auto_note') ?></p>
                </form>
            </div>

        </div>
    </div>

</main>

<?php pp_lang_emit_js(); ?>
<script src="script.js"></script>
</body>
</html>
