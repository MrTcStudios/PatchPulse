<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

include("config.php");
require_once __DIR__ . "/lang/lang.php";

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
<link rel="stylesheet" href="/css/fonts/primary.css">
<style>
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

:root {
    --bg:        #0d0d10;
    --ink:       #ece9e1;
    --ink-2:     #b4afa3;
    --mut:       #87837a;
    --faint:     #5e5b54;
    --line:      rgba(255,255,255,0.11);
    --violet:    #9d86ff;
    --violet-2:  #b7a6ff;
    --ok:        #5fd6a3;
    --red:       #e8736b;

    --purple:        #8b7cf8;
    --sidebar-bg:    #161616;
    --sidebar-border:#2a2a2a;
    --sidebar-text:  #c8c8c8;
    --sidebar-width: 280px;

    --disp: 'Fraunces', Georgia, 'Times New Roman', serif;
    --sans: 'DM Sans', system-ui, -apple-system, sans-serif;
}

html { height: 100%; }
body {
    font-family: var(--sans);
    background: var(--bg);
    color: var(--ink);
    display: flex;
    height: 100vh;
    overflow: hidden;
    -webkit-font-smoothing: antialiased;
    text-rendering: optimizeLegibility;
}
a { color: inherit; }
::selection { background: var(--violet); color: #14121f; }
:focus-visible { outline: 2px solid var(--violet); outline-offset: 3px; }

#vanta-bg { position: fixed; inset: 0; z-index: 0; background: var(--bg); }
#scrim { position: fixed; inset: 0; z-index: 0; pointer-events: none;
    background: linear-gradient(180deg, rgba(13,13,16,0.46) 0%, rgba(13,13,16,0.64) 55%, rgba(13,13,16,0.8) 100%); }

