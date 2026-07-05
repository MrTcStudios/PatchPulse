<?php
include 'config.php';
require_once __DIR__ . "/lang/lang.php";
$currentLang = pp_lang_current();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= t('vpn.title_tag') ?></title>
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
    --amber:     #e3b457;
    --red:       #e8736b;

    --purple:        #8b7cf8;
    --sidebar-bg:    #161616;
    --sidebar-border:#2a2a2a;
    --sidebar-text:  #c8c8c8;
    --sidebar-width: 280px;

    --disp: 'Fraunces', Georgia, 'Times New Roman', serif;
    --sans: 'DM Sans', system-ui, -apple-system, sans-serif;
    --mono: ui-monospace, 'SFMono-Regular', 'Cascadia Code', Menlo, Consolas, monospace;
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
    background: linear-gradient(180deg, rgba(13,13,16,0.45) 0%, rgba(13,13,16,0.64) 55%, rgba(13,13,16,0.8) 100%); }

/* ─────────── SIDEBAR — preservata dalla home attuale ─────────── */
.sidebar {
    width: var(--sidebar-width); min-width: var(--sidebar-width); height: 100vh;
    background: var(--sidebar-bg); display: flex; flex-direction: column;
    padding: 1.5rem 1.2rem; border-right: 1px solid var(--sidebar-border);
    z-index: 100; overflow-y: auto; overflow-x: hidden; font-family: var(--sans);
}
.sidebar-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.logo { display: flex; align-items: center; gap: 0.6rem; text-decoration: none; color: #fff; font-weight: 600; font-size: 1.15rem; letter-spacing: -0.02em; }
.logo img { width: 35px; height: 35px; object-fit: contain; }
.bell-btn { background: none; border: none; cursor: pointer; color: var(--sidebar-text); padding: 0.4rem; border-radius: 8px; transition: color .2s, background .2s; display: flex; align-items: center; }
.bell-btn:hover { color: #fff; background: rgba(255,255,255,.07); }
.search-bar { display: flex; align-items: center; background: #222; border: 1px solid #333; border-radius: 10px; padding: .55rem .8rem; margin-bottom: 1.8rem; gap: .5rem; transition: border-color .2s; }
.search-bar:focus-within { border-color: var(--purple); }
.search-bar input { background: none; border: none; outline: none; color: var(--sidebar-text); font-family: var(--sans); font-size: .9rem; flex: 1; min-width: 0; }
.search-bar input::placeholder { color: #666; }
.search-shortcut { background: #333; color: #888; font-size: .7rem; font-weight: 600; padding: .2rem .45rem; border-radius: 5px; letter-spacing: .05em; }
.nav-section { margin-bottom: .5rem; }
.nav-item { display: flex; align-items: center; gap: .7rem; padding: .6rem .75rem; border-radius: 10px; text-decoration: none; color: var(--sidebar-text); font-size: .9rem; font-weight: 400; transition: background .18s, color .18s; cursor: pointer; border: none; background: none; width: 100%; text-align: left; }
.nav-item:hover { background: rgba(255,255,255,.06); color: #fff; }
.nav-item.active { background: rgba(139,124,248,.15); color: var(--purple); font-weight: 500; }
.nav-icon { width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; opacity: .8; }
.nav-item.active .nav-icon { opacity: 1; }
.nav-sub-icon { width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; opacity: .5; }
.nav-divider { height: 1px; background: var(--sidebar-border); margin: .8rem 0; }
.sidebar-bottom { margin-top: auto; padding-top: .8rem; border-top: 1px solid var(--sidebar-border); }
.hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; padding: 8px; background: none; border: none; }
.hamburger span { display: block; width: 22px; height: 2px; background: #fff; border-radius: 2px; transition: all .3s ease; }
.hamburger.active span:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
.hamburger.active span:nth-child(2) { opacity: 0; }
.hamburger.active span:nth-child(3) { transform: rotate(-45deg) translate(5px, -5px); }

/* ─────────── MAIN ─────────── */
.main-wrapper {
    flex: 1; height: 100vh; overflow-y: auto; overflow-x: hidden;
    background: transparent; color: var(--ink); position: relative; z-index: 1;
    scroll-behavior: smooth;
}
.wrap { max-width: 760px; margin: 0 auto; padding: 0 clamp(1.5rem, 5vw, 3rem) clamp(3rem, 8vw, 5rem); }

/* intestazione */
.scan-hero { padding: clamp(2.5rem, 7vh, 4.5rem) 0 clamp(1.5rem, 4vw, 2.5rem); }
.back-link { display: inline-flex; align-items: center; gap: .45rem; font-size: .82rem; color: var(--mut); text-decoration: none; margin-bottom: 1.6rem; transition: color .2s; }
.back-link:hover { color: var(--violet); }
.back-link svg { width: 14px; height: 14px; }
.eyebrow { font-size: .76rem; font-weight: 500; letter-spacing: .18em; text-transform: uppercase; color: var(--mut); }
.scan-title { font-family: var(--disp); font-optical-sizing: auto; font-weight: 500; font-size: clamp(2.4rem, 6vw, 4rem); line-height: 1; letter-spacing: -.025em; color: var(--ink); margin: .9rem 0 1rem; }
.scan-desc { font-family: var(--disp); font-weight: 400; font-size: clamp(1.05rem, 1.8vw, 1.3rem); line-height: 1.5; color: var(--ink-2); max-width: 54ch; }

/* bottone test */
.test-button { display: inline-flex; align-items: center; gap: .5rem; font-family: var(--sans); font-size: .95rem; font-weight: 600; color: #14121f; background: var(--violet); border: none; border-radius: 0; padding: .95rem 1.7rem; cursor: pointer; transition: background .2s, transform .15s; }
.test-button:hover { background: var(--violet-2); }
.test-button:active { transform: translateY(1px); }
.test-button:disabled { opacity: .6; cursor: default; }

/* risultati (HTML iniettato da script.js) */
#results { margin-top: 1.9rem; }
#results .loading { padding: 1.4rem 0; color: var(--mut); font-size: .95rem; animation: ld 1.2s ease-in-out infinite; }
@keyframes ld { 0%, 100% { opacity: .55; } 50% { opacity: 1; } }
#results h2 { font-family: var(--disp) !important; font-weight: 500 !important; font-size: clamp(1.5rem, 3vw, 2rem) !important; color: var(--ink) !important; text-align: left !important; margin: .4rem 0 1.4rem !important; }
.result { background: rgba(255,255,255,0.025); border: 1px solid var(--line); border-radius: 0; padding: 1.3rem 1.5rem; margin-bottom: 1rem; line-height: 1.9; color: var(--ink-2); font-size: .95rem; }
.result strong { color: var(--ink); font-weight: 600; }
#results code, .result code { font-family: var(--mono); font-size: .85em; color: var(--violet-2); background: rgba(157,134,255,.12); padding: .12em .45em; }
.secure { color: var(--ok); font-weight: 600; }
.warning { color: var(--amber); font-weight: 600; }
.danger { color: var(--red); font-weight: 600; }

/* info */
.info-section { margin-top: clamp(2.5rem, 6vw, 3.5rem); border-top: 1px solid var(--line); padding-top: clamp(2rem, 5vw, 2.8rem); }
.info-section h3 { font-family: var(--disp); font-weight: 500; font-size: clamp(1.4rem, 2.8vw, 1.9rem); line-height: 1.15; letter-spacing: -.015em; color: var(--ink); margin-bottom: .9rem; }
.info-section p { color: var(--ink-2); font-size: 1rem; line-height: 1.7; max-width: 64ch; }
.info-section ul { list-style: none; margin: 1.2rem 0; padding: 0; }
.info-section li { color: var(--ink-2); font-size: .98rem; line-height: 1.6; padding: .55rem 0 .55rem 1.1rem; border-left: 1px solid var(--line); margin-bottom: .55rem; }
.info-section strong { color: var(--ink); font-weight: 600; }

/* footer */
.foot { border-top: 1px solid var(--line); margin-top: clamp(2.5rem, 6vw, 3.5rem); padding-top: 1.5rem; display: flex; flex-wrap: wrap; gap: .8rem 1.6rem; align-items: baseline; justify-content: space-between; }
.foot .copy { font-size: .78rem; color: var(--faint); }
.foot .legal { display: flex; flex-wrap: wrap; gap: .5rem 1.3rem; }
.foot a { font-size: .78rem; color: var(--mut); text-decoration: none; transition: color .2s; }
.foot a:hover { color: var(--violet); }

/* ─────────── RESPONSIVE ─────────── */
@media (max-width: 768px) {
    html, body { height: 100%; overflow: hidden; }
    .sidebar { position: fixed; top: 0; left: 0; right: 0; width: 100%; height: auto; min-width: 0; border-right: none; border-bottom: 1px solid var(--sidebar-border); padding: 0; flex-direction: column; overflow: hidden; z-index: 200; }
    .sidebar-top { margin-bottom: 0; padding: 0 1.2rem; width: 100%; height: 56px; flex-shrink: 0; }
    .hamburger { display: flex; }
    .search-bar, .nav-section, .nav-divider, .sidebar-bottom { display: none; }
    .sidebar.open { height: auto; max-height: 100vh; overflow-y: auto; }
    .sidebar.open .search-bar { display: flex; flex-direction: row; align-items: center; margin: .4rem 1.2rem .6rem; width: calc(100% - 2.4rem); }
    .sidebar.open .nav-section { display: flex; flex-direction: column; width: 100%; padding: 0 .6rem; max-height: 55vh; overflow-y: auto; }
    .sidebar.open .nav-divider { display: block; margin: .4rem 1.2rem; }
    .sidebar.open .sidebar-bottom { display: flex; flex-direction: column; width: 100%; padding: .4rem .6rem .8rem; border-top: 1px solid var(--sidebar-border); margin-top: .2rem; }
    .main-wrapper { position: fixed; top: 56px; left: 0; right: 0; bottom: 0; height: auto; overflow-y: auto; -webkit-overflow-scrolling: touch; overscroll-behavior-y: contain; }
}
</style>
</head>
<body>

<div id="vanta-bg"></div>
<div id="scrim"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <a href="home.php" class="logo">
            <img src="images/PatchPulseLogo.svg" alt="PatchPulse">
            PatchPulse
        </a>
        <div style="display:flex;align-items:center;gap:0.5rem">
            <button class="bell-btn" title="<?= t('nav.notifications') ?>" aria-label="<?= t('nav.notifications') ?>">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </button>
            <button class="hamburger" id="hamburger" aria-label="<?= t('nav.menu') ?>"><span></span><span></span><span></span></button>
        </div>
    </div>
    <div class="search-bar">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:#666;flex-shrink:0"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="<?= t('nav.search_placeholder') ?>" aria-label="<?= t('nav.search_placeholder') ?>">
        <span class="search-shortcut">S</span>
    </div>
    <div class="nav-section">
        <a href="home.php" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span><?= t('nav.homepage') ?>
        </a>
        <a href="home.php#servizi" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span><?= t('nav.applications') ?>
        </a>
        <a href="browser-scan.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span><?= t('nav.browser_scanner') ?>
        </a>
        <a href="VulnerabilityScanner.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span><?= t('nav.website_overview') ?>
        </a>
        <a href="vpn-checker.php" class="nav-item active">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span><?= t('nav.vpn_checker') ?>
        </a>
        <a href="data-breach-checker.php" class="nav-item">
            <span class="nav-sub-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span><?= t('nav.data_breach_monitor') ?>
        </a>
        <a href="extension.php" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20.5 11H19V7a2 2 0 0 0-2-2h-4V3.5a2.5 2.5 0 0 0-5 0V5H4a2 2 0 0 0-2 2v3.8h1.5a2.2 2.2 0 0 1 0 4.4H2V19a2 2 0 0 0 2 2h3.8v-1.5a2.2 2.2 0 0 1 4.4 0V21H17a2 2 0 0 0 2-2v-4h1.5a2.5 2.5 0 0 0 0-5z"/></svg></span><?= t('ext.eyebrow') ?>
        </a>
    </div>
    <div class="nav-divider"></div>
    <div class="sidebar-bottom">
        <a href="home.php#faq" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span><?= t('nav.faq') ?>
        </a>
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
        <a href="settings.php" class="nav-item">
            <span class="nav-icon"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg></span><?= t('nav.settings') ?>
        </a>
    </div>
</aside>

<!-- MAIN -->
<main class="main-wrapper" id="main">
    <div class="wrap">

        <!-- intestazione -->
        <section class="scan-hero">
            <a href="home.php" class="back-link">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                <?= t('nav.back_home') ?>
            </a>
            <p class="eyebrow"><?= t('vpn.eyebrow') ?></p>
            <h1 class="scan-title"><?= t('vpn.title') ?></h1>
            <p class="scan-desc"><?= t('vpn.desc') ?></p>
        </section>

        <button class="test-button" id="testBtn"><?= t('vpn.run_test') ?></button>

        <div id="results"></div>

        <div class="info-section">
            <h3><?= t('vpn.info.title') ?></h3>
            <p><?= t('vpn.info.intro') ?></p>
            <ul>
                <li><strong><?= t('vpn.info.ip_label') ?></strong> <?= t('vpn.info.ip_desc') ?></li>
                <li><strong><?= t('vpn.info.detect_label') ?></strong> <?= t('vpn.info.detect_desc') ?></li>
                <li><strong><?= t('vpn.info.webrtc_label') ?></strong> <?= t('vpn.info.webrtc_desc') ?></li>
                <li><strong><?= t('vpn.info.isp_label') ?></strong> <?= t('vpn.info.isp_desc') ?></li>
            </ul>
            <p style="margin-top:1rem">
                <strong><?= t('vpn.info.privacy_label') ?></strong> <?= t('vpn.info.privacy_desc') ?>
            </p>
        </div>

        <!-- FOOTER -->
        <footer class="foot" id="contatti">
            <span class="copy">&copy; <?= date('Y') ?> PatchPulse</span>
            <span class="legal">
                <a href="policy/privacy_policy.php" target="_blank"><?= t('footer.privacy') ?></a>
                <a href="policy/terms&amp;condition.php" target="_blank"><?= t('footer.terms') ?></a>
                <a href="policy/security-policy.php" target="_blank"><?= t('footer.security') ?></a>
            </span>
        </footer>

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