/* ─────────── SIDEBAR ─────────── */
.sidebar { width: var(--sidebar-width); min-width: var(--sidebar-width); height: 100vh; background: var(--sidebar-bg); display: flex; flex-direction: column; padding: 1.5rem 1.2rem; border-right: 1px solid var(--sidebar-border); z-index: 100; overflow-y: auto; overflow-x: hidden; font-family: var(--sans); }
.sidebar-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.logo { display: flex; align-items: center; gap: 0.6rem; text-decoration: none; color: #fff; font-weight: 600; font-size: 1.15rem; letter-spacing: -0.02em; }
.logo img { width: 35px; height: 35px; object-fit: contain; }
.nav-section { margin-bottom: .5rem; }
.nav-item { display: flex; align-items: center; gap: .7rem; padding: .6rem .75rem; border-radius: 10px; text-decoration: none; color: var(--sidebar-text); font-size: .9rem; font-weight: 400; transition: background .18s, color .18s; cursor: pointer; border: none; background: none; width: 100%; text-align: left; }
.nav-item:hover { background: rgba(255,255,255,.06); color: #fff; }
.nav-item.active { background: rgba(139,124,248,.15); color: var(--purple); font-weight: 500; }
.nav-icon { width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; opacity: .8; }
.nav-item.active .nav-icon { opacity: 1; }
.sidebar-bottom { margin-top: auto; padding-top: .8rem; border-top: 1px solid var(--sidebar-border); }
.hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; padding: 8px; background: none; border: none; }
.hamburger span { display: block; width: 22px; height: 2px; background: #fff; border-radius: 2px; transition: all .3s ease; }
.hamburger.active span:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
.hamburger.active span:nth-child(2) { opacity: 0; }
.hamburger.active span:nth-child(3) { transform: rotate(-45deg) translate(5px, -5px); }

/* ─────────── MAIN ─────────── */
.main-wrapper { flex: 1; height: 100vh; overflow-y: auto; overflow-x: hidden; background: transparent; color: var(--ink); position: relative; z-index: 1; scroll-behavior: smooth; }

.page-header { max-width: 680px; margin: 0 auto; padding: clamp(2.5rem, 7vh, 4.5rem) clamp(1.5rem, 5vw, 3rem) clamp(1.2rem, 3vw, 2rem); }
.page-header-back { display: inline-flex; align-items: center; gap: .45rem; font-size: .82rem; color: var(--mut); text-decoration: none; margin-bottom: 1.6rem; transition: color .2s; }
.page-header-back:hover { color: var(--violet); }
.page-header-back svg { width: 14px; height: 14px; }
.page-header-eyebrow { font-size: .76rem; font-weight: 500; letter-spacing: .18em; text-transform: uppercase; color: var(--mut); }
.page-header-title { font-family: var(--disp); font-optical-sizing: auto; font-weight: 500; font-size: clamp(2.4rem, 6vw, 4rem); line-height: 1; letter-spacing: -.025em; color: var(--ink); margin: .9rem 0 0; }

.scanner-section { padding: 0 clamp(1rem, 4vw, 2rem) clamp(3rem, 8vw, 5rem); }

/* Settings */
.settings-grid { display: grid; gap: 1.2rem; max-width: 640px; margin: 0 auto; padding: 0 1rem; }
.settings-card { background: rgba(255,255,255,0.025); border: 1px solid var(--line); border-radius: 0; padding: 2rem; }
.settings-card h3 { font-family: var(--disp); font-size: 1.3rem; font-weight: 500; color: var(--ink); margin-bottom: .4rem; letter-spacing: -.01em; }
.settings-card .settings-desc { font-size: .92rem; color: var(--mut); margin-bottom: 1.4rem; line-height: 1.55; }
.lang-options { display: grid; grid-template-columns: 1fr 1fr; gap: .7rem; margin-bottom: 1.4rem; }
.lang-option { display: flex; align-items: center; justify-content: center; gap: .7rem; padding: 1rem 1.2rem; border: 1px solid var(--line); border-radius: 0; cursor: pointer; transition: border-color .18s, background .18s, transform .12s; font-size: .95rem; font-weight: 500; color: var(--ink); user-select: none; }
.lang-option:hover { border-color: rgba(157,134,255,0.5); transform: translateY(-1px); }
.lang-option input { accent-color: var(--violet); }
.lang-option.is-current { border-color: var(--violet); background: rgba(157,134,255,0.08); box-shadow: 0 0 0 3px rgba(157,134,255,0.1); }
.settings-actions { display: flex; justify-content: flex-start; align-items: center; gap: .8rem; }
.settings-btn { background: var(--violet); color: #14121f; border: none; border-radius: 0; padding: .75rem 1.6rem; font-size: .92rem; font-weight: 600; cursor: pointer; transition: background .18s, transform .12s; font-family: var(--sans); }
.settings-btn:hover { background: var(--violet-2); transform: translateY(-1px); }
.settings-btn:active { transform: translateY(0); }
.settings-note { font-size: .8rem; color: var(--mut); line-height: 1.6; margin-top: 1.2rem; padding-top: 1.2rem; border-top: 1px solid var(--line); }
.settings-flash { padding: .9rem 1.2rem; border-radius: 0; font-size: .9rem; font-weight: 500; }
.settings-flash.ok { background: rgba(95,214,163,0.1); color: var(--ok); border: 1px solid rgba(95,214,163,0.25); }
.settings-flash.error { background: rgba(232,115,107,0.1); color: var(--red); border: 1px solid rgba(232,115,107,0.25); }

/* ─────────── RESPONSIVE ─────────── */
@media (max-width: 768px) {
    html, body { height: 100%; overflow: hidden; }
    .sidebar { position: fixed; top: 0; left: 0; right: 0; width: 100%; height: auto; min-width: 0; border-right: none; border-bottom: 1px solid var(--sidebar-border); padding: 0; flex-direction: column; overflow: hidden; z-index: 200; }
    .sidebar-top { margin-bottom: 0; padding: 0 1.2rem; width: 100%; height: 56px; flex-shrink: 0; }
    .hamburger { display: flex; }
    .nav-section, .sidebar-bottom { display: none; }
    .sidebar.open { height: auto; max-height: 100vh; overflow-y: auto; }
    .sidebar.open .nav-section { display: flex; flex-direction: column; width: 100%; padding: 0 .6rem; max-height: 55vh; overflow-y: auto; }
    .sidebar.open .sidebar-bottom { display: flex; flex-direction: column; width: 100%; padding: .4rem .6rem .8rem; border-top: 1px solid var(--sidebar-border); margin-top: .2rem; }
    .main-wrapper { position: fixed; top: 56px; left: 0; right: 0; bottom: 0; height: auto; overflow-y: auto; -webkit-overflow-scrolling: touch; overscroll-behavior-y: contain; }
}
@media (max-width: 480px) {
    .settings-card { padding: 1.5rem; }
    .lang-options { grid-template-columns: 1fr; }
    .settings-actions { justify-content: stretch; }
    .settings-btn { width: 100%; }
}
</style>
</head>
<body>

<div id="vanta-bg"></div>
<div id="scrim"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <a href="home.php" class="logo">
            <img src="images/PatchPulseLogo.svg" alt="PatchPulse">
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
                            <span><?= t('settings.language.italian') ?></span>
                        </label>
                        <label class="lang-option<?= $currentLang === 'en' ? ' is-current' : '' ?>">
                            <input type="radio" name="lang" value="en" <?= $currentLang === 'en' ? 'checked' : '' ?>>
                            <span><?= t('settings.language.english') ?></span>
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
<script src="js/three.min.js"></script>
<script src="js/vanta.waves.min.js"></script>
<script>
(function () {
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!reduce && window.VANTA && window.VANTA.WAVES) {
        window.VANTA.WAVES({
            el: '#vanta-bg',
            mouseControls: true, touchControls: true, gyroControls: false,
            minHeight: 200.0, minWidth: 200.0, scale: 1.0, scaleMobile: 1.0,
            color: 0x14132a, shininess: 22.0, waveHeight: 13.0, waveSpeed: 0.72, zoom: 0.95
        });
    }
})();
</script>
</body>
</html>
